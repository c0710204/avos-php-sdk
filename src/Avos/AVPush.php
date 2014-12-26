<?php

namespace Avos;

/**
 * AVPush - Handles sending push notifications with Avos
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVPush {

    /**
     * Sends a push notification.
     *
     * @param array $data The data of the push notification.  Valid fields
     *   are:
     *     channels - An Array of channels to push to.
     *     push_time - A Date object for when to send the push.
     *     expiration_time -  A Date object for when to expire
     *         the push.
     *     expiration_interval - The seconds from now to expire the push.
     *     where - A AVQuery over AVInstallation that is used to match
     *         a set of installations to push to.
     *     data - The data to send as part of the push
     * @param boolean $useMasterKey Whether to use the Master Key for the request
     *
     * @throws \Exception, AVException
     * @return mixed
     */
    public static function send($data, $useMasterKey = false)
    {
        if (isset($data['expiration_time']) && isset($data['expiration_interval']))
        {
            throw new \Exception('Both expiration_time and expiration_interval can\'t be set.');
        }
        if (isset($data['where']))
        {
            if ($data['where'] instanceof AVQuery)
            {
                $data['where'] = $data['where']->_getOptions()['where'];
            } else
            {
                throw new \Exception('Where parameter for Avos Push must be of type AVQuery');
            }
        }
        if (isset($data['push_time']))
        {
            //Local push date format is different from iso format generally used in Avos
            //Schedule does not work if date format not correct
            $data['push_time'] = AVClient::getLocalPushDateFormat($data['push_time']);
        }
        if (isset($data['expiration_time']))
        {
            $data['expiration_time'] = AVClient::_encode($data['expiration_time'], false)['iso'];
        }

        return AVClient::_request('POST', '/push', null, json_encode($data), $useMasterKey);
    }

}