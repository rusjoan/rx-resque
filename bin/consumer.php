#!/usr/bin/env php
<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use Clue\React\Redis\Factory;
use Rx\Observable;
use RxResque\Worker\WorkerPool;

$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);
$client = new \RxResque\Client\RedisClient($factory);

$pollRedis = function ($queue, $interval = 10) use ($client) {
    return $client->blpop($queue, $interval);
};

$pauser = new \Rx\Subject\Subject();

$pool = new WorkerPool($loop);
$pool->on('status', function ($isIdle) use ($pauser) {
    $pauser->onNext($isIdle);
});

/** @var Observable $redisStream */
$redisStream = Observable::start(function () {})
    ->flatMap(function () use ($pollRedis) {
        return Rx\React\Promise::toObservable($pollRedis('queue'));
    })
    ->repeat()
    ->_RxResque_pausable($pauser);

$taskStream = $redisStream
    ->filter(function ($result) {
        return is_array($result) && count($result) === 2;
    })
    ->map(function (array $result) {
        return $result[1];
    });

$taskStream->subscribeCallback(
    function ($task) use ($pool) {
        echo json_encode($task) . PHP_EOL;
    }
);

$pool->start();
$loop->run();