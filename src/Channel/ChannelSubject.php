<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque\Channel;

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
     * @inheritdoc
     */
    public function onNext($data)
    {
        $serialized = serialize($data);
        $this->write->write($serialized);
    }

    /**
     * @inheritdoc
     */
    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        parent::subscribe($observer, $scheduler);

        $this->read->on('data', function ($serialized) {
            try {
                $data = unserialize($serialized);
                parent::onNext($data);
            } catch (\Throwable $exception) {
                $this->onError($exception);
            }
        });

        $this->read->on('close', function () {
            $this->onError(new \Exception('Context has died'));
        });
    }
}