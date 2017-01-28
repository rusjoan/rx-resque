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
use RxResque\Channel\ChannelInterface;
use RxResque\Channel\StreamedChannel;
use RxResque\StrandInterface;

class ChanneledProcess implements ProcessInterface, StrandInterface
{
    /** @var LoopInterface */
    private $loop;

    /** @var Process */
    private $process;

    /** @var ChannelInterface */
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
        $this->channel = new StreamedChannel($this->process->stdout, $this->process->stdin);
    }

    /**
     * @inheritdoc
     */
    public function publish($data)
    {
        $this->channel->publish($data);
    }

    /**
     * @inheritdoc
     */
    public function subscribe(callable $onData, callable $onError)
    {
        $this->channel->subscribe($onData, $onError);
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

    /**
     * @inheritdoc
     */
    public function signal(int $signo)
    {
        // TODO
    }
}