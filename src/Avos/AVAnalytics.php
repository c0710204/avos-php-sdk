<?php

namespace Avos;

use \Exception;

/**
 * AVAnalytics - Handles sending app-open and custom analytics events
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVAnalytics
{

  /**
   * Tracks the occurrence of a custom event with additional dimensions.
   * Avos will store a data point at the time of invocation with the given
   * event name.
   *
   * Dimensions will allow segmentation of the occurrences of this custom
   * event. Keys and values should be strings, and will throw
   * otherwise.
   *
   * To track a user signup along with additional metadata, consider the
   * following:
   * <pre>
   * $dimensions = array(
   *  'gender' => 'm',
   *  'source' => 'web',
   *  'dayType' => 'weekend'
   * );
   * AVAnalytics::track('signup', $dimensions);
   * </pre>
   *
   * There is a default limit of 4 dimensions per event tracked.
   *
   * @param string $name       The name of the custom event
   * @param array  $dimensions The dictionary of segment information
   *
   * @throws \Exception
   * @return mixed
   */
  public static function track($name, $dimensions = array())
  {
    $name = trim($name);
    if (strlen($name) === 0) {
      throw new Exception('A name for the custom event must be provided.');
    }
    foreach ($dimensions as $key => $value) {
      if (!is_string($key) || !is_string($value)) {
        throw new Exception('Dimensions expected string keys and values.');
      }
    }
    return AVClient::_request(
      'POST',
      '/events/' . $name,
      null,
      static::_toSaveJSON($dimensions)
    );
  }

  /**
   * @ignore
   */
  public static function _toSaveJSON($data)
  {
    return json_encode(
      array(
        'dimensions' => $data
      ),
      JSON_FORCE_OBJECT
    );
  }

}