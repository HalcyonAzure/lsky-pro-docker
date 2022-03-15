<?php

namespace Overtrue\CosClient;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Overtrue\CosClient\Exceptions\InvalidArgumentException;
use Overtrue\CosClient\Exceptions\InvalidConfigException;
use Overtrue\CosClient\Support\XML;

class ObjectClient extends Client
{
    public ?string $baseUri = null;

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

        if (!$config->has('bucket')) {
            throw new InvalidConfigException('No bucket configured.');
        }

        $this->baseUri = \sprintf(
            'https://%s-%s.cos.%s.myqcloud.com/',
            $config->get('bucket'),
            $config->get('app_id'),
            $config->get('region', self::DEFAULT_REGION)
        );

        parent::__construct($config->extend([
            'guzzle' => [
                'base_uri' => $this->baseUri,
            ],
        ]));
    }

    /**
     * @param  string  $key
     * @param  string  $body
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putObject(string $key, string $body, array $headers = [])
    {
        return $this->put(\urlencode($key), \compact('body', 'headers'));
    }

    /**
     * @param  string  $key
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     * @throws \Overtrue\CosClient\Exceptions\InvalidArgumentException
     */
    public function copyObject(string $key, array $headers)
    {
        if (empty($headers['x-cos-copy-source'])) {
            throw new InvalidArgumentException('Missing required header: x-cos-copy-source');
        }

        if (($headers['x-cos-metadata-directive'] ?? 'Copy') === 'Replaced' && empty($headers['Content-Type'])) {
            throw new InvalidArgumentException('Missing required header: Content-Type');
        }

        return $this->put(\urlencode($key), array_filter(\compact('headers')));
    }

    /**
     * @see https://docs.guzzlephp.org/en/stable/request-options.html#multipart
     *
     * @param  array  $multipart
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function postObject(array $multipart)
    {
        return $this->post('/', \compact('multipart'));
    }

    /**
     * @param  string  $key
     * @param  array  $query
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getObject(string $key, array $query = [], array $headers = [])
    {
        return $this->get(\urlencode($key), \compact('query', 'headers'));
    }

    /**
     * @param  string  $key
     * @param  string|null  $versionId
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function headObject(string $key, string $versionId = null, array $headers = [])
    {
        return $this->head(\urlencode($key), [
            'query' => \compact('versionId'),
            'headers' => $headers,
        ]);
    }

    /**
     * @param  string  $key
     * @param  string|null  $versionId
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteObject(string $key, string $versionId = null)
    {
        return $this->delete(\urlencode($key), [
            'query' => \compact('versionId'),
        ]);
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteObjects(array $body)
    {
        return $this->post('/?delete', ['body' => XML::fromArray($body)]);
    }

    /**
     * @param  string  $key
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function optionsObject(string $key)
    {
        return $this->options(\urlencode($key));
    }

    /**
     * @param  string  $key
     * @param  array  $body
     * @param  string|null  $versionId
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function restoreObject(string $key, array $body, string $versionId = null)
    {
        return $this->post(\urlencode($key), [
            'query' => [
                'restore' => '',
                'versionId' => $versionId,
            ],
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @param  string  $key
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function selectObjectContents(string $key, array $body)
    {
        return $this->post(\urlencode($key), [
            'query' => [
                'select' => '',
                'select-type' => 2,
            ],
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @param  string  $key
     * @param  array  $body
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putObjectACL(string $key, array $body, array $headers = [])
    {
        return $this->put(\urlencode($key), [
            'query' => [
                'acl' => '',
            ],
            'body' => XML::fromArray($body),
            'headers' => $headers,
        ]);
    }

    /**
     * @param  string  $key
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getObjectACL(string $key)
    {
        return $this->get(\urlencode($key), [
            'query' => [
                'acl' => '',
            ],
        ]);
    }

    /**
     * @param  string  $key
     * @param  array  $body
     * @param  string|null  $versionId
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putObjectTagging(string $key, array $body, string $versionId = null)
    {
        return $this->put(\urlencode($key), [
            'query' => [
                'tagging' => '',
                'VersionId' => $versionId,
            ],
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @param  string  $key
     * @param  string|null  $versionId
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getObjectTagging(string $key, string $versionId = null)
    {
        return $this->get(\urlencode($key), [
            'query' => [
                'tagging' => '',
                'VersionId' => $versionId,
            ],
        ]);
    }

    /**
     * @param  string  $key
     * @param  string|null  $versionId
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteObjectTagging(string $key, string $versionId = null)
    {
        return $this->delete(\urlencode($key), [
            'query' => [
                'tagging' => '',
                'VersionId' => $versionId,
            ],
        ]);
    }

    /**
     * @param  string  $key
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     * @throws \Overtrue\CosClient\Exceptions\InvalidArgumentException
     */
    public function createUploadId(string $key, array $headers)
    {
        if (empty($headers['Content-Type'])) {
            throw new InvalidArgumentException('Missing required headers: Content-Type');
        }

        return $this->post(\urlencode($key), [
            'query' => [
                'uploads' => '',
            ],
            'headers' => $headers,
        ]);
    }

    /**
     * @param  string  $key
     * @param  int  $partNumber
     * @param  string  $uploadId
     * @param  string  $body
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function uploadPart(string $key, int $partNumber, string $uploadId, string $body, array $headers = [])
    {
        return $this->putPart(...\func_get_args());
    }

    /**
     * @param  string  $key
     * @param  int  $partNumber
     * @param  string  $uploadId
     * @param  string  $body
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putPart(string $key, int $partNumber, string $uploadId, string $body, array $headers = [])
    {
        return $this->put(\urlencode($key), [
            'query' => \compact('partNumber', 'uploadId'),
            'headers' => $headers,
            'body' => $body,
        ]);
    }

    /**
     * @param  string  $key
     * @param  int  $partNumber
     * @param  string  $uploadId
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     * @throws \Overtrue\CosClient\Exceptions\InvalidArgumentException
     */
    public function copyPart(string $key, int $partNumber, string $uploadId, array $headers = [])
    {
        if (empty($headers['x-cos-copy-source'])) {
            throw new InvalidArgumentException('Missing required header: x-cos-copy-source');
        }

        return $this->put(\urlencode($key), [
            'query' => \compact('partNumber', 'uploadId'),
            'headers' => $headers,
        ]);
    }

    /**
     * @param  string  $key
     * @param  string  $uploadId
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function markUploadAsCompleted(string $key, string $uploadId, array $body)
    {
        return $this->post(\urlencode($key), [
            'query' => [
                'uploadId' => $uploadId,
            ],
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @param  string  $key
     * @param  string  $uploadId
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function markUploadAsAborted(string $key, string $uploadId)
    {
        return $this->delete(\urlencode($key), [
            'query' => [
                'uploadId' => $uploadId,
            ],
        ]);
    }

    /**
     * @param  array  $query
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getUploadJobs(array $query = [])
    {
        return $this->get('/?uploads', \compact('query'));
    }

    /**
     * @param  string  $key
     * @param  string  $uploadId
     * @param  array  $query
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getUploadedParts(string $key, string $uploadId, array $query = [])
    {
        $query['uploadId'] = $uploadId;

        return $this->get(\urlencode($key), compact('query'));
    }

    /**
     * @param  string  $key
     *
     * @return string
     */
    public function getObjectUrl(string $key)
    {
        return \sprintf('%s/%s', \rtrim($this->baseUri, '/'), \ltrim($key, '/'));
    }

    /**
     * @param  string  $key
     * @param  string|null  $expires
     *
     * @return string
     */
    public function getObjectSignedUrl(string $key, ?string $expires = '+60 minutes')
    {
        $objectUrl = $this->getObjectUrl($key);
        $signature = new Signature($this->config['secret_id'], $this->config['secret_key']);
        $request = new Request('GET', $objectUrl);

        return \strval((new Uri($objectUrl))->withQuery(\http_build_query(['sign' => $signature->createAuthorizationHeader($request, $expires)])));
    }
}
