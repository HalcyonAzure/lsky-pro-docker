<?php

namespace Overtrue\CosClient;

class ServiceClient extends Client
{
    /**
     * @param  string|null  $region
     *
     * @return \Overtrue\CosClient\Http\Response
     */
    public function getBuckets(?string $region = null)
    {
        $uri = $region ? \sprintf('https://cos.%s.myqcloud.com/', $region) : 'https://service.cos.myqcloud.com/';

        return $this->get($uri);
    }
}
