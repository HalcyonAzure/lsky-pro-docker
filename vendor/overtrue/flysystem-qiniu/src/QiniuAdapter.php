<?php

namespace Overtrue\Flysystem\Qiniu;

use JetBrains\PhpStorm\Pure;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use Qiniu\Auth;
use Qiniu\Cdn\CdnManager;
use Qiniu\Http\Error;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class QiniuAdapter implements FilesystemAdapter
{
    protected ?Auth $authManager = null;
    protected ?UploadManager $uploadManager = null;
    protected ?BucketManager $bucketManager = null;
    protected ?CdnManager $cdnManager = null;

    public function __construct(
        protected string $accessKey,
        protected string $secretKey,
        protected string $bucket,
        protected string $domain
    ) {
    }

    public function fileExists(string $path): bool
    {
        [, $error] = $this->getBucketManager()->stat($this->bucket, $path);

        return is_null($error);
    }

    public function directoryExists(string $path): bool
    {
        return $this->fileExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $mime = $config->get('mime', 'application/octet-stream');

        /**
         * @var Error|null $error
         */
        [, $error] = $this->getUploadManager()->put(
            $this->getAuthManager()->uploadToken($this->bucket),
            $path,
            $contents,
            null,
            $mime,
            $path
        );

        if ($error) {
            throw UnableToWriteFile::atLocation($path, $error->message());
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $data = '';

        while (!feof($contents)) {
            $data .= fread($contents, 1024);
        }

        $this->write($path, $data, $config);
    }

    public function read(string $path): string
    {
        $result = file_get_contents($this->getUrl($path));
        if ($result === false) {
            throw UnableToReadFile::fromLocation($path);
        }

        return $result;
    }

    public function readStream(string $path)
    {
        if (ini_get('allow_url_fopen')) {
            if ($result = fopen($this->getUrl($path), 'r')) {
                return $result;
            }
        }

        throw UnableToReadFile::fromLocation($path);
    }

    public function delete(string $path): void
    {
        [, $error] = $this->getBucketManager()->delete($this->bucket, $path);
        if (!is_null($error)) {
            throw UnableToDeleteFile::atLocation($path);
        }
    }

    public function deleteDirectory(string $path): void
    {
        $this->delete($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
    }

    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path);
    }

    public function visibility(string $path): FileAttributes
    {
        throw UnableToRetrieveMetadata::visibility($path);
    }

    public function mimeType(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if ($meta->mimeType() === null) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return $meta;
    }

    public function lastModified(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if ($meta->lastModified() === null) {
            throw UnableToRetrieveMetadata::lastModified($path);
        }
        return $meta;
    }

    public function fileSize(string $path): FileAttributes
    {
        $meta = $this->getMetadata($path);

        if ($meta->fileSize() === null) {
            throw UnableToRetrieveMetadata::fileSize($path);
        }
        return $meta;
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $result = $this->getBucketManager()->listFiles($this->bucket, $path);

        foreach ($result[0]['items'] ?? [] as $files) {
            yield $this->normalizeFileInfo($files);
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        [, $error] = $this->getBucketManager()->rename($this->bucket, $source, $destination);
        if (!is_null($error)) {
            throw UnableToMoveFile::fromLocationTo($source, $destination);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        [, $error] = $this->getBucketManager()->copy($this->bucket, $source, $this->bucket, $destination);
        if (!is_null($error)) {
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }
    }

    protected function getMetadata($path): FileAttributes|array
    {
        $result = $this->getBucketManager()->stat($this->bucket, $path);
        $result[0]['key'] = $path;

        return $this->normalizeFileInfo($result[0]);
    }

    public function getUrl(string $path): string
    {
        $segments = $this->parseUrl($path);
        $query = empty($segments['query']) ? '' : '?' . $segments['query'];

        return $this->normalizeHost($this->domain) . ltrim(implode('/', array_map('rawurlencode', explode('/', $segments['path']))), '/') . $query;
    }


    public function fetch(string $path, string $url): bool|array
    {
        [$response, $error] = $this->getBucketManager()->fetch($url, $this->bucket, $path);

        if ($error) {
            return false;
        }

        return $response;
    }

    /**
     * For laravel FilesystemAdapter.
     */
    public function getTemporaryUrl($path, int|string|\DateTimeInterface $expiration): string
    {
        if ($expiration instanceof \DateTimeInterface) {
            $expiration = $expiration->getTimestamp();
        }

        if (is_string($expiration)) {
            $expiration = strtotime($expiration);
        }

        return $this->privateDownloadUrl($path, $expiration);
    }

    public function privateDownloadUrl(string $path, int $expires = 3600): string
    {
        return $this->getAuthManager()->privateDownloadUrl($this->getUrl($path), $expires);
    }

    public function refresh(string $path)
    {
        if (is_string($path)) {
            $path = [$path];
        }

        // 将 $path 变成完整的 url
        $urls = array_map([$this, 'getUrl'], $path);

        return $this->getCdnManager()->refreshUrls($urls);
    }

    public function setBucketManager(BucketManager $manager): static
    {
        $this->bucketManager = $manager;

        return $this;
    }

    public function setUploadManager(UploadManager $manager): static
    {
        $this->uploadManager = $manager;

        return $this;
    }

    public function setAuthManager(Auth $manager): static
    {
        $this->authManager = $manager;

        return $this;
    }

    public function setCdnManager(CdnManager $manager): static
    {
        $this->cdnManager = $manager;

        return $this;
    }

    public function getBucketManager()
    {
        return $this->bucketManager ?: $this->bucketManager = new BucketManager($this->getAuthManager());
    }

    public function getAuthManager()
    {
        return $this->authManager ?: $this->authManager = new Auth($this->accessKey, $this->secretKey);
    }

    public function getUploadManager()
    {
        return $this->uploadManager ?: $this->uploadManager = new UploadManager();
    }

    public function getCdnManager()
    {
        return $this->cdnManager ?: $this->cdnManager = new CdnManager($this->getAuthManager());
    }

    public function getBucket(): string
    {
        return $this->bucket;
    }

    public function getUploadToken(string $key = null, int $expires = 3600, string $policy = null, string $strictPolice = null): string
    {
        return $this->getAuthManager()->uploadToken($this->bucket, $key, $expires, $policy, $strictPolice);
    }

    #[Pure]
    protected function normalizeFileInfo(array $stats): FileAttributes
    {
        return new FileAttributes(
            $stats['key'],
            $stats['fsize'] ?? null,
            null,
            isset($stats['putTime']) ? floor($stats['putTime'] / 10000000) : null,
            $stats['mimeType'] ?? null
        );
    }

    protected function normalizeHost($domain): string
    {
        if (0 !== stripos($domain, 'https://') && 0 !== stripos($domain, 'http://')) {
            $domain = "http://{$domain}";
        }

        return rtrim($domain, '/') . '/';
    }

    protected static function parseUrl($url): array
    {
        $result = [];

        // Build arrays of values we need to decode before parsing
        $entities = [
            '%21',
            '%2A',
            '%27',
            '%28',
            '%29',
            '%3B',
            '%3A',
            '%40',
            '%26',
            '%3D',
            '%24',
            '%2C',
            '%2F',
            '%3F',
            '%23',
            '%5B',
            '%5D',
            '%5C'
        ];
        $replacements = ['!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '$', ',', '/', '?', '#', '[', ']', '/'];

        // Create encoded URL with special URL characters decoded so it can be parsed
        // All other characters will be encoded
        $encodedURL = str_replace($entities, $replacements, urlencode($url));

        // Parse the encoded URL
        $encodedParts = parse_url($encodedURL);

        // Now, decode each value of the resulting array
        if ($encodedParts) {
            foreach ($encodedParts as $key => $value) {
                $result[$key] = urldecode(str_replace($replacements, $entities, $value));
            }
        }

        return $result;
    }
}
