<?php

namespace Overtrue\CosClient\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

trait CreatesHttpClient
{
    protected array $options = [];
    protected array $middlewares = [];
    protected ?HandlerStack $handlerStack = null;

    /**
     * @param array $options
     *
     * @return \GuzzleHttp\Client
     */
    public function createHttpClient(array $options = [])
    {
        return new Client(array_merge([
            'handler' => $this->getHandlerStack(),
        ], $this->options, $options));
    }

    /**
     * @param  array  $options
     *
     * @return $this
     */
    public function setHttpClientOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getHttpClientOptions()
    {
        return $this->options;
    }

    /**
     * Add a middleware.
     *
     * @param callable    $middleware
     * @param string|null $name
     *
     * @return $this
     */
    public function pushMiddleware(callable $middleware, string $name = null)
    {
        if (!is_null($name)) {
            $this->middlewares[$name] = $middleware;
        } else {
            array_push($this->middlewares, $middleware);
        }

        return $this;
    }

    /**
     * Return all middlewares.
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @param  array  $middlewares
     *
     * @return \Overtrue\CosClient\Traits\CreatesHttpClient
     */
    public function setMiddlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @param \GuzzleHttp\HandlerStack $handlerStack
     *
     * @return $this
     */
    public function setHandlerStack(HandlerStack $handlerStack)
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    /**
     * Build a handler stack.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack(): HandlerStack
    {
        if ($this->handlerStack) {
            return $this->handlerStack;
        }

        $this->handlerStack = HandlerStack::create();

        foreach ($this->middlewares as $name => $middleware) {
            $this->handlerStack->unshift($middleware, $name);
        }

        return $this->handlerStack;
    }
}
