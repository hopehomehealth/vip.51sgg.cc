<?php
return array(
    //app支付宝配置参数
    'app_alipay_config'=>array(
        'seller_email'=>'liexi_sh@163.com',
//        'app_id' =>'2016082000295159',   //这里是你在成功申请支付宝接口后获取到的PID；
        'app_id' =>'2017051107199484',   //这里是你在成功申请支付宝接口后获取到的PID；
        "method" => '',
        'seller_id' => '2088521193041541',
        'key'=>'5lwpejeyzv9bmt0f45xa94tljtltsctx',//这里是你在成功申请支付宝接口后获取到的Key
        'private_key' => 'MIIEogIBAAKCAQEAunlKFWR3yWnlcEcWT1p5hW2u6/s8QxO5Y2JiP7iLZcAKcF/BFO0/DDSkLyfVfI+fHfCzltd6IhR/1Pr8EZL54TsCTBftMJM6AwfAQAs8NbbfHSxpQGuTmmtSCIgWgYxQlQvdzxy09JdI8b1j0iCSM9j4Mx58aOFNbxYKdtvOVVpgeL6gSb9h/PFvRcHG6nn4JBa8DKN+lztzbrnIh6QcgHdR7csTz4PD9jgU4mkuaE976xXpjBfmrxjAHygFmbsH/usi0IR9A/TC+6JvpHI4mIy3z/AKG1MiIcMey3kyJYBfQ376Ks9+o0kjKV+nblP9ZrhBjukXSqvMeu5gng9XWwIDAQABAoIBAA5GZLZ4h3cCxU6wEnQmr/DX8bEc/YMBCosiJ/VK0lkKt4HcwrIwa0gq63q0qPItKHgpSgY9HptnSFXslMlfoANu+gELP9wdGS7MNBpzAbv28OOR18jXs2f52UDP8jEepbiTPPqy7uNehXYEEpUuayLO9Ekth0acIMplPI3Wy9favp68BJSwcW1YFveVWPU7GeN/72Y2JjE2hpKvxkQA+hU6xd8yJ5yiG32PaVub3aH5iljcWl53lqMER8z7kld41yCInMztmaV3Pkw+sr20EQWCPtwU138aUKFjCMXdR8crSq6J/ddSeUTmr7g/Jz/Vo+rT2Uv0PkS7iL3ME7N6r5ECgYEA9r5c6JRob2YwtzinYgWQPXrIhIJZI/OXWTANLcV0tuf0STH3zDznkd/EtUva1NUP7eTETorBDashc9II6I+GwgPouYU1beJcZMM/NE0p6N9rCIjZZXto4LBW0cuhACB0F8+gEcXhbaV645Pw4YHqDdV2ATZP5nAxIGiiyVRY60MCgYEAwXgd4yWYxBBFhmtIMDSP96iMk5WkMO6dBVAomfQTok6H4F+RagbCw/A8oEnnH6sRnU/qD5wn5eTyNGhMtu7/biuZlfc/amoO9zlOaehcU+3UDfdZaENc0uJCgH2FJHmJtbrDYpY0mxnfXJH4KYchvvdf8UPapYBTTnjRGhduhgkCgYAL8EwIiN3Avh4PT3Nx37kJ8H3xQ2lSvv+MtjF9DHfIPdLpE7zcqfm+aihXaVMuRxzdMtt4vWf35FbbBsedkQxBKPVCvLpIFdLIoVXWjwE/HkhMgmqaaW06qe2ZexPQMMHNQSOKmJt+taoLhuX501Ji1vg9uMYG7VjDK6zstuMShwKBgBAKn5/H7ETFfJ5Kou+sTAui5BUQoU6VWluoa9VoEYCxtj56bho2eUu5za0Us59ClfFPQP8OWZiMRTDnPQUmB/PglZmDqLRwGtGQ5NmPNKiY3a5Sxg0JGNc7f3wb7EA7+5kf5Td1cOMzX/vS9pqq47dr136vu59hZobpKZ1aOAXRAoGAFHHGRczsOFjMMHT/c5F1xlqQh6Aa8D63Rs8AaP+CWgpy9FCiIJT9D1mawI5rrACl1pXcVHf2/IvvknLW0gt7RwR3IyRaw7593ZN9nWTj1CSJl/nwqs4kEmuaKqiKBfG9evJ9DPKlRmgS5N6e61p1ou0mq08yO99azXOjpmNLLgk=',
        'sign_type'=>strtoupper('RSA2'),
        "sign" => '',
        'charset'=> strtolower('utf-8'),
        'version' => '1.0',
        'notify_url' => "vip.51sgg.cc/index.php?s=Api/Pay/notifyurl",
        'biz_content' => '',
//        'gateway_url'=>'https://openapi.alipaydev.com/gateway.do',
        'gateway_url'=>'https://openapi.alipaydev.com/gateway.do',
        'public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAu8W76Pwmifce//GHYAbRdaicB49spJRLwpB6XJmedX3ywwFhZZyGTM3c7Hoa3n+G3iywfHHriEBMR+4RjLBnjOL+2WI8qT72w/XBUZ1lGThonJleZUi805B2pI3+pk3kwMRGlwpqIjKCcZt7/8tGNpZ+dsZO51GhWBjQYIRSoNRN8sMXAUaqxRVtyWCZLTRdzBpYI01AJuxiZM2Xpb6MU47ZN/nbcITKU5A1P1PvOfssnTXK8jce93HQg18xZQ2Mhs2zdzxYLrVI6WnRZ1BZcVwQMFbwN8Yf+2n1AdQNTXliZ/elv5/4Hrh4ID/3rpXVD3dS4FdSv1t5fvm6BAQLIQIDAQAB',
    ),

    'app_weipay_config'=>array(
        'appid' => '',
        'mch_id' => '',
        'nonce_str' => '',
        'device_info' => '',
        'body' => '欢迎购买外贸浏览器产品',

        'trade_type' => 'APP',
        'notify_url' => 'http://vip.51sgg.cc/index.php?s=/Api/Wei/notify',
    ),


);



?>