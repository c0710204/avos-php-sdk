<?php

namespace Avos;

/**
 * AVGeoPoint - Representation of a Avos GeoPoint object.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVGeoPoint implements \Avos\Internal\Encodable {

    /**
     * @var - Float value for latitude.
     */
    private $latitude;
    /**
     * @var - Float value for longitude.
     */
    private $longitude;

    /**
     * Create a Avos GeoPoint object.
     *
     * @param float $lat Latitude.
     * @param float $lon Longitude.
     */
    public function __construct($lat, $lon)
    {
        $this->setLatitude($lat);
        $this->setLongitude($lon);
    }

    /**
     * Returns the Latitude value for this GeoPoint.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set the Latitude value for this GeoPoint.
     *
     * @param $lat
     *
     * @throws AVException
     */
    public function setLatitude($lat)
    {
        if ($lat > 90.0 || $lat < -90.0)
        {
            throw new AVException("Latitude must be within range [-90.0, 90.0]");
        }
        $this->latitude = $lat;
    }

    /**
     * Returns the Longitude value for this GeoPoint.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set the Longitude value for this GeoPoint.
     *
     * @param $lon
     *
     * @throws AVException
     */
    public function setLongitude($lon)
    {
        if ($lon > 180.0 || $lon < -180.0)
        {
            throw new AVException("Longitude must be within range [-180.0, 180.0]");
        }
        $this->longitude = $lon;
    }

    /**
     * Encode to associative array representation
     *
     * @return array
     * @ignore
     */
    public function _encode()
    {
        return array(
            '__type'    => 'GeoPoint',
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude
        );
    }
}
