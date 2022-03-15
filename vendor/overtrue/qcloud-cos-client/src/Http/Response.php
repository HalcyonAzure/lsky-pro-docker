<?php

namespace Overtrue\CosClient\Http;

use Overtrue\CosClient\Support\XML;
use Psr\Http\Message\ResponseInterface;

class Response extends \GuzzleHttp\Psr7\Response implements \JsonSerializable, \ArrayAccess
{
    protected ?array $arrayResult = null;

    public function __construct(ResponseInterface $response)
    {
        parent::__construct(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }

    public function toArray()
    {
        if (!\is_null($this->arrayResult)) {
            return $this->arrayResult;
        }

        $contents = $this->getContents();

        if (empty($contents)) {
            return $this->arrayResult = null;
        }

        return $this->arrayResult = $this->isXML() ? XML::toArray($contents) : \json_decode($contents, true);
    }

    public function toObject()
    {
        return \json_decode(\json_encode($this->toArray()));
    }

    public function isXML()
    {
        return \strpos($this->getHeaderLine('content-type'), 'xml') > 0;
    }

    public function jsonSerialize()
    {
        try {
            return $this->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function offsetExists($offset)
    {
        return \array_key_exists($offset, $this->toArray());
    }

    public function offsetGet($offset)
    {
        return $this->toArray()[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        return null;
    }

    public function offsetUnset($offset)
    {
        return null;
    }

    public static function create(
        $status = 200,
        array $headers = [],
        $body = null,
        $version = '1.1',
        $reason = null
    ) {
        return new self(new \GuzzleHttp\Psr7\Response($status, $headers, $body, $version, $reason));
    }

    public function toString()
    {
        return $this->getContents();
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        $this->getBody()->rewind();

        return $this->getBody()->getContents();
    }

    /**
     * Is response informative?
     *
     * @final
     */
    public function isInformational(): bool
    {
        return $this->getStatusCode() >= 100 && $this->getStatusCode() < 200;
    }

    /**
     * Is response successful?
     *
     * @final
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }

    /**
     * Is the response a redirect?
     *
     * @final
     */
    public function isRedirection(): bool
    {
        return $this->getStatusCode() >= 300 && $this->getStatusCode() < 400;
    }

    /**
     * Is there a client error?
     *
     * @final
     */
    public function isClientError(): bool
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * Was there a server side error?
     *
     * @final
     */
    public function isServerError(): bool
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * Is the response OK?
     *
     * @final
     */
    public function isOk(): bool
    {
        return 200 === $this->getStatusCode();
    }

    /**
     * Is the response forbidden?
     *
     * @final
     */
    public function isForbidden(): bool
    {
        return 403 === $this->getStatusCode();
    }

    /**
     * Is the response a not found error?
     *
     * @final
     */
    public function isNotFound(): bool
    {
        return 404 === $this->getStatusCode();
    }

    /**
     * Is the response empty?
     *
     * @final
     */
    public function isEmpty(): bool
    {
        return \in_array($this->getStatusCode(), [204, 304]) || empty($this->getContents());
    }
}
