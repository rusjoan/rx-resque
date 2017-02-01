<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 01.02.17
 * Time: 22:24
 */

namespace RxResque\Task;

use RxResque\Exception\TaskException;

class FailedTask implements TaskResultInterface
{
    /** @var string */
    private $type;

    /** @var string */
    private $message;

    /** @var int */
    private $code;

    /** @var array */
    private $trace;

    public function __construct(\Throwable $exception) {
        $this->type = \get_class($exception);
        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
        $this->trace = $exception->getTraceAsString();
    }

    /**
     * {@inheritdoc}
     */
    public function getResult() {
        throw new TaskException(
            $this->type,
            \sprintf(
                'Uncaught exception in execution context of type "%s" with message "%s"',
                $this->type,
                $this->message
            ),
            $this->code,
            $this->trace
        );
    }
}