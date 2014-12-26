<?php

namespace Avos;

/**
 * AVCloud - Facilitates calling Avos Cloud functions
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVCloud {

    /**
     * Makes a call to a Cloud function
     *
     * @param string $name Cloud function name
     * @param array $data Parameters to pass
     * @param boolean $useMasterKey Whether to use the Master Key
     *
     * @return mixed
     */
    public static function run($name, $data = array(), $useMasterKey = false)
    {
        $sessionToken = null;
        if (AVUser::getCurrentUser())
        {
            $sessionToken = AVUser::getCurrentUser()->getSessionToken();
        }
        $response = AVClient::_request('POST', '/functions/' . $name, $sessionToken,
            json_encode(AVClient::_encode($data, null, false)), $useMasterKey);

        return AVClient::_decode($response['result']);
    }

}