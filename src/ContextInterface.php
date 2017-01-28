<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 22:18
 */

namespace RxResque;

interface ContextInterface
{
    /**
     * Check if current context is running
     *
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * Starts the execution context.
     */
    public function start();

    /**
     * Immediately kills the context.
     */
    public function kill();
}