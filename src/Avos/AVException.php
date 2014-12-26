<?php

namespace Avos;

/**
 * AVException - Wrapper for \Exception class
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVException extends \Exception
{

  /**
   * Constructs a Avos\Exception
   *
   * @param string     $message  Message for the Exception.
   * @param int        $code     Error code.
   * @param \Exception $previous Previous Exception.
   */
  public function __construct($message, $code = 0,
                              \Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }

}