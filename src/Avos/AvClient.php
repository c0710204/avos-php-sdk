<?php

namespace Avos;

// use Parse\Internal\Encodable;

/**
 * ParseClient - Main class for Parse initialization and communication
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
final class AvClient
{

  /**
   * Constant for the API Server Host Address.
   * @ignore
   */
  const HOST_NAME = 'https://leancloud.cn/1.1';

  /**
   * @var - String for applicationId.
   */
  private static $applicationId;

  /**
   * @var - String for REST API Key.
   */
  private static $appKey;

  /**
   * @var - String for Master Key.
   */
  private static $masterKey;

  /**
   * @var ParseStorageInterface Object for managing persistence
   */
  private static $storage;

  /**
   * Parse\Client::initialize, must be called before using Parse features.
   *
   * @param string $app_id     Parse Application ID
   * @param string $app_key   Parse REST API Key
   * @param string $master_key Parse Master Key
   *
   * @return null
   */
  public static function initialize($app_id, $app_key, $master_key)
  {
    echo 'Init ok';
    // ParseUser::registerSubclass();
    // ParseRole::registerSubclass();
    // ParseInstallation::registerSubclass();
    // self::$applicationId = $app_id;
    // self::$appKey = $app_key;
    // self::$masterKey = $master_key;
    // if (!static::$storage) {
    //   if (session_status() === PHP_SESSION_ACTIVE) {
    //     self::setStorage(new ParseSessionStorage());
    //   } else {
    //     self::setStorage(new ParseMemoryStorage());
    //   }
    // }
  }

}
