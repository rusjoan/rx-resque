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

class ProcessWorker implements WorkerInterface
{
    /** @var StrandInterface */
    private $strand;

    /** @var Deferred */
    private $current;

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
    }

    /**
     * @inheritdoc
     */
    public function enqueue(TaskInterface $task): Promise
    {
        // TODO: Implement enqueue() method.
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