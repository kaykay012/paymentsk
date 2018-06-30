fecshop 支付插件
================
fecshop支付-易极付 国际信用卡支付,支持 Visa, MasterCard, JCB卡种等

安装
-------

```
composer require --prefer-dist kaykay012/paymentsk 
```

or 在根目录的`composer.json`中添加

```
"kaykay012/paymentsk": "*"

```

然后执行

```
composer update
```

配置
-----

1.配置文件复制

将`vendor\kaykay012\paymentsk\config\fecshop_paymentsk.php` 复制到
`@common\config\fecshop_third_extensions\fecshop_paymentsk.php`(需要创建该文件)

该文件是扩展的配置文件，通过上面的操作，加入到fecshop的插件配置中.

2.支付配置

在文件 `common\fecshop_local_services\Payment.php` 添加如下代码

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