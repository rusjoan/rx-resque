<?php
use Rx\Observable;
use Rx\ObserverInterface;
use Clue\React\Redis\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);

$client = new \RxResque\Client\RedisClient($factory);

$f = function () use ($client) {
    var_dump('CALLED');
    return $client->blpop('queue', 10)
        ->then(function ($result) {
            if (!is_array($result) || count($result) < 2) {
                throw new \Exception('Empty result, retry');
            }
            return $result[1];
        });
};

$pauser = new \Rx\Subject\Subject();
$pool = new \RxResque\WorkerPool(1, $pauser, $loop);
$source = Observable::start(function () {})
    ->flatMap(function () use ($f) {
        return Rx\React\Promise::toObservable($f());
    })
    ->retry()
    ->_RxResque_pausable($pauser)
    ->repeat()
    ->share();

$source->subscribe(new Rx\Observer\CallbackObserver(
    function ($value) use ($pool) {
        $pool->submit($value);
        echo "Next value: " . $value . "\n";
    },
    function (\Exception $error) {
        echo "Exception: " . $error->getMessage() . "\n";
    },
    function () {
        echo "Complete!\n";
    }
));
$pauser->onNext(true);

$loop->run();