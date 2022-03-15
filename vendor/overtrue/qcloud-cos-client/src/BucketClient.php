<?php

namespace Overtrue\CosClient;

use Overtrue\CosClient\Exceptions\InvalidConfigException;
use Overtrue\CosClient\Support\XML;

class BucketClient extends Client
{
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

        parent::__construct($config->extend([
            'guzzle' => [
                'base_uri' => \sprintf(
                    'https://%s-%s.cos.%s.myqcloud.com/',
                    $config->get('bucket'),
                    $config->get('app_id'),
                    $config->get('region', self::DEFAULT_REGION)
                ),
            ]
        ]));
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putBucket(array $body = [])
    {
        return $this->put('/', empty($body) ? [] : [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function headBucket()
    {
        return $this->head('/');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteBucket()
    {
        return $this->delete('/');
    }

    /**
     * @param  array  $query
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getObjects(array $query = [])
    {
        return $this->get('/', \compact('query'));
    }

    /**
     * @param  array  $query
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getObjectVersions(array $query = [])
    {
        return $this->get('/?versions', \compact('query'));
    }

    /**
     * @param  array  $body
     * @param  array  $headers
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putACL(array $body = [], array $headers = [])
    {
        return $this->put('/?acl', \array_filter([
            'headers' => $headers,
            'body' => XML::fromArray($body),
        ]));
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getACL()
    {
        return $this->get('/?acl');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putCORS(array $body)
    {
        return $this->put('/?cors', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getCORS()
    {
        return $this->get('/?cors');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteCORS()
    {
        return $this->delete('/?cors');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putLifecycle(array $body)
    {
        return $this->put('/?lifecycle', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getLifecycle()
    {
        return $this->get('/?lifecycle');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteLifecycle()
    {
        return $this->delete('/?lifecycle');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putPolicy(array $body)
    {
        return $this->put('/?policy', ['json' => $body]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getPolicy()
    {
        return $this->get('/?policy');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deletePolicy()
    {
        return $this->delete('/?policy');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putReferer(array $body)
    {
        return $this->put('/?referer', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getReferer()
    {
        return $this->get('/?referer');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putTagging(array $body)
    {
        return $this->put('/?tagging', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getTagging()
    {
        return $this->get('/?tagging');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteTagging()
    {
        return $this->delete('/?tagging');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putWebsite(array $body)
    {
        return $this->put('/?website', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getWebsite()
    {
        return $this->get('/?website');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteWebsite()
    {
        return $this->delete('/?website');
    }

    /**
     * @param  string  $id
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putInventory(string $id, array $body)
    {
        return $this->put(\sprintf('/?inventory&id=%s', $id), [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @param  string  $id
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getInventory(string $id)
    {
        return $this->get(\sprintf('/?inventory&id=%s', $id));
    }

    /**
     * @param  string|null  $nextContinuationToken
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getInventoryConfigurations(?string $nextContinuationToken = null)
    {
        return $this->get(\sprintf('/?inventory&continuation-token=%s', $nextContinuationToken));
    }

    /**
     * @param  string  $id
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteInventory(string $id)
    {
        return $this->delete(\sprintf('/?inventory&id=%s', $id));
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putVersioning(array $body)
    {
        return $this->put('/?versioning', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getVersioning()
    {
        return $this->get('/?versioning');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putReplication(array $body)
    {
        return $this->put('/?replication', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getReplication()
    {
        return $this->get('/?replication');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteReplication()
    {
        return $this->delete('/?replication');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putLogging(array $body)
    {
        return $this->put('/?logging', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getLogging()
    {
        return $this->get('/?logging');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putAccelerate(array $body)
    {
        return $this->put('/?accelerate', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getAccelerate()
    {
        return $this->get('/?accelerate');
    }

    /**
     * @param  array  $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function putEncryption(array $body)
    {
        return $this->put('/?encryption', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getEncryption()
    {
        return $this->get('/?encryption');
    }

    /**
     * @return \Overtrue\CosClient\Http\Response
     */
    public function deleteEncryption()
    {
        return $this->delete('/?encryption');
    }
}
