<?php

namespace LinkV\IM\Util;

/**
 * Class Util
 *
 * @package LinkV\IM\Util
 */
final class Util
{
    /**
     * Hex2String is byte => string
     *
     * @param string $hex
     *
     * @return string
     */
    static function Hex2String($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex); $i++) {
            $string .= dechex(ord($hex[$i]));
        }
        return $string;
    }

    /**
     * String2Hex is string => byte
     *
     * @param string $string
     *
     * @return string
     */
    static function String2Hex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string) - 1; $i += 2) {
            $hex .= chr(hexdec($string[$i] . $string[$i + 1]));
        }
        return $hex;
    }

    /**
     * GetTimestamp 获取时间戳
     *
     * @return float
     */
    static function GetTimestamp()
    {
        $arr = explode(" ", microtime());
        return (float)$arr[1];
    }

    /**
     * genNonce 获取nonce
     *
     * @return string
     */
    static function genNonce()
    {
        $arr = explode(" ", microtime());
        $charID = strtoupper(md5(uniqid(mt_rand(), true)));
        $hyphen = (float)$arr[1];
        return substr($charID, 0, 8) . $hyphen
            . substr($charID, 8, 8);
    }

    /**
     * genSign 获取sign
     *
     * @param array $params
     * @param string $app_secret
     *
     * @return string
     */
    static function genSign($params, $app_secret)
    {
        ksort($params);
        $signString = '';
        $index = 0;
        foreach ($params as $key => $value) {
            $index++;
            if (!empty($value)) {
                $newValue = is_array($value) ? implode('|', $value) : $value;
                $signString .= $key . '=' . $newValue;
                if ($index !== count($params)) {
                    $signString .= '&';
                }
            }
        }
        $signString .= '&key=' . $app_secret;
        return strtolower(md5($signString));
    }
}