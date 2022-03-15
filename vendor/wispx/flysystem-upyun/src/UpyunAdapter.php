<?php

namespace WispX\Flysystem\Upyun;

use JetBrains\PhpStorm\Pure;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use Upyun\Upyun;

class UpyunAdapter implements FilesystemAdapter
{
    protected ?Upyun $client = null;

    public function __construct(
        protected string $service,
        protected string $operator,
        protected string $password,
        protected string $domain
    ) {
    }

    public function fileExists(string $path): bool
    {
        try {
            return $this->client()->has($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function directoryExists(string $path): bool
    {
        return $this->fileExists($path);
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            $this->client()->write($path, $contents);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
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
        try {
            return $this->client()->read($path);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path);
        }
    }

    /**
     * @param string $path
     * @return resource
     */
    public function readStream(string $path)
    {
        try {
            $stream = tmpfile();
            fwrite($stream, $this->client()->read($path));
            rewind($stream);
            return $stream;
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path);
        }
    }

    public function delete(string $path): void
    {
        try {
            if (! $this->client()->delete($path)) {
                throw new \Exception('delete fail.');
            }
        } catch (\Exception $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage());
        }
    }

    public function deleteDirectory(string $path): void
    {
        $this->delete($path);
    }

    public function createDirectory(string $path, Config $config): void
    {
        try {
            $this->client()->createDir($path);
        } catch (\Exception $e) {
            throw UnableToCreateDirectory::atLocation($path, $e->getMessage());
        }
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
        $mimetype = $this->getMimetype($path);

        if (! $mimetype) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return new FileAttributes($path, mimeType: $mimetype);
    }

    public function lastModified(string $path): FileAttributes
    {
        $lastModified = $this->getTimestamp($path);

        if (! $lastModified) {
            throw UnableToRetrieveMetadata::lastModified($path);
        }

        return new FileAttributes($path, lastModified: $lastModified);
    }

    public function fileSize(string $path): FileAttributes
    {
        $size = $this->getSize($path);

        if (! $size) {
            throw UnableToRetrieveMetadata::fileSize($path);
        }

        return new FileAttributes($path, fileSize: $size);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $list = [];

        try {
            $result = $this->client()->read($path, null, ['X-List-Limit' => 100, 'X-List-Iter' => null]);
            foreach ($result['files'] as $files) {
                $list[] = $this->normalizeFileInfo($files, $path);
            }
        } catch (\Exception $e) {
            return [];
        }

        return $list;
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->client()->move($source, $destination);
        } catch (\Exception $e) {
            throw UnableToMoveFile::fromLocationTo($source, $destination);
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->client()->copy($source, $destination);
        } catch (\Exception $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination);
        }
    }

    public function getUrl(string $path): string
    {
        return $this->normalizeHost($this->domain) . ltrim($path, '/');
    }

    public function getMetadata(string $path): array
    {
        return $this->client()->info($path);
    }

    public function getType(string $path): string
    {
        $response = $this->getMetadata($path);

        return $response['x-upyun-file-type'] ?? '';
    }

    public function getMimetype(string $path): string
    {
        return $this->client()->getMimetype($path);
    }

    public function getSize(string $path): string
    {
        $response = $this->getMetadata($path);

        return $response['x-upyun-file-size'] ?? '';
    }

    public function getTimestamp(string $path): string
    {
        $response = $this->getMetadata($path);

        return $response['x-upyun-file-date'] ?? '';
    }

    protected function client(): Upyun
    {
        if (is_null($this->client)) {
            $config = new \Upyun\Config($this->service, $this->operator, $this->password);
            $config->useSsl = true;
            $this->client = new Upyun($config);
        }

        return $this->client;
    }

    #[Pure]
    protected function normalizeFileInfo(array $stats, string $directory): FileAttributes
    {
        $filePath = ltrim($directory . '/' . $stats['name'], '/');

        return new FileAttributes(
            $filePath,
            $stats['size'],
            null,
            $stats['time'] ?? null,
            $this->getMimetype($filePath),
        );
    }

    protected function normalizeHost($domain): string
    {
        if (0 !== stripos($domain, 'https://') && 0 !== stripos($domain, 'http://')) {
            $domain = "http://{$domain}";
        }

        return rtrim($domain, '/') . '/';
    }
}
