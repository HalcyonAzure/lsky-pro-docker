<?php

namespace Overtrue\CosClient\Middleware;

use Psr\Http\Message\RequestInterface;

class SetContentMd5
{
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $request->withHeader(
                'Content-MD5',
                base64_encode(md5($request->getBody()->getContents(), true))
            );

            return $handler($request, $options);
        };
    }
}
