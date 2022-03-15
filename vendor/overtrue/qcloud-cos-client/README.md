<img src="https://github.com/overtrue/qcloud-cos-client/workflows/Test/badge.svg" align="right" />

<h1 align="center">QCloud COS Client</h1>

对象存储（Cloud Object Storage，COS）是腾讯云提供的一种存储海量文件的分布式存储服务，具有高扩展性、低成本、可靠安全等优点。通过控制台、API、SDK 和工具等多样化方式，用户可简单、快速地接入 COS，进行多格式文件的上传、下载和管理，实现海量数据存储和管理。

> :star: 官方文档：https://cloud.tencent.com/document/product/436


## 安装

环境要求：

- PHP >= 7.4
- ext-libxml
- ext-simplexml
- ext-json
- ext-dom

```shell
$ composer require overtrue/qcloud-cos-client -vvv
```

## 配置

配置前请了解官方名词解释：[文档中心 > 对象存储 > API 文档 > 简介：术语信息](https://cloud.tencent.com/document/product/436/7751#.E6.9C.AF.E8.AF.AD.E4.BF.A1.E6.81.AF)

```php
$config = [
    // 必填，app_id、secret_id、secret_key 
    // 可在个人秘钥管理页查看：https://console.cloud.tencent.com/capi
    'app_id' => 10020201024, 
    'secret_id' => 'AKIDsiQzQla780mQxLLU2GJCxxxxxxxxxxx', 
    'secret_key' => 'b0GMH2c2NXWKxPhy77xhHgwxxxxxxxxxxx',
    
    // 可选(批量处理接口必填)，腾讯云账号 ID
    // 可在腾讯云控制台账号信息中查看：https://console.cloud.tencent.com/developer
    'uin' => '10000*******', 
    
    // 可选，地域列表请查看 https://cloud.tencent.com/document/product/436/6224
    'region' => 'ap-guangzhou', 

    // 可选，仅在调用不同的接口时按场景必填
    'bucket' => 'example', // 使用 Bucket 接口时必填
    
    // 可选，签名有效期，默认 60 分钟
    'signature_expires' => '+60 minutes', 
    
    // 可选，guzzle 配置
    // 参考：https://docs.guzzlephp.org/en/7.0/request-options.html
    'guzzle' => [
        // ...
    ],
];
```

## 使用

您可以分两种方式使用此 SDK：

- **ServiceClient、BucketClient、ObjectClient、JobClient** - 封装了具体 API 的类调用指定业务的 API。
- **Client** - 基于最基础的 HTTP 类封装调用 COS 全部 API。

在使用前我们强烈建议您仔细阅读[官方 API 文档](https://cloud.tencent.com/document/product/436)，以减少不必要的时间浪费。

### 返回值

所有的接口调用都会返回 [`Overtrue\CosClient\Http\Response`](https://github.com/overtrue/qcloud-cos-client/blob/master/src/Http/Response.php) 对象，改对象提供了以下便捷方法：

```php
array|null $response->toArray(); // 获取响应内容数组转换结果                                                
object $response->toObject(); // 获取对象格式的返回值
bool $response->isXML(); // 检测返回内容是否为 XML
string $response->getContents(); // 获取原始返回内容
```

你也可以直接把 `$response` 当成数组访问：`$response['ListBucketResult']`

## ServiceClient

```php
use Overtrue\CosClient\ServiceClient;

$config = [
    // 请参考配置说明
];
$service = new ServiceClient($config);

$service->getBuckets();
$service->getBuckets('ap-guangzhou');
```

## JobClient

```php
use Overtrue\CosClient\JobClient;

$config = [
    // 请参考配置说明
];

$job = new JobClient($config);

## API

$job->getJobs(array $query = []);
$job->createJob(array $body);
$job->describeJob(string $id);
$job->updateJobPriority(string $id, int $priority);
$job->updateJobStatus(string $id, array $query);
```

## BucketClient

```php
use Overtrue\CosClient\BucketClient;

$config = [
    // 请参考配置说明
    'bucket' => 'example',
    'region' => 'ap-guangzhou',
];

$bucket = new BucketClient($config);

## API

$bucket->putBucket(array $body); 
$bucket->headBucket(); 
$bucket->deleteBucket();
$bucket->getObjects(array $query = []);
$bucket->getObjectVersions(array $query = []);

// Versions
$bucket->putVersions(array $body);
$bucket->getVersions();

// ACL
$bucket->putACL(array $body, array $headers = [])
$bucket->getACL();

// CORS
$bucket->putCORS(array $body);
$bucket->getCORS();
$bucket->deleteCORS();

// Lifecycle
$bucket->putLifecycle(array $body);
$bucket->getLifecycle();
$bucket->deleteLifecycle();

// Policy
$bucket->putPolicy(array $body);
$bucket->getPolicy();
$bucket->deletePolicy();

// Referer
$bucket->putReferer(array $body);
$bucket->getReferer();

// Taging
$bucket->putTaging(array $body);
$bucket->getTaging();
$bucket->deleteTaging();

// Website
$bucket->putWebsite(array $body);
$bucket->getWebsite();
$bucket->deleteWebsite();

// Inventory
$bucket->putInventory(string $id, array $body)
$bucket->getInventory(string $id)
$bucket->getInventoryConfigurations(?string $nextContinuationToken = null)
$bucket->deleteInventory(string $id)

// Versioning
$bucket->putVersioning(array $body);
$bucket->getVersioning();

// Replication
$bucket->putReplication(array $body);
$bucket->getReplication();
$bucket->deleteReplication();

// Logging
$bucket->putLogging(array $body);
$bucket->getLogging();

// Accelerate
$bucket->putAccelerate(array $body);
$bucket->getAccelerate();

// Encryption
$bucket->putEncryption(array $body);
$bucket->getEncryption();
$bucket->deleteEncryption();
```

## ObjectClient

```php
use Overtrue\CosClient\ObjectClient;

$config = [
    // 请参考配置说明
    'bucket' => 'example',
    'region' => 'ap-guangzhou',
]);

$object = new ObjectClient($config);

$object->putObject(string $key, string $body, array $headers = []);
$object->copyObject(string $key, array $headers = []);
$object->getObject(string $key, array $query = [], array $headers = []);
$object->headObject(string $key, string $versionId, array $headers = []);
$object->restoreObject(string $key, string $versionId, array $body);
$object->selectObjectContents(string $key, array $body);
$object->deleteObject(string $key, string $versionId);
$object->deleteObjects(array $body);

$object->putObjectACL(string $key, array $body, array $headers = []);
$object->getObjectACL(string $key);

$object->putObjectTagging(string $key, string $versionId, array $body);
$object->getObjectTagging(string $key, string $versionId);
$object->deleteObjectTagging(string $key, string $versionId);

$object->createUploadId(string $key, array $headers = []);
$object->putPart(string $key, int $partNumber, string $uploadId, string $body, array $headers = []);
$object->copyPart(string $key, int $partNumber, string $uploadId, array $headers = []);
$object->markUploadAsCompleted(string $key, string $uploadId, array $body);
$object->markUploadAsAborted(string $key, string $uploadId);
$object->getUploadJobs(array $query = []);
$object->getUploadedParts(string $key, string $uploadId, array $query = []);

$object->getObjectUrl(string $key)
$object->getObjectSignedUrl(string $key, string $expires = '+60 minutes')
```

## 异常处理

```php
use Overtrue\CosClient\BucketClient;

$client = new BucketClient([
    'app_id' => 123456789,
    'secret_id' => 'AKIDsiQzQla780mQxLLUxxxxxxx',
    'secret_key' => 'b0GMH2c2NXWKxPhy77xxxxxxxx',
    'region' => 'ap-guangzhou',
    'bucket' => 'example',
]);

try {
    $client->getObjects();
} catch(\Throwable $e) {
    var_dump($e->getResponse()->toArray());     
}
```

其中 `$e->getResponse()` 为 `\Overtrue\CosClient\Http\Response` 示例，你也可以通过 `$e->getRequest()` 获取请求对象。

## 测试

你可以使用类提供的 `spy` 方法来创建一个测试对象：

```php

use Overtrue\CosClient\Http\Response;
use Overtrue\CosClient\ServiceClient;

$service = ServiceClient::spy();

$mockResponse = Response::create(200, [], '<ListAllMyBucketsResult>
                                               <Buckets>
                                                   <Bucket>
                                                       <Name>examplebucket1-1250000000</Name>
                                                       <Location>ap-beijing</Location>
                                                       <CreationDate>2019-05-24T11:49:50Z</CreationDate>
                                                   </Bucket>
                                               </Buckets>
                                          </ListAllMyBucketsResult>');

$service->shouldReceive('listBuckets')
        ->with('zp-guangzhou')
        ->once()
        ->andReturn($mockResponse);
```

更多测试写法请阅读：[Mockery 官方文档](http://docs.mockery.io/en/latest/index.html)

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/vendor/package/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/vendor/package/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT
