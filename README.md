Avos PHP SDK
-------------

由于[Leancloud](https://www.leancloud.cn/)没有官方的PHP SDK，自己平时项目中经常用到所以参考[Parse SDK](https://github.com/ParsePlatform/parse-php-sdk)做的一个版本，差异性主要是Leancloud和Parse的部分接口的区别。

安装
------------

使用Composer

```json
{
  "require": {
    "bigbing/avos-php-sdk": "dev-master"
  }
}
```

执行composer install，等待安装完成
引用加载文件

```php
require 'vendor/autoload.php';
```

注意: PHP需要5.4及以上版本。

初始化
---------------

从你在Leancloud创建的应用中找到应用Key，填入。

```php
AVClient::initialize( $app_id, $app_key, $master_key );
```

使用
-----

接口文件可以参考官方的[rest api](https://www.leancloud.cn/docs/rest_api.html)。

```php
use Avos\AVClient as AVClient;
use Avos\AVSessionStorage as AVSessionStorage;
use Avos\AVUser as AVUser;
use Avos\AVException as AVException;
use Avos\AVObject as AVObject;
```

Objects:

```php
session_start();
AVClient::initialize("app_id","rest_key", "master_key");
AVClient::setStorage(new AVSessionStorage());

$object = AVObject::create("TestObject");
$objectId = $object->getObjectId();
$php = $object->get("elephant");

// Set values:
$object->set("elephant", "php");
$object->set("today", new DateTime());
$object->setArray("mylist", [1, 2, 3]);
$object->setAssociativeArray(
  "languageTypes", array("php" => "awesome", "ruby" => "wtf")
);

// Save:
$object->save();
echo "saved object id:".$object->getObjectId();
```
[Get Composer]: https://getcomposer.org/download/
[Leancloud REST Guide]: https://www.leancloud.cn/docs/rest_api.html