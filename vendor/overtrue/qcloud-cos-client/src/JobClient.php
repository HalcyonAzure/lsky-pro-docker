<?php

namespace Overtrue\CosClient;

use Overtrue\CosClient\Exceptions\InvalidConfigException;
use Overtrue\CosClient\Support\XML;

class JobClient extends Client
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

        $this->validateConfig($config);

        parent::__construct($config->extend([
            'guzzle' => [
                'base_uri' => \sprintf(
                    'https://%s.cos-control.%s.myqcloud.com/',
                    $config->get('uin'),
                    $config->get('region')
                ),
                'headers' => [
                    'x-cos-appid' => $config->get('app_id')
                ]
            ]
        ]));
    }

    /**
     * @param array $query
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getJobs(array $query = [])
    {
        return $this->get('/jobs', [
            'query' => $query,
        ]);
    }

    /**
     * @param array $body
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function createJob(array $body)
    {
        return $this->post('/jobs', [
            'body' => XML::fromArray($body),
        ]);
    }

    /**
     * @param string $id
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function describeJob(string $id)
    {
        return $this->get(\sprintf('/jobs/%s', $id));
    }

    /**
     * @param string $id
     * @param int    $priority
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function updateJobPriority(string $id, int $priority)
    {
        return $this->post(\sprintf('/jobs/%s/priority', $id), [
            'query' => [
                'priority' => $priority,
            ],
        ]);
    }

    /**
     * @param string $id
     * @param array  $query
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function updateJobStatus(string $id, array $query)
    {
        return $this->post(\sprintf('/jobs/%s/status', $id), \compact('query'));
    }

    /**
     * @param  \Overtrue\CosClient\Config  $config
     *
     * @throws \Overtrue\CosClient\Exceptions\InvalidConfigException
     */
    protected function validateConfig(Config $config)
    {
        if (!$config->has('uin')) {
            throw new InvalidConfigException('Invalid config uin.');
        }

        if (!$config->has('region')) {
            throw new InvalidConfigException('Invalid config region.');
        }
    }
}
