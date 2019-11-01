<?php
/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium\ApiAuth;


class Tool
{
    /**
     * 随机字符串
     *
     * @param int    $length
     * @param string $char
     *
     * @return string
     */
    static function strRand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        if (!is_int($length) || $length < 0) return '';
        $string = '';
        for ($i = $length; $i > 0; $i--) $string .= $char[mt_rand(0, strlen($char) - 1)];
        return $string;
    }

    /**
     * 创建KEY
     *
     * @return array
     */
    static function createKey()
    {
        $access_key = self::strRand();
        $secret_key = self::strRand();
        return compact('access_key', 'secret_key');
    }
}