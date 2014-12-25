<?php

namespace Avos\Internal;

/**
 * Class FieldOperation - Interface for all Avos Field Operations.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
interface FieldOperation extends Encodable
{

  /**
   * Applies the current operation and returns the result.
   *
   * @param mixed  $oldValue Value prior to this operation.
   * @param mixed  $object   Value for this operation.
   * @param string $key      Key to perform this operation on.
   *
   * @return mixed Result of the operation.
   */
  public function _apply($oldValue, $object, $key);

  /**
   * Merge this operation with a previous operation and return the new
   * operation.
   *
   * @param FieldOperation $previous Previous operation.
   *
   * @return FieldOperation Merged operation result.
   */
  public function _mergeWithPrevious($previous);

}