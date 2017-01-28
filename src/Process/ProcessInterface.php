<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 22:32
 */

namespace RxResque\Process;

interface ProcessInterface
{
    /**
     * @return int PID of process.
     */
    public function getPid(): int;

    /**
     * @param int $signo
     */
    public function signal(int $signo);
}