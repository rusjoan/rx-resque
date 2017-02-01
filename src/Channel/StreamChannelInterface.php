<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 01.02.17
 * Time: 20:57
 */

namespace RxResque\Channel;

use React\Stream\Stream;

interface StreamChannelInterface
{
    public function __construct(Stream $read, Stream $write);
}