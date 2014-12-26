<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

require 'vendor/autoload.php';

use Avos\AVClient as AVClient;
use Avos\AVSessionStorage as AVSessionStorage;
use Avos\AVUser as AVUser;
use Avos\AVException as AVException;

session_start();
AVClient::initialize('your_app_id', 'your_app_key', 'your_master_key');
AVClient::setStorage(new AVSessionStorage());

try
{
    AVUser::logIn('username', 'password');
    echo "login";
} catch (AVException $ex)
{
    var_dump($ex->getMessage());
}