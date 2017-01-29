#!/usr/bin/env php
<?php
declare(strict_types = 1);

@cli_set_process_title('rx-resque worker');

ob_start(function ($data) {
    fwrite(STDERR, $data);
    return '';
}, 1, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE);

(function () {
    $paths = [
        dirname(__DIR__, 3) . '/autoload.php',
        dirname(__DIR__) . '/vendor/autoload.php',
    ];

    $autoloadPath = null;
    foreach ($paths as $path) {
        if (file_exists($path)) {
            $autoloadPath = $path;
            break;
        }
    }

    if ($autoloadPath === null) {
        fwrite(STDERR, 'Could not locate autoload.php.');
        exit(1);
    }

    require $autoloadPath;
})();

$loop = \React\EventLoop\Factory::create();

$channel = new \RxResque\Channel\StreamedChannel(new \React\Stream\Stream(STDIN, $loop), new \React\Stream\Stream(STDOUT, $loop));
$channel->subscribe(
    function ($task) use ($channel) {
        $task->run();
        $channel->publish($task->value);
    },
    function () {

    }
);

$loop->run();

