fecshop 支付插件
================
fecshop支付-易极付 国际信用卡支付,支持 Visa, MasterCard, JCB卡种等

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist kaykay012/paymentsk "*"
```

or add

```
"kaykay012/paymentsk": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
'paymentConfig' => [
    'yjpay_standard' => [
            'label'=> '易极付',
            // 跳转开始URL
            'start_url'             => '@homeUrl/paymentsk/yjpay/standard/start',
            // 支付完成后，跳转的地址。
            'return_url'            => '@homeUrl/paymentsk/yjpay/standard/review',
            // 支付平台发送消息，接收的地址。
            'ipn_url'               => '@homeUrl/paymentsk/yjpay/standard/ipn',
            // 取消支付后，返回fecshop的url
            'cancel_url'            => '@homeUrl/paymentsk/yjpay/standard/cancel',
            // 下面是沙盒地址， 正式环境请改为：https://openapiglobal.yiji.com/gateway.html
            'webscr_url'            => 'https://openapi.yijifu.net/gateway.html',
            'success_redirect_url'  => '@homeUrl/payment/success',
            // 账号信息
            'account'  => '1205248159561118',
            'signature'=> 'b31fa4aedd67a7f92d882f5e461524bf',
        ],
]
```