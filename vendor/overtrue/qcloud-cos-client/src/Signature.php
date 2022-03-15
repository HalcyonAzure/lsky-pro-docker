<?php

namespace Overtrue\CosClient;

use Psr\Http\Message\RequestInterface;

class Signature
{
    public const SIGN_HEADERS = [
        'host',
        'content-type',
        'content-md5',
        'content-disposition',
        'content-encoding',
        'content-length',
        'transfer-encoding',
        'range',
    ];

    public string $accessKey;

    public string $secretKey;

    public function __construct(string $accessKey, string $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    /**
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @param  string|null  $expires
     *
     * @return string
     */
    public function createAuthorizationHeader(RequestInterface $request, ?string $expires = null): string
    {
        $signTime = self::getTimeSegments($expires);
        $queryToBeSigned = self::getQueryToBeSigned($request);
        $headersToBeSigned = self::getHeadersToBeSigned($request);

        $httpStringHashed = sha1(
            strtolower($request->getMethod())."\n".urldecode($request->getUri()->getPath())."\n".
            join('&', array_values($queryToBeSigned)).
            "\n".\http_build_query($headersToBeSigned)."\n"
        );

        $stringToSign = \sprintf("sha1\n%s\n%s\n", $signTime, $httpStringHashed);
        $signature = hash_hmac('sha1', $stringToSign, hash_hmac('sha1', $signTime, $this->secretKey));

        return \sprintf(
            'q-sign-algorithm=sha1&q-ak=%s&q-sign-time=%s&q-key-time=%s&q-header-list=%s&q-url-param-list=%s&q-signature=%s',
            $this->accessKey,
            $signTime,
            $signTime,
            join(';', array_keys($headersToBeSigned)),
            join(';', array_keys($queryToBeSigned)),
            $signature
        );
    }

    /**
     * @param  \Psr\Http\Message\RequestInterface  $request
     *
     * @return array
     */
    protected static function getHeadersToBeSigned(RequestInterface $request): array
    {
        $headers = [];
        foreach ($request->getHeaders() as $header => $value) {
            $header = strtolower(urlencode($header));

            if (false !== \strpos($header, 'x-cos-') || \in_array($header, self::SIGN_HEADERS)) {
                $headers[$header] = $value[0];
            }
        }

        ksort($headers);

        return $headers;
    }

    /**
     * @param  \Psr\Http\Message\RequestInterface  $request
     *
     * @return array
     */
    protected static function getQueryToBeSigned(RequestInterface $request): array
    {
        $query = [];
        foreach (explode('&', $request->getUri()->getQuery()) as $item) {
            if (!empty($item)) {
                $tmpquery = explode('=', $item);
                $key = strtolower($tmpquery[0]);
                if (count($tmpquery) >= 2) {
                    $value = $tmpquery[1];
                } else {
                    $value = "";
                }
                $query[$key] = $key.'='.$value;
            }
        }
        ksort($query);

        return $query;
    }

    /**
     * @param  string|null  $expires
     *
     * @return string
     */
    protected static function getTimeSegments(?string $expires): string
    {
        $timezone = \date_default_timezone_get();

        date_default_timezone_set('PRC');

        $signTime = \sprintf('%s;%s', time() - 60, strtotime($expires ?? '+60 minutes'));

        date_default_timezone_set($timezone);

        return $signTime;
    }
}
