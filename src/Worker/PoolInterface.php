<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 14:42
 */

namespace RxResque\Worker;

use Evenement\EventEmitterInterface;

interface PoolInterface extends WorkerInterface, EventEmitterInterface
{
    /** @var int The default minimum pool size. */
    const DEFAULT_MIN_SIZE = 4;

    /** @var int The default maximum pool size. */
    const DEFAULT_MAX_SIZE = 32;

    /**
     * Gets the number of workers currently running in the pool.
     *
     * @return int The number of workers.
     */
    public function getWorkerCount(): int;

    /**
     * Gets the number of workers that are currently idle.
     *
     * @return int The number of idle workers.
     */
    public function getIdleWorkerCount(): int;

    /**
     * Gets the minimum number of workers the pool may have idle.
     *
     * @return int The minimum number of workers.
     */
    public function getMinSize(): int;

    /**
     * Gets the maximum number of workers the pool may spawn to handle concurrent tasks.
     *
     * @return int The maximum number of workers.
     */
    public function getMaxSize(): int;
}