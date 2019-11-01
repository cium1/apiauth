# apiauth

#### 介绍
这是一个 API 鉴权包。 PS: web 前端 API 没有绝对的安全，该项目的本意是给不暴露源码的客户端提供一种鉴权方案(如 service、APP客户端)。

#### 软件架构
软件架构说明
 采用 token 的鉴权方式，只要客户端不被反编译从而泄露密钥，该鉴权方式理论上来说是安全的。

#### 安装教程

1.  composer require cium/apiauth

#### 使用说明

##### service
```

```

##### client
```
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
```
