<?php
/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace Cium\ApiAuth\Signatures;

interface SignatureInterface
{
    /**
     * 签名加密
     *
     * @param string $string
     * @param string $secret
     *
     * @return string
     */
    public static function signEncrypt(string $string, string $secret): string;

    /**
     * 数据加密
     * @param string $string
     * @return string
     */
    public static function dataEncrypt(string $string): string;

    /**
     * 签名校验
     *
     * @param string $string
     * @param string $secret
     * @param string $signature
     *
     * @return bool
     */
    public static function signCheck(string $string, string $secret, string $signature): bool;

    /**
     * 数据校验
     * @param string $encryptStr
     * @param string $data
     * @return bool
     */
    public static function dataCheck(string $encryptStr, string $data): bool;

}