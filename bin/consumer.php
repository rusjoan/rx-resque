<?php
use Rx\Observable;
use Rx\ObserverInterface;
use Clue\React\Redis\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);
$client = new \RxResque\Client\RedisClient($factory);

$pollRedis = function ($queue, $interval = 10) use ($client) {
    return $client->blpop('queue', $interval);
};

$pauser = new \Rx\Subject\Subject();
$pool = new \RxResque\WorkerPool(5, $pauser, $loop);

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
        $pool->submit($task);
    }
);
$pauser->onNext(true);

$loop->run();