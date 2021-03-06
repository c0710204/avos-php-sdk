<?php

namespace Avos;

/**
 * AVMemoryStorage - Uses non-persisted memory for storage.
 * This is used by default if a PHP Session is not active.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVMemoryStorage implements AVStorageInterface {

    /**
     * @var array
     */
    private $storage = array();

    public function set($key, $value)
    {
        $this->storage[$key] = $value;
    }

    public function remove($key)
    {
        unset($this->storage[$key]);
    }

    public function get($key)
    {
        if (isset($this->storage[$key]))
        {
            return $this->storage[$key];
        }

        return null;
    }

    public function clear()
    {
        $this->storage = array();
    }

    public function save()
    {
        // No action required.
        return;
    }

    public function getKeys()
    {
        return array_keys($this->storage);
    }

    public function getAll()
    {
        return $this->storage;
    }

}