<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 14:47
 */

namespace RxResque\Worker;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;

class WorkerPool extends EventEmitter implements PoolInterface
{
    /** @var LoopInterface */
    private $loop;

    /** @var int */
    private $minSize;

    /** @var int */
    private $maxSize;

    /** @var \SplObjectStorage */
    private $workers;

    /** @var \SplQueue */
    private $idleWorkers;

    /** @var \SplQueue */
    private $busyWorkers;

    /** @var bool */
    private $running = false;

    public function __construct(LoopInterface $loop, int $minSize = null, int $maxSize= null)
    {
        $this->loop = $loop;

        if ($minSize < 0) {
            throw new \Error('Minimum size must be a non-negative integer.');
        }

        if ($maxSize < 0 || $maxSize < $minSize) {
            throw new \Error('Maximum size must be a non-negative integer at least '.$minSize.'.');
        }

        $this->minSize = $minSize ?: self::DEFAULT_MIN_SIZE;
        $this->maxSize = $maxSize ?: self::DEFAULT_MAX_SIZE;

        $this->workers = new \SplObjectStorage();
        $this->idleWorkers = new \SplQueue();
        $this->busyWorkers = new \SplQueue();
    }

    /**
     * @inheritdoc
     */
    public function getWorkerCount(): int
    {
        return $this->workers->count();
    }

    /**
     * @inheritdoc
     */
    public function getIdleWorkerCount(): int
    {
        return $this->idleWorkers->count();
    }

    /**
     * @inheritdoc
     */
    public function getMinSize(): int
    {
        return $this->minSize;
    }

    /**
     * @inheritdoc
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * @inheritdoc
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * @inheritdoc
     */
    public function isIdle(): bool
    {
        return $this->idleWorkers->count() > 0;
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        if ($this->isRunning()) {
            throw new \Exception('The worker pool has already been started.');
        }

        $count = $this->minSize;
        while (--$count >= 0) {
            $worker = $this->createWorker();
            $this->idleWorkers->enqueue($worker);
        }

        $this->running = true;

        $this->emit('status', [$this->isIdle()]);
    }

    /**
     * Creates a worker and adds them to the pool.
     *
     * @return WorkerInterface The worker created.
     */
    private function createWorker() {
        $worker = new ProcessWorker($this->loop);
        $worker->start();

        $this->workers->attach($worker, 0);

        return $worker;
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
        // TODO: Implement shutdown() method.
    }

    /**
     * @inheritdoc
     */
    public function kill()
    {
        // TODO: Implement kill() method.
    }
}