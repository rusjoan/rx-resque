#!/usr/bin/env php
<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use Clue\React\Redis\Factory;
use Rx\Observable;
use RxResque\SampleTask;
use RxResque\Worker\WorkerPool;

$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);
$client = new \RxResque\Client\RedisClient($factory);

$pollRedis = function ($queue, $interval = 10) use ($client) {
    return $client->blpop($queue, $interval);
};

$pauser = new \Rx\Subject\Subject();

$pool = new WorkerPool($loop, 0, 4);
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
        return new SampleTask($result[1]);
    });

$taskStream->subscribeCallback(
    function (\RxResque\Worker\TaskInterface $task) use ($pool) {
        $pool->enqueue($task)
            ->then(function ($data) use ($task) {
               echo "SENT $task->value RECEIVED $data done!\n";
            });
    },
    function (\Throwable $exeption) {
        throw $exeption;
    }
);

$loop->addPeriodicTimer(10, function () use ($pool) {
   printf("Active: %d, Free: %d\n", $pool->getBusyWorkerCount(), $pool->getIdleWorkerCount());
});

$pool->start();
$loop->run();