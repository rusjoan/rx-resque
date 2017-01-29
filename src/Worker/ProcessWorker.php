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

    /** @var Deferred */
    private $current;

    /** @var bool */
    private $shutdown = false;

    public function __construct(LoopInterface $loop)
    {
        $dir = \dirname(__DIR__, 2) . '/bin';
        $this->strand = new ChanneledProcess($loop, "$dir/worker.php", $dir);
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
        return $this->current === null;
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        $this->strand->start();
        $this->strand->subscribe(
            function ($data) {
                if ($this->isIdle()) {
                    throw new \Exception('kek');
                }

                $deferred = $this->current;
                $this->current = null;

                return $deferred->resolve($data);
            },
            function () {
                return $this->current->reject();
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function enqueue(TaskInterface $task): Promise
    {
        if (!$this->strand->isRunning()) {
            throw new \Exception('The worker has not been started.');
        }

        if ($this->shutdown) {
            throw new \Exception('The worker has been shut down.');
        }

        try {
            $this->current = $deferred = new Deferred();
            $this->strand->publish($task);
        } catch (\Throwable $exception) {
            throw new \Exception('Sending the task to the worker failed.', 0, $exception);
        }

        return $deferred->promise();
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