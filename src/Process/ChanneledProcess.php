<?php
/**
 * Created by PhpStorm.
 * User: sergey
 * Date: 28.01.17
 * Time: 22:13
 */

namespace RxResque\Process;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use Rx\React\Promise as RxPromise;
use Rx\Subject\Subject;
use RxResque\Channel\ChannelSubject;
use RxResque\StrandInterface;

class ChanneledProcess implements ProcessInterface, StrandInterface
{
    /** @var LoopInterface */
    private $loop;

    /** @var Process */
    private $process;

    /** @var Subject */
    private $channel;

    public function __construct(LoopInterface $loop, string $path, string $cwd = "", array $env = [])
    {
        $this->loop = $loop;

        $command = \PHP_BINARY . " " . \escapeshellarg($path);
        $this->process = new Process($command, $cwd, $env);
    }

    /**
     * @inheritdoc
     */
    public function start()
    {
        $this->process->start($this->loop);
        $this->channel = new ChannelSubject($this->process->stdout, $this->process->stdin, $this->process->stderr);
    }

    /**
     * @inheritdoc
     */
    public function send($data)
    {
        $this->channel->onNext($data);
    }

    /**
     * @inheritdoc
     */
    public function receive(): Promise
    {
        return RxPromise::fromObservable($this->channel->take(1));
    }

    /**
     * @inheritdoc
     */
    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    /**
     * @inheritdoc
     */
    public function kill()
    {
        $this->process->terminate();
    }

    /**
     * @inheritdoc
     */
    public function getPid(): int
    {
        return $this->process->getPid();
    }
}