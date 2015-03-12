<?php

namespace Avos;

use Avos\Internal\Encodable;

/**
 * AVClient - Main class for Avos initialization and communication
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
final class AVClient {

    /**
     * Constant for the API Server Host Address.
     *
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
     * @var AVStorageInterface Object for managing persistence
     */
    private static $storage;

    /**
     * Avos\Client::initialize, must be called before using Avos features.
     *
     * @param string $app_id Avos Application ID
     * @param string $app_key Avos REST API Key
     * @param string $master_key Avos Master Key
     *
     * @return null
     */
    public static function initialize($app_id, $app_key, $master_key)
    {
        AVUser::registerSubclass();
        AVRole::registerSubclass();
        AVInstallation::registerSubclass();
        self::$applicationId = $app_id;
        self::$appKey = $app_key;
        self::$masterKey = $master_key;;
        if (!static::$storage)
        {
            if (session_status() === PHP_SESSION_ACTIVE)
            {
                self::setStorage(new AVSessionStorage());
            } else
            {
                self::setStorage(new AVMemoryStorage());
            }
        }
    }

    /**
     * AVClient::_encode, internal method for encoding object values.
     *
     * @param mixed $value Value to encode
     * @param bool $allowAVObjects Allow nested objects
     *
     * @return mixed Encoded results.
     *
     * @throws \Exception
     * @ignore
     */
    public static function _encode($value, $allowAVObjects)
    {
        if ($value instanceof \DateTime)
        {
            return array(
                '__type' => 'Date',
                'iso'    => self::getProperDateFormat($value)
            );
        }

        if ($value instanceof \stdClass)
        {
            return $value;
        }

        if ($value instanceof AVObject)
        {
            if (!$allowAVObjects)
            {
                throw new \Exception('AVObjects not allowed here.');
            }

            return $value->_toPointer();
        }

        if ($value instanceof Encodable)
        {
            return $value->_encode();
        }

        if (is_array($value))
        {
            return self::_encodeArray($value, $allowAVObjects);
        }

        if (!is_scalar($value) && $value !== null)
        {
            throw new \Exception('Invalid type encountered.');
        }

        return $value;
    }

    /**
     * AVClient::_decode, internal method for decoding server responses.
     *
     * @param mixed $data The value to decode
     *
     * @return mixed
     * @ignore
     */
    public static function _decode($data)
    {
        // The json decoded response from Avos will make JSONObjects into stdClass
        //   objects.  We'll change it to an associative array here.
        if ($data instanceof \stdClass)
        {
            $tmp = (array) $data;
            if (!empty($tmp))
            {
                return self::_decode(get_object_vars($data));
            }
        }

        if (!$data && !is_array($data))
        {
            return null;
        }

        if (is_array($data))
        {
            $typeString = (isset($data['__type']) ? $data['__type'] : null);

            if ($typeString === 'Date')
            {
                return new \DateTime($data['iso']);
            }

            if ($typeString === 'Bytes')
            {
                return base64_decode($data['base64']);
            }

            if ($typeString === 'Pointer')
            {
                return AVObject::create($data['className'], $data['objectId']);
            }

            if ($typeString === 'File')
            {
                return AVFile::_createFromServer($data['name'], $data['url']);
            }

            if ($typeString === 'GeoPoint')
            {
                return new AVGeoPoint($data['latitude'], $data['longitude']);
            }

            if ($typeString === 'Object')
            {
                $output = AVObject::create($data['className']);
                $output->_mergeAfterFetch($data);

                return $output;
            }

            if ($typeString === 'Relation')
            {
                return $data;
            }

            $newDict = array();
            foreach ($data as $key => $value)
            {
                $newDict[$key] = static::_decode($value);
            }

            return $newDict;

        }

        return $data;
    }

    /**
     * AVClient::_encodeArray, internal method for encoding arrays.
     *
     * @param array $value Array to encode.
     * @param bool $allowAVObjects Allow nested objects.
     *
     * @return array Encoded results.
     * @ignore
     */
    public static function _encodeArray($value, $allowAVObjects)
    {
        $output = array();
        foreach ($value as $key => $item)
        {
            $output[$key] = self::_encode($item, $allowAVObjects);
        }

        return $output;
    }

    /**
     * Avos\Client::_request, internal method for communicating with Avos.
     *
     * @param string $method HTTP Method for this request.
     * @param string $relativeUrl REST API Path.
     * @param null $sessionToken Session Token.
     * @param null $data Data to provide with the request.
     * @param bool $useMasterKey Whether to use the Master Key.
     *
     * @return mixed          Result from Avos API Call.
     * @throws \Exception
     * @ignore
     */
    public static function _request(
        $method,
        $relativeUrl,
        $sessionToken = null,
        $data = null,
        $useMasterKey = false
    ) {
        if ($data === '[]')
        {
            $data = '{}';
        }
        self::assertAVInitialized();
        $headers = self::_getRequestHeaders($sessionToken, $useMasterKey);

        $url = self::HOST_NAME . $relativeUrl;
        if ($method === 'GET' && !empty($data))
        {
            $url .= '?' . http_build_query($data);
        }
        $rest = curl_init();
		curl_setopt($rest,CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($rest,CURLOPT_SSL_VERIFYHOST , false);
        curl_setopt($rest, CURLOPT_URL, $url);
        curl_setopt($rest, CURLOPT_RETURNTRANSFER, 1);
        if ($method === 'POST')
        {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($rest, CURLOPT_POST, 1);
            curl_setopt($rest, CURLOPT_POSTFIELDS, $data);
        }
        if ($method === 'PUT')
        {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($rest, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($rest, CURLOPT_POSTFIELDS, $data);
        }
        if ($method === 'DELETE')
        {
            curl_setopt($rest, CURLOPT_CUSTOMREQUEST, $method);
        }
        curl_setopt($rest, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($rest);
        $status = curl_getinfo($rest, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($rest, CURLINFO_CONTENT_TYPE);
        if (curl_errno($rest))
        {
            throw new AVException(curl_error($rest), curl_errno($rest));
        }
        curl_close($rest);
        if (strpos($contentType, 'text/html') !== false)
        {
            throw new AVException('Bad Request', -1);
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['error']))
        {
            throw new AVException($decoded['error'], isset($decoded['code']) ? $decoded['code'] : 0);
        }

        return $decoded;

    }

    /**
     * AVClient::setStorage, will update the storage object used for
     * persistence.
     *
     * @param AVStorageInterface $storageObject
     *
     * @return null
     */
    public static function setStorage(AVStorageInterface $storageObject)
    {
        self::$storage = $storageObject;
    }

    /**
     * AVClient::getStorage, will return the storage object used for
     * persistence.
     *
     * @return AVStorageInterface
     */
    public static function getStorage()
    {
        return self::$storage;
    }

    /**
     * AVClient::_unsetStorage, will null the storage object.
     *
     * Without some ability to clear the storage objects, all test cases would
     *   use the first assigned storage object.
     *
     * @return null
     * @ignore
     */
    public static function _unsetStorage()
    {
        self::$storage = null;
    }

    private static function assertAVInitialized()
    {
        if (self::$applicationId === null)
        {
            throw new \Exception('You must call AvClient::initialize() before making any requests.');
        }
    }

    /**
     * @param $sessionToken
     * @param $useMasterKey
     *
     * @return array
     * @ignore
     */
    public static function _getRequestHeaders($sessionToken, $useMasterKey)
    {
        $headers = array('X-AVOSCloud-Application-Id: ' . self::$applicationId);
        if ($sessionToken)
        {
            $headers[] = 'X-AVOSCloud-Session-Token: ' . $sessionToken;
        }
        if ($useMasterKey)
        {
            $headers[] = 'X-AVOSCloud-Master-Key: ' . self::$masterKey;
        } else
        {
            $headers[] = 'X-AVOSCloud-Application-Key: ' . self::$appKey;
        }
        /**
         * Set an empty Expect header to stop the 100-continue behavior for post
         *   data greater than 1024 bytes.
         *   http://pilif.github.io/2007/02/the-return-of-except-100-continue/
         */
        $headers[] = 'Expect: ';

        return $headers;
    }

    /**
     * Get a date value in the format stored on Avos.
     *
     * All the SDKs do some slightly different date handling.
     * PHP provides 6 digits for the microseconds (u) so we have to chop 3 off.
     *
     * @param \DateTime $value DateTime value to format.
     *
     * @return string
     */
    public static function getProperDateFormat($value)
    {
        $dateFormatString = 'Y-m-d\TH:i:s.u';
        $date = date_format($value, $dateFormatString);
        $date = substr($date, 0, -3) . 'Z';

        return $date;
    }

    /**
     * Get a date value in the format to use in Local Push Scheduling on Avos.
     *
     * All the SDKs do some slightly different date handling.
     * Format from Avos doc: an ISO 8601 date without a time zone, i.e. 2014-10-16T12:00:00 .
     *
     * @param \DateTime $value DateTime value to format.
     *
     * @return string
     */
    public static function getLocalPushDateFormat($value)
    {
        $dateFormatString = 'Y-m-d\TH:i:s';
        $date = date_format($value, $dateFormatString);

        return $date;
    }
}
