## Flysystem Adapter for Upyun Cloud Storage

:floppy_disk: Flysystem adapter for the Upyun cloud storage.

> This package is modified from [overtrue/flysystem-qiniu](https://github.com/overtrue/flysystem-qiniu)

# Requirement

-   PHP >= 8.0.2

# Installation

```shell
$ composer require "wispx/flysystem-upyun"
```

# Usage

```php
use League\Flysystem\Filesystem;
use WispX\Flysystem\Upyun\UpyunAdapter;

$service = 'xxxxxx';
$operator = 'xxxxxx';
$password = 'xxxxxx';
$domain = 'xxxx.test.upcdn.net'; // or with protocol: https://xxxx.test.upcdn.net

$adapter = new UpyunAdapter($service, $operator, $password, $domain);

$flysystem = new League\Flysystem\Filesystem($adapter);
```

## API

```php
bool $flysystem->write('file.md', 'contents');
bool $flysystem->write('file.md', 'http://httpbin.org/robots.txt');
bool $flysystem->writeStream('file.md', fopen('path/to/your/local/file.jpg', 'r'));
bool $flysystem->copy('foo.md', 'foo2.md');
bool $flysystem->delete('file.md');
bool $flysystem->has('file.md');
bool $flysystem->fileExists('file.md');
bool $flysystem->directoryExists('path/to/dir');
string|false $flysystem->read('file.md');
array $flysystem->listContents();
int $flysystem->fileSize('file.md');
string $flysystem->mimeType('file.md');
```

Adapter extended methods:

```php
string $adapter->getUrl('file.md');
```

# License

MIT
