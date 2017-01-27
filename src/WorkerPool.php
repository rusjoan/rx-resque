<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque;

use React\EventLoop\LoopInterface;
use Rx\Subject\Subject;

class WorkerPool
{
    private $pauser;
    private $active;
    private $size;
    private $loop;

    public function __construct($size, Subject $pauser, LoopInterface $loop)
    {
        $this->pauser = $pauser;
        $this->size = $size;
        $this->active = 0;
        $this->loop = $loop;
    }

    public function submit($value)
    {
        ++$this->active;
        $isFree = $this->size > $this->active;
        var_dump("Pool is freeze! $this->active is active, $isFree - means free");
        $this->pauser->onNext($isFree);
        $this->recieve();
    }

    public function recieve() {
        $this->loop->addTimer(rand(3, 10), function () {
            --$this->active;
            $isFree = $this->size > $this->active;
            $this->pauser->onNext($isFree);
            var_dump("Pool is free now! $this->active is active, $isFree - means free");
        });
    }
}