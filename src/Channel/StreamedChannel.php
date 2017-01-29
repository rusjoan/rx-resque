<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque\Channel;

use React\Stream\Stream;
use Rx\Subject\Subject;

class StreamedChannel implements ChannelInterface
{
    /** @var Stream */
    private $read;

    /** @var Stream */
    private $write;

    /** @var Subject */
    private $subject;

    public function __construct(Stream $read, Stream $write)
    {
        $this->read = $read;
        $this->write = $write;

        $this->subject = new Subject();
    }

    /**
     * @inheritdoc
     */
    public function publish($data)
    {
        $serialized = serialize($data);
        $this->write->write($serialized);
    }

    /**
     * @inheritdoc
     */
    public function subscribe(callable $onData, callable $onError)
    {
        $this->subject->subscribeCallback($onData, $onError);

        try {
            $this->read->on('data', function ($serialized) {
                try {
                    $data = unserialize($serialized);
                    $this->subject->onNext($data);
                } catch (\Throwable $exception) {
                    throw new \Exception('Serialization');
                }
            });

            $this->read->on('close', function ($output) {
                throw new \Exception('Process died');
                $this->subject->onCompleted();
            });
        } catch (\Throwable $exception) {
            $this->subject->onError($exception);
        }
    }
}