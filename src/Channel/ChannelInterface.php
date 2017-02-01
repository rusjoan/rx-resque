<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque\Channel;

use React\Promise\Promise;

interface ChannelInterface
{
    /**
     * Send data to channel
     *
     * @param mixed $data
     */
    public function send($data);

    /**
     * Receive data from channel in future
     *
     * @return Promise
     */
    public function receive(): Promise;
}