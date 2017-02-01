<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque\Channel;

use React\Stream\Stream;

class PubSubChannel implements PubSubChannelInterface, StreamChannelInterface
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
    public function publish($data)
    {
        $serialized = serialize($data);
        $this->write->write($serialized);
    }

    /**
     * @inheritdoc
     */
    public function subscribe(callable $onData, callable $onError, callable $onClose)
    {
        $this->read->on('data', function ($raw) use ($onData, $onError) {
            try {
                $data = unserialize($raw);
                $onData($data);
            } catch (\Throwable $exception) {
                $onError($exception);
            }
        });

        $this->read->on('close', $onClose);
    }
}