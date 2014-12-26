<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

require 'vendor/autoload.php';

use Avos\AVClient as AVClient;
use Avos\AVSessionStorage as AVSessionStorage;
use Avos\AVUser as AVUser;
use Avos\AVException as AVException;
use Avos\AVObject as AVObject;

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