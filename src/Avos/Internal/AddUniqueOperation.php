<?php

namespace Avos\Internal;

use Avos\AVClient;
use Avos\AVException;

/**
 * Class AddUniqueOperation - Operation to add unique objects to an array key.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AddUniqueOperation implements FieldOperation {

    /**
     * @var - Array containing objects to add.
     */
    private $objects;

    /**
     * Creates an operation for adding unique values to an array key.
     *
     * @param array $objects Objects to add.
     *
     * @throws AVException
     */
    public function __construct($objects)
    {
        if (!is_array($objects))
        {
            throw new AVException("AddUniqueOperation requires an array.");
        }
        $this->objects = $objects;
    }

    /**
     * Returns the values for this operation.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->objects;
    }

    /**
     * Returns an associative array encoding of this operation.
     *
     * @return array
     */
    public function _encode()
    {
        return array(
            '__op'    => 'AddUnique',
            'objects' => AVClient::_encode($this->objects, true)
        );
    }

    /**
     * Merge this operation with the previous operation and return the result.
     *
     * @param FieldOperation $previous Previous Operation.
     *
     * @return FieldOperation Merged Operation.
     * @throws AVException
     */
    public function _mergeWithPrevious($previous)
    {
        if (!$previous)
        {
            return $this;
        }
        if ($previous instanceof DeleteOperation)
        {
            return new SetOperation($this->objects);
        }
        if ($previous instanceof SetOperation)
        {
            $oldValue = $previous->getValue();
            $result = $this->_apply($oldValue, null, null);

            return new SetOperation($result);
        }
        if ($previous instanceof AddUniqueOperation)
        {
            $oldList = $previous->getValue();
            $result = $this->_apply($oldList, null, null);

            return new AddUniqueOperation($result);
        }
        throw new AVException('Operation is invalid after previous operation.');
    }

    /**
     * Apply the current operation and return the result.
     *
     * @param mixed $oldValue Value prior to this operation.
     * @param array $obj Value being applied.
     * @param string $key Key this operation affects.
     *
     * @return array
     */
    public function _apply($oldValue, $obj, $key)
    {
        if (!$oldValue)
        {
            return $this->objects;
        }
        if (!is_array($oldValue))
        {
            $oldValue = (array) $oldValue;
        }
        foreach ($this->objects as $object)
        {
            if ($object instanceof AVObject && $object->getObjectId())
            {
                if (!$this->isAVObjectInArray($object, $oldValue))
                {
                    $oldValue[] = $object;
                }
            } else if (is_object($object))
            {
                if (!in_array($object, $oldValue, true))
                {
                    $oldValue[] = $object;
                }
            } else
            {
                if (!in_array($object, $oldValue, true))
                {
                    $oldValue[] = $object;
                }
            }
        }

        return $oldValue;
    }

    private function isAVObjectInArray($avObject, $oldValue)
    {
        foreach ($oldValue as $object)
        {
            if ($object instanceof AVObject && $object->getObjectId() != null)
            {
                if ($object->getObjectId() == $avObject->getObjectId())
                {
                    return true;
                }
            }
        }

        return false;
    }

}