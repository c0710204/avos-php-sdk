<?php

namespace Avos\Internal;

/**
 * Class Encodable - Interface for Avos Classes which provide an encode
 * method.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
interface Encodable {

    /**
     * Returns an associate array encoding of the implementing class.
     *
     * @return mixed
     */
    public function _encode();

}