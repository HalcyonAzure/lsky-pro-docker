<?php

namespace Overtrue\CosClient;

use Overtrue\CosClient\Exceptions\ClientException;
use Overtrue\CosClient\Exceptions\Exception;
use Overtrue\CosClient\Exceptions\InvalidConfigException;
use Overtrue\CosClient\Exceptions\ServerException;
use Overtrue\CosClient\Http\Response;
use Overtrue\CosClient\Middleware\CreateRequestSignature;
use Overtrue\CosClient\Middleware\SetContentMd5;
use Overtrue\CosClient\Traits\CreatesHttpClient;

/**
 * @method \Overtrue\CosClient\Http\Response get($uri, array $options = [])
 * @method \Overtrue\CosClient\Http\Response head($uri, array $options = [])
 * @method \Overtrue\CosClient\Http\Response options($uri, array $options = [])
 * @method \Overtrue\CosClient\Http\Response put($uri, array $options = [])
 * @method \Overtrue\CosClient\Http\Response post($uri, array $options = [])
 * @method \Overtrue\CosClient\Http\Response patch($uri, array $options = [])
 * @method \Overtrue\CosClient\Http\Response delete($uri, array $options = [])
 * @method \Overtrue\CosClient\Http\Response request(string $method, $uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface getAsync($uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface headAsync($uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface optionsAsync($uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface putAsync($uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface postAsync($uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface patchAsync($uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface deleteAsync($uri, array $options = [])
 * @method \GuzzleHttp\Promise\PromiseInterface requestAsync(string $method, $uri, array $options = [])
 */
class Client
{
    use CreatesHttpClient;

    public const DEFAULT_REGION = 'ap-guangzhou';

    protected Config $config;

    protected \GuzzleHttp\Client $client;

    /**
     * @param  \Overtrue\CosClient\Config|array  $config
     *
     * @throws \Overtrue\CosClient\Exceptions\InvalidConfigException
     */
    public function __construct($config)
    {
        if (!($config instanceof Config)) {
            $config = new Config($config);
        }

        if (!$config->has('app_id') || !$config->has('secret_id') || !$config->has('secret_key')) {
            throw new InvalidConfigException('app_id, secret_id and secret_key was required.');
        }

        $this->config = $config;

        $this->configureUserAgent($config);

        $this->pushMiddleware(
            new CreateRequestSignature(
                $this->getSecretId(),
                $this->getSecretKey(),
                $this->config->get('signature_expires')
            )
        );
        $this->pushMiddleware(new SetContentMd5());
    }

    public function getAppId(): int
    {
        return $this->config->get('app_id', 0);
    }

    public function getSecretId(): string
    {
        return $this->config->get('secret_id', '');
    }

    public function getSecretKey(): string
    {
        return $this->config->get('secret_key', '');
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getHttpClient(): \GuzzleHttp\Client
    {
        return $this->client ?? $this->client = $this->createHttpClient();
    }

    public function __call($method, $arguments)
    {
        try {
            return new Response(\call_user_func_array([$this->getHttpClient(), $method], $arguments));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new ClientException($e);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            throw new ServerException($e);
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    public static function spy()
    {
        return \Mockery::mock(static::class);
    }

    public static function partialMock()
    {
        $mock = \Mockery::mock(static::class)->makePartial();
        $mock->shouldReceive('getHttpClient')->andReturn(\Mockery::mock(\GuzzleHttp\Client::class));

        return $mock;
    }

    public static function partialMockWithConfig(Config $config, array $methods)
    {
        $mock = \Mockery::mock(static::class.\sprintf('[%s]', \join(',', $methods)), [$config]);
        $mock->shouldReceive('getHttpClient')->andReturn(\Mockery::mock(\GuzzleHttp\Client::class));

        return $mock;
    }

    /**
     * @param  \Overtrue\CosClient\Config  $config
     *
     * @return \Overtrue\CosClient\Client
     */
    protected function configureUserAgent(Config $config): Client
    {
        $this->setHttpClientOptions(\array_replace_recursive([
            'headers' => [
                'User-Agent' => 'overtrue/qcloud-cos-client:'.\GuzzleHttp\Client::MAJOR_VERSION,
            ],
        ], $config->get('guzzle', [])));

        return $this;
    }
}
