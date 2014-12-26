<?php

namespace Avos;

/**
 * AVSessionStorage - Uses PHP session support for persistent storage.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVSessionStorage implements AVStorageInterface
{

  /**
   * @var string Avos will store its values in a specific key.
   */
  private $storageKey = 'avData';

  public function __construct()
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      throw new AVException(
        'PHP session_start() must be called first.'
      );
    }
    if (!isset($_SESSION[$this->storageKey])) {
      $_SESSION[$this->storageKey] = array();
    }
  }

  public function set($key, $value)
  {
    $_SESSION[$this->storageKey][$key] = $value;
  }

  public function remove($key)
  {
    unset($_SESSION[$this->storageKey][$key]);
  }

  public function get($key)
  {
    if (isset($_SESSION[$this->storageKey][$key])) {
      return $_SESSION[$this->storageKey][$key];
    }
    return null;
  }

  public function clear()
  {
    $_SESSION[$this->storageKey] = array();
  }

  public function save()
  {
    // No action required.  PHP handles persistence for $_SESSION.
    return;
  }

  public function getKeys()
  {
    return array_keys($_SESSION[$this->storageKey]);
  }

  public function getAll()
  {
    return $_SESSION[$this->storageKey];
  }

}