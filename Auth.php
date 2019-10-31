<?php
/**
 * Author:  Yejia
 * Email:   ye91@foxmail.com
 */

namespace cium1\apiauth;


use cium1\apiauth\Exceptions\AccessKeyException;
use cium1\apiauth\Exceptions\InvalidTokenException;
use cium1\apiauth\Exceptions\SignatureMethodException;
use cium1\apiauth\Signatures\SignatureInterface;


/*
const access_key = '{access_key}';  // 服务端生成的 access_key
const secret_key = '{secret_key}';  // 服务端生成的 secret_key

const timestamp = Date.parse(new Date()) / 1000;    // 取时间戳
const rand_str = 'asldjaksdjlkjgqpojg64131321';      // 随机字符串自行生成

const header = Base64.encode(JSON.stringify({
                   "alg": "md5",
               }));
const payload = Base64.encode(JSON.stringify({
                 "timestamp": timestamp,
                 "rand_str": rand_str,
                 "access_key": access_key
             }));
const signature_string = header  + '.' + payload;

function md5Sign(string, secret){
    return md5(string + secret);
}

const api_token = signature_string + '.' + md5Sign(signature_string,secret_key);

const requestConfig = {
    headers: {
        "api-token": api_token
    }
};

$.post('/api/example',{},requestConfig).then(res=>{

});
*/

/**
 * Class Auth
 *
 * @package cium1\apiauth
 */
class Auth
{
    //状态开
    const STATUS_ON = 'on';

    //状态关
    const STATUS_OFF = 'off';

    /**
     * 状态
     *
     * @var string
     */
    private $status = self::STATUS_ON;

    /**
     * 规则
     *
     * @var array
     */
    private $roles = [];

    /**
     * 签名方法
     *
     * @var array
     */
    private $signatureMethods = [];

    /**
     * 忽略
     *
     * @var array
     */
    private $skip = [];

    /**
     * 超时时间(秒)
     *
     * @var int
     */
    private $timeout = 60;

    /**
     * Auth constructor.
     *
     * @param array $config 配置
     */
    public function __construct(array $config = [])
    {
        if (isset($config['status'])) {
            $this->status = strval($config['status']);
        }
        if (isset($config['roles']) && is_array($config['roles'])) {
            $this->roles = $config['roles'];
        }
        if (isset($config['signature_methods']) && is_array($config['signature_methods'])) {
            $this->signatureMethods = $config['signature_methods'];
        }
        if (isset($config['skip']) && is_array($config['skip'])) {
            $this->skip = $config['skip'];
        }
        if (isset($config['timeout'])) {
            $this->timeout = intval($config['timeout']);
        }
    }

    /**
     * 设置状态
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * 获取状态
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 设置超时
     *
     * @param int $time
     *
     * @return $this
     */
    public function setTimeout(int $time)
    {
        $this->timeout = $time;
        return $this;
    }

    /**
     * 获取超时
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * 设置规则
     *
     * @param array $roles
     *
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * 获取规则
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * 设置签名方法
     *
     * @param array $methods
     *
     * @return $this
     */
    public function setSignatureMethods(array $methods)
    {
        $this->signatureMethods = $methods;
        return $this;
    }

    /**
     * 获取签名方法
     *
     * @return array
     */
    public function getSignatureMethods()
    {
        return $this->signatureMethods;
    }

    /**
     * 设置忽略
     *
     * @param array $skip
     *
     * @return $this
     */
    public function setSkip(array $skip)
    {
        $this->skip = $skip;
        return $this;
    }

    /**
     * 获取忽略
     *
     * @return array
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * 校验
     *
     * @param string $url
     * @param string $token
     *
     * @return bool
     * @throws AccessKeyException
     * @throws InvalidTokenException
     * @throws SignatureMethodException
     */
    public function check(string $url, string $token)
    {
        if ($this->status == self::STATUS_ON && $this->isSkip($url)) {
            if (mb_substr_count($token, '.') != 2) {
                throw new InvalidTokenException("Token format error");
            }
            list($headerStr, $payloadStr, $signature) = explode(".", $token);
            list($header, $payload, $alg) = $this->parseParams($headerStr, $payloadStr);
            $role = $this->roles[$payload['access_key']];
            $this->signatureCheck($alg, "$headerStr.$payloadStr", $role['secret_key'], $signature);
            //$this->bindParamsToRequest($request, $role['name'], $payload);
        }
        return true;
    }

    /**
     * 检查忽略
     *
     * @param string $url
     *
     * @return mixed
     */
    private function isSkip(string $url)
    {
        $handler = [self::class, 'defaultSkipHandler'];
        $urls = [];
        if (!empty($this->skip)) {
            if (isset($this->skip['is']) && is_callable($this->skip['is'])) {
                $handler = $this->skip['is'];
            }
            if (isset($this->skip['urls']) && is_array($this->skip['urls'])) {
                $urls = $this->skip['urls'];
            }
        }

        return call_user_func_array($handler, [$url, $urls]);
    }

    /**
     * 默认忽略
     *
     * @param string $url
     * @param array  $urls
     *
     * @return bool
     */
    private function defaultSkipHandler(string $url, array $urls)
    {
        return in_array($url, $urls);
    }

    /**
     * 解析参数
     *
     * @param string $headerStr
     * @param string $payloadStr
     *
     * @return array
     * @throws AccessKeyException
     * @throws InvalidTokenException
     * @throws SignatureMethodException
     */
    private function parseParams(string $headerStr, string $payloadStr)
    {
        $header = json_decode(base64_decode($headerStr), true);
        $payload = json_decode(base64_decode($payloadStr), true);

        if (!is_array($header) || !isset($header['alg']) ||
            !is_array($payload) || !isset($payload['timestamp']) || !isset($payload['rand_str']) || !isset($payload['access_key'])) {
            throw new InvalidTokenException('Invalid token !');
        }

        if ($this->timeout > 0 && $this->timeout < (time() - $payload['timestamp'])) {
            throw new InvalidTokenException('Token has expired!');
        }

        if (!isset($this->roles[$payload['access_key']])) {
            throw new AccessKeyException('Access key invalid !');
        }

        if (!isset($this->signatureMethods[$header['alg']])) {
            throw new SignatureMethodException($header['alg'] . ' signatures are not supported !');
        }

        $alg = $this->signatureMethods[$header['alg']];

        if (!class_exists($alg)) {
            throw new SignatureMethodException($header['alg'] . ' signatures method configuration error !');
        }

        $alg = new $alg;

        if (!$alg instanceof SignatureInterface) {
            throw new SignatureMethodException($header['alg'] . ' signatures method configuration error !');
        }

        return [$header, $payload, $alg];
    }

    /**
     * 签名校验
     *
     * @param SignatureInterface $alg
     * @param string             $signatureStr
     * @param string             $secret
     * @param string             $signature
     *
     * @throws InvalidTokenException
     */
    private function signatureCheck(SignatureInterface $alg, string $signatureStr, string $secret, string $signature)
    {
        if (!$alg::check($signatureStr, $secret, $signature)) {
            throw new InvalidTokenException('invalid token !');
        }
    }
}