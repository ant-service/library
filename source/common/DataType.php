<?php

namespace AntService\Src\Common;

use SimpleXMLElement;

class DataType
{

    public static function convertArray($variate, $delimiter = ',')
    {
        if (is_string($variate)) {
            if ($variate === '') {
                return array();
            }
            $objcet = simplexml_load_string($variate, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR);
            if (is_object($objcet)) {
                return self::convertArray($objcet, $delimiter);
            }
            $array = json_decode($variate, true);
            if (is_array($array)) {
                return self::convertArray($array, $delimiter);
            }
            $array = explode($delimiter, $variate);
            if (is_array($array) && count($array) > 1) {
                return self::convertArray($array, $delimiter);
            }
            return self::convertArray(array($variate), $delimiter);
        } elseif (is_numeric($variate)) {
            if (is_float($variate)) {
                return self::convertArray((string) $variate, $delimiter);
            }
            return array($variate);
        } elseif (is_object($variate)) {
            return self::convertArray(json_encode($variate), $delimiter);
        } elseif (is_null($variate)) {
            return null;
        } else {
            return $variate;
        }
    }

    public static function convertObject($variate)
    {
        if (is_object($variate)) return $variate;
        if (is_array($variate)) return (object) $variate;
        return json_decode(self::convertArray($variate));
    }

    public static function convertString($variate, $delimiter = ',')
    {
        if (!is_array($variate)) $variate = self::convertArray($variate);
        return implode($delimiter, $variate);
    }

    public static function convertXml($variate, $rootElement = 'xml', $xml = null)
    {
        $array = self::convertArray($variate);
        $xmlObj = $xml;
        if ($xml === null) {
            $xmlObj = new SimpleXMLElement("<{$rootElement}/>");
        }
        array_walk($array, function ($value, $key) use (&$xmlObj) {
            if (is_array($value)) {
                self::convertXml($value, $key, $xmlObj->addChild($key));
            } else {
                $xmlObj->addChild($key, $value);
            }
        });
        return $xmlObj->asXML();
    }

    public static function convertJson($variate, $isFormat = false)
    {
        if (!is_object($variate) && !is_array($variate)) $variate = self::convertArray($variate);
        return json_encode($variate, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
