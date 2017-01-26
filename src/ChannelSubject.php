<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque;

use React\Stream\Stream;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

class ChannelSubject extends Subject
{
    /** @var Stream */
    private $read;

    /** @var Stream */
    private $write;

    public function __construct(Stream $read, Stream $write)
    {
        $this->read = $read;
        $this->write = $write;
    }

    /**
     * @param mixed $value
     */
    public function onNext($value)
    {
        $this->write->write($value);
    }

    /**
     * @inheritdoc
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {

    }
}