<?php
/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium1\ApiAuth\Signatures;


class Md5 implements SignatureInterface
{

    const NAME = "md5";

    public static function sign(string $string, string $secret): string
    {
        return md5($string . $secret);
    }

    public static function check(string $string, string $secret, string $signature): bool
    {
        return static::sign($string, $secret) === $signature;
    }

}