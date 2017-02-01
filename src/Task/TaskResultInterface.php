<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 29.01.17
 * Time: 21:08
 */

namespace RxResque\Task;

interface TaskResultInterface
{
    /**
     * Get the result of task execution
     *
     * @return mixed
     *
     */
    public function getResult();
}