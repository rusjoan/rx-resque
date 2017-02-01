<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 01.02.17
 * Time: 20:05
 */

namespace RxResque\Channel;

use React\Promise\Deferred;
use React\Promise\Promise;
use React\Stream\Stream;

class Channel implements ChannelInterface, StreamChannelInterface
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
    public function send($data)
    {
        $serialized = serialize($data);
        $this->write->write($serialized);
    }

    /**
     * @inheritdoc
     */
    public function receive(): Promise
    {
        $deferred = new Deferred();

        $this->read->once('data', function ($raw) use ($deferred) {
           $data = unserialize($raw);
           $deferred->resolve($data);
        });

        $this->read->once('close', [$deferred, 'reject']);

        return $deferred->promise();
    }
}