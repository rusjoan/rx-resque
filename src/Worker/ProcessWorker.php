<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 21:52
 */

namespace RxResque\Worker;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use RxResque\Process\ChanneledProcess;
use RxResque\StrandInterface;
use RxResque\Task\TaskInterface;

class ProcessWorker implements WorkerInterface
{
    /** @var StrandInterface */
    private $strand;

    /** @var \SplObjectStorage */
    private $taskStorage;

    /** @var bool */
    private $shutdown = false;

    public function __construct(LoopInterface $loop)
    {
        $dir = \dirname(__DIR__, 2) . '/bin';
        $this->strand = new ChanneledProcess($loop, "$dir/worker.php", $dir);
        $this->taskStorage = new \SplObjectStorage();
    }

    /**
     * @inheritdoc
     */
    public function isRunning(): bool
    {
        return $this->strand->isRunning();
    }

    /**
     * @inheritdoc
     */
    public function isIdle(): bool
    {
        return $this->taskStorage->count() === 0;
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        $this->strand->start();
    }

    /**
     * @inheritdoc
     */
    public function enqueue(TaskInterface $task): Promise
    {
        $this->taskStorage->attach($task);
        $deferred = new Deferred();

        switch (true) {
            case !$this->strand->isRunning():
                $deferred->reject(new \Exception('The worker has not been started.'));
                break;
            case $this->shutdown:
                $deferred->reject(new \Exception('The worker has been shut down.'));
                break;
            default:
                $this->strand->send($task);
                $deferred->resolve($this->strand->receive());
                /*
                    ->then(
                        function ($data) use ($deferred) {
                            $deferred->resolve($data);
                        },
                        function (\Throwable $exception) use ($deferred) {
                            $deferred->reject($exception);
                        }
                    );*/
                break;
        }

        return $deferred->promise()
            ->always(function () use ($task) {
                $this->taskStorage->detach($task);
            });
    }

    /**
     * @inheritdoc
     */
    public function shutdown(): Promise
    {
        // TODO
    }

    /**
     * @inheritdoc
     */
    public function kill()
    {
        $this->strand->kill();
    }
}