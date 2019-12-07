<?php
/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium\ApiAuth\Signatures;


class Md5 implements SignatureInterface
{
    const NAME = "md5";

    public static function signEncrypt(string $string, string $secret): string
    {
        return md5($string . $secret);
    }

    public static function signCheck(string $string, string $secret, string $signature): bool
    {
        return static::signEncrypt($string, $secret) === $signature;
    }

    public static function dataCheck(string $encryptStr, string $data): bool
    {
        return static::dataEncrypt($data) === $encryptStr;
    }

    public static function dataEncrypt(string $string): string
    {
        return md5($string);
    }
}