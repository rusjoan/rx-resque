<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 29.01.17
 * Time: 15:26
 */

namespace RxResque;

use RxResque\Task\TaskInterface;

class SampleTask implements TaskInterface
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Runs the task inside the caller's context.
     */
    public function run()
    {
        sleep(rand(5, 20));
    }
}