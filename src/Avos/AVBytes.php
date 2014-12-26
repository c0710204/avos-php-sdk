<?php

namespace Avos;

/**
 * AVBytes - Representation of a Byte array for storage on a Avos Object.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVBytes implements Internal\Encodable
{

  /**
   * @var - byte array
   */
  private $byteArray;

  /**
   * Create a AVBytes object with a given byte array.
   *
   * @param array $byteArray
   *
   * @return AVBytes
   */
  public static function createFromByteArray(array $byteArray)
  {
    $bytes = new AVBytes();
    $bytes->setByteArray($byteArray);
    return $bytes;
  }

  /**
   * Create a AVBytes object with a given base 64 encoded data string
   *
   * @param string $base64Data
   *
   * @return AVBytes
   */
  public static function createFromBase64Data($base64Data)
  {
    $bytes = new AVBytes();
    $bytes->setBase64Data($base64Data);
    return $bytes;
  }

  private function setBase64Data($base64Data)
  {
    $byteArray = unpack('C*', base64_decode($base64Data));
    $this->setByteArray($byteArray);
  }

  private function setByteArray(array $byteArray)
  {
    $this->byteArray = $byteArray;
  }

  /**
   * Encode to associative array representation
   *
   * @return array
   * @ignore
   */
  public function _encode()
  {
    $data = "";
    foreach ($this->byteArray as $byte) {
      $data .= chr($byte);
    }
    return array(
      '__type' => 'Bytes',
      'base64' => base64_encode($data)
    );
  }
}
