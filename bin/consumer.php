<?php
use Rx\Observable;
use Rx\ObserverInterface;
use Clue\React\Redis\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
$loop = \React\EventLoop\Factory::create();
$factory = new Factory($loop);
function asString($value)
{
    if (is_array($value)) {
        return json_encode($value);
    }
    return (string) $value;
}
$createStdoutObserver = function ($prefix = '') {
    return new Rx\Observer\CallbackObserver(
        function ($value) use ($prefix) {
            echo $prefix . "Next value: " . asString($value) . "\n";
        },
        function (\Exception $error) use ($prefix) {
            echo $prefix . "Exception: " . $error->getMessage() . "\n";
        },
        function () use ($prefix) {
            echo $prefix . "Complete!\n";
        }
    );
};

$client = new \RxResque\Client\RedisClient($factory);
$stdoutObserver = $createStdoutObserver();

$obs = \Rx\Observable::create(
    function (ObserverInterface $observer) use ($client) {
        $client->blpop('queue', 10)
            ->then(function ($result) use ($observer) {
                if (is_array($result) && count($result) === 2) {
                    $observer->onNext($result[1]);
                }
                $observer->onCompleted();
            });
    })
    ->repeatWhen(function (\Rx\Observable $polls) {
        return $polls->concatMap(function ($value) {
            return Observable::just($value);
        });
    });

$obs->subscribe($stdoutObserver);

$loop->run();