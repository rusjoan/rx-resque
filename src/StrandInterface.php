<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 22:39
 */

namespace RxResque;

use RxResque\Channel\ChannelInterface;

interface StrandInterface extends ChannelInterface, ContextInterface {}