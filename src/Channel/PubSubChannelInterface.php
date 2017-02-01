<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 01.02.17
 * Time: 20:54
 */

namespace RxResque\Channel;

interface PubSubChannelInterface
{
    /**
     * Publish data into channel
     *
     * @param $data
     */
    public function publish($data);

    /**
     * Subscribe callbacks on channel actions
     *
     * @param callable $onData
     * @param callable $onError
     * @param callable $onClose
     */
    public function subscribe(callable $onData, callable $onError, callable $onClose);
}