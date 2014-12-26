<?php

namespace Avos;

/**
 * AVAggregateException - Multiple error condition
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVAggregateException extends AVException {

    private $errors;

    /**
     * Constructs a Avos\AVAggregateException
     *
     * @param string $message Message for the Exception.
     * @param array $errors Collection of error values.
     * @param \Exception $previous Previous exception.
     */
    public function __construct($message, $errors = array(), $previous = null)
    {
        parent::__construct($message, 600, $previous);
        $this->errors = $errors;
    }

    /**
     * Return the aggregated errors that were thrown.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}