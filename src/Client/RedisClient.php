<?php
/**
 * Created by Sergey Gorodnichev
 * @email sergey.gor@livetex.ru
 */

namespace RxResque\Client;

use Clue\React\Redis\Client;
use Clue\React\Redis\Factory;
use React\Promise\PromiseInterface;

/**
 * Class RedisClient
 * @package RxResque\Client
 *
 * @method PromiseInterface blpop(array $keys, $timeout)
 */
class RedisClient
{
    const DEFAULT_SCHEME = 'tcp';
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = '6379';

    /** @var array  */
    private static $defaultOptions = [
        'scheme' => self::DEFAULT_SCHEME,
        'host' => self::DEFAULT_HOST,
        'port' => self::DEFAULT_PORT
    ];

    /** @var PromiseInterface */
    private $client;


    /**
     * RedisClient constructor.
     *
     * @param Factory $factory
     * @param array $options
     */
    public function __construct(Factory $factory, array $options = [])
    {
        $mergedOptions = array_merge(self::$defaultOptions, $options);
        $connectionParams = "{$mergedOptions['scheme']}://{$mergedOptions['host']}:{$mergedOptions['port']}";

        $this->client = $factory->createClient($connectionParams);
    }

    /**
     * Close current connection
     *
     * @return PromiseInterface
     */
    public function close()
    {
        return $this->client->then(function (Client $client) {
                return $client->close();
            }, function (\Exception $e) {
                throw $e;
            });
    }

    /**
     * Call redis command
     *
     * @param string $name
     * @param array $arguments
     *
     * @return PromiseInterface
     */
    public function __call($name, array $arguments)
    {
        return $this->client->then(function (Client $client) use ($name, $arguments) {
            return call_user_func_array([$client, $name], $arguments);
        }, function (\Exception $e) {
            throw $e;
        });
    }
}