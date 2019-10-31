<?php
/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium1\ApiAuth\Signatures;

interface SignatureInterface
{
    /**
     * 签名
     *
     * @param string $string
     * @param string $secret
     *
     * @return string
     */
    public static function sign(string $string, string $secret): string;

    /**
     * 校验
     *
     * @param string $string
     * @param string $secret
     * @param string $signature
     *
     * @return bool
     */
    public static function check(string $string, string $secret, string $signature): bool;

}