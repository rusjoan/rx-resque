<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 01.02.17
 * Time: 22:25
 */

namespace RxResque\Task;

class SuccessfulTask implements TaskResultInterface
{
    /** @var mixed */
    private $result;

    public function __construct($result) {
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getResult() {
        return $this->result;
    }
}