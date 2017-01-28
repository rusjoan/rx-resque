<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 14:41
 */

namespace RxResque\Worker;

interface TaskInterface
{
    /**
     * Runs the task inside the caller's context.
     */
    public function run();
}