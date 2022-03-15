<?php

declare(strict_types=1);

namespace Zing\Flysystem\Oss;

interface VisibilityConverter
{
    public function visibilityToAcl(string $visibility): string;

    public function aclToVisibility(string $acl): string;

    public function defaultForDirectories(): string;
}
