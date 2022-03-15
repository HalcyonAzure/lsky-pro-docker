<?php

declare(strict_types=1);

namespace Zing\Flysystem\Oss;

use League\Flysystem\Visibility;
use OSS\OssClient;

class PortableVisibilityConverter implements VisibilityConverter
{
    /**
     * @var string
     */
    private const PUBLIC_ACL = OssClient::OSS_ACL_TYPE_PUBLIC_READ;

    /**
     * @var string
     */
    private const PRIVATE_ACL = OssClient::OSS_ACL_TYPE_PRIVATE;

    /**
     * @var string
     */
    private $defaultForDirectories;

    /**
     * @var string
     */
    private $default;

    public function __construct(
        string $default = Visibility::PUBLIC,
        string $defaultForDirectories = Visibility::PUBLIC
    ) {
        $this->defaultForDirectories = $defaultForDirectories;
        $this->default = $default;
    }

    public function visibilityToAcl(string $visibility): string
    {
        if ($visibility === Visibility::PUBLIC) {
            return self::PUBLIC_ACL;
        }

        return self::PRIVATE_ACL;
    }

    public function aclToVisibility(string $acl): string
    {
        switch ($acl) {
            case OssClient::OSS_ACL_TYPE_PRIVATE:
                return Visibility::PRIVATE;

            case OssClient::OSS_ACL_TYPE_PUBLIC_READ:
            case OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE:
                return Visibility::PUBLIC;

            default:
                return $this->default;
        }
    }

    public function defaultForDirectories(): string
    {
        return $this->defaultForDirectories;
    }

    public function getDefault(): string
    {
        return $this->default;
    }
}
