<?php
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

require 'vendor/autoload.php';

use Avos\AVClient as AVClient;
use Avos\AVSessionStorage as AVSessionStorage;
use Avos\AVUser as AVUser;
use Avos\AVException as AVException;

session_start();
AVClient::initialize("10ono71kr81a8cp1kvskbnqev6ryr1ogccu3cmwheungsw71",
    "24rlcmvk2hx2zqi8nehtv7f0lm6ej4vl90thkbxrb4ssme3q", "l98q00w0w4nfdj9h0c9t8fzsc09g3lca4vhqjf9g9qxuwr0s");
AVClient::setStorage(new AVSessionStorage());

try {
    AVUser::logIn('18014828927','12344444');
    echo "login";
} catch (AVException $ex) {
	var_dump($ex->getMessage());
}