<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque\Channel;

interface ChannelInterface
{
    /**
     * Send data to channel
     *
     * @param mixed $data
     */
    public function publish($data);

    /**
     * Subscribe fn on received data
     *
     * @param callable $onData
     * @param callable $onError
     */
    public function subscribe(callable $onData, callable $onError);
}