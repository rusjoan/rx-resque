<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 01.02.17
 * Time: 22:16
 */

namespace RxResque\Exception;

class TaskException extends \Exception
{
    /** @var string Class name of uncaught exception. */
    private $name;

    /** @var string Stack trace. */
    private $trace;

    /**
     * Creates a new task exception
     *
     * @param string $name
     * @param string $message
     * @param int    $code
     * @param string $trace
     */
    public function __construct(string $name, string $message = '', int $code = 0, string $trace = '')
    {
        parent::__construct($message, $code);
        $this->name = $name;
        $this->trace = $trace;
    }

    /**
     * Returns the class name of the uncaught exception.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the stack trace
     *
     * @return string
     */
    public function getErrorTrace(): string
    {
        return $this->trace;
    }
}