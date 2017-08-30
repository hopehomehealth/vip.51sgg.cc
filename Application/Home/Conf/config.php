<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.thinkphp.cn>
// +----------------------------------------------------------------------

/**
 * 前台配置文件
 * 所有除开系统级别的前台配置
 */
return array(

    // 预先加载的标签库
    'TAGLIB_PRE_LOAD'     =>    'OT\\TagLib\\Article,OT\\TagLib\\Think',
        
    /* 主题设置 */
    'DEFAULT_THEME' =>  'default',  // 默认模板主题名称

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX' => 'onethink_', // 缓存前缀
    'DATA_CACHE_TYPE'   => 'File', // 数据缓存类型

    /* 文件上传相关配置 */
    'DOWNLOAD_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Download/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //下载模型上传配置（文件上传类配置）

    /* 编辑器图片上传相关配置 */
    'EDITOR_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Editor/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

     /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）



    /**
     * 附件相关配置
     * 附件是规划在插件中的，所以附件的配置暂时写到这里
     * 后期会移动到数据库进行管理
     */
    'ATTACHMENT_DEFAULT' => array(
        'is_upload'     => true,
        'allow_type'    => '0,1,2', //允许的附件类型 (0-目录，1-外链，2-文件)
        'driver'        => 'Local', //上传驱动
        'driver_config' => null, //驱动配置
    ), //附件默认配置

    'ATTACHMENT_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Attachment/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //附件上传配置（文件上传类配置）
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__' => __ROOT__ . '/Application/Home'   . '/Static/images',
        '__CSS__' => __ROOT__ . '/Application/Home'   . '/Static/css',
        '__JS__' => __ROOT__ . '/Application/Home'  . '/Static/js',
        '__ZUI__' => __ROOT__ . '/Public/zui'
    ),

    //支付宝配置参数
    'alipay_config'=>array(
        'partner' =>'2088521193041541',   //这里是你在成功申请支付宝接口后获取到的PID；
        'seller_id' => '2088521193041541',
        'key'=>'5lwpejeyzv9bmt0f45xa94tljtltsctx',//这里是你在成功申请支付宝接口后获取到的Key
        'sign_type'=>strtoupper('MD5'),
        'input_charset'=> strtolower('utf-8'),
        'cacert'=> getcwd().'\\cacert.pem',
        'transport'=> 'http',
    ),
    //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；
    
    'alipay'   =>array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'liexi_sh@163.com',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://vip.51sgg.cc/home/pay/notifyurl.html',
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url'=>'http://vip.51sgg.cc/home/pay/returnurl.html',
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage'=>'http://vip.51sgg.cc',
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage'=>'http://vip.51sgg.cc',
    ),
    
    //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；


    //app支付宝配置参数
    'app_alipay_config'=>array(
        'app_id' =>'2016082000295159',   //这里是你在成功申请支付宝接口后获取到的PID；
        "method" => '',
        'seller_id' => '2088521193041541',
        'key'=>'5lwpejeyzv9bmt0f45xa94tljtltsctx',//这里是你在成功申请支付宝接口后获取到的Key
        'private_key' => 'MIIEpQIBAAKCAQEAvQLTf3EemhJps61lae+n13sqhIMoWgzcGWjeRVT9No9oM7z7Y/U4iiQ0/mBKkMkjrDr7f/KkGVZoZtygFzz9sEHLDiyp3P2YRw3Q2g49IaW1oakJGBDT/XZasP2lQqM/Y2daS1UBBffxNS3o4vjWM6O6ilZg4IzNtJv/Vhmub8eIjuOtYMT9eQT6FZzCoWXx0sWE2ToOrrkuQMYaCQGPzXy9e1r3ND38rhAatAkLS5vI5dQ+dmdIf424kLOusO4HeGh0i+aG0YjRqRacbzG7gwt/5TMl8lMybfWIk4IW2BIEMC52SFCh9IYo9EqpInKoA84E1N7yXaOYYiXmXfFk/QIDAQABAoIBAQCYPJiBQ2l0i3QbxoOyiddUVd3vEX0E2urEhJTSnxu8QFGbqpGI91Bs47DTWld0uq1C4dcEPTkzN0er4fQIA5YJy1RzvEGsv41RXa1klKdkXIYpCW9LtPIqapOtjv7252n89JfjqTnDxuq+/JVhiy9sNGnhVqV896wP1r4YaWL4oAboP6CCbi593FTTMhu66cwDYtJHrNTjnesajUSj0yPrUJ3Of0Jc2d/45fExp7aoO+i1DKdIdRKG4NHXGy/pyVRvmbiAl3Y4WkkZBO03GtN8kWONfRYWGFWt7J59u/Su2ytaVbjvceOyLhjtMtwcdn2ysJ51MYKGoYnqgfyuo+d5AoGBAPpidc1hkNT4i5M7gC9uL+ObWNad8RFBq/NB/3LtniuZ+2TOjq2EgXBqq/BGL1CgZoZS4GI7H0O8h9IcjRZKb0A4Cnw0DQ+A5plvdk0SS3oDFoluoBnHxPKA7JCqpmi+njB+VI3fTRrAMlu4P8beMSbL0ae+9N0VufRi1R3udU+HAoGBAMFAABKPcTjYan2AslxLsfF7EKmkK6pd27hyYg2TzKbujHJphE2pqdfbk9aqcucfPay4nnbCX/PJsJbZlWXe6LS4hjQK1dWiXAvN+IusqxN6e2rOD6jbSfnJiv+ZgQaw69QiNJojkh2V9EjyJwaL02yDnwxn3bA/8vMT1J+AGOBbAoGARAgiuAZNgwBxdDTTlI+c5XwdPFs32Bd+8B67mO+lbXVuUCqrq8v4G6JwS13Wl1Kt6Mt1+lrUGOSOBvIJB8h7x/gyKSM/dTnx7jjR7QkOhv11zyvGxpp4Eegj3v7vLkC03LpGoTHOUnlsdW1vrspKkp8IvUpuX+/6UcPU4kgbidsCgYEAo3ZKWWjCAa51rUEOHandk4BgYM5ALTSFJWiDbi6lvlIrtJ9yTOsFglP2om69EKjJV282gwf5d9ITsBXOHERIHI203xKHO9TA/S81XJgqgShqerZgYplS4pnLHFcw34MJh/+C9Rq/fo0X/BfwUMY3iSNwhmLZDzuYemo8wMnuwN8CgYEAnZhAfIWBhPd9c1ge7FzPa4DZUE+ckVyZ5GYO0+9JmXAK9cvQmkBRE8GdVTXh6KZ4wmZlFDkB22RQky4PabZtm/Tnr9O6jnO+FOdM0CFPObKfXopOH5/l5ceV5pnfk8ZPJ6M7yOKliYE465IffgBP/YCO8+F1UhAm9T9HclHbvhI=',
        'sign_type'=>strtoupper('RSA2'),
        "sign" => '',
        'charset'=> strtolower('utf-8'),
        'version' => '1.0',
        'notify_url' => "vip.51sgg.cc/index.php?s=Api/Mem/notifyurl",
        'biz_content' => '',
//        'gateway_url'=>'https://openapi.alipaydev.com/gateway.do',
        'gateway_url'=>'https://openapi.alipaydev.com/gateway.do',
        'public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAu8W76Pwmifce//GHYAbRdaicB49spJRLwpB6XJmedX3ywwFhZZyGTM3c7Hoa3n+G3iywfHHriEBMR+4RjLBnjOL+2WI8qT72w/XBUZ1lGThonJleZUi805B2pI3+pk3kwMRGlwpqIjKCcZt7/8tGNpZ+dsZO51GhWBjQYIRSoNRN8sMXAUaqxRVtyWCZLTRdzBpYI01AJuxiZM2Xpb6MU47ZN/nbcITKU5A1P1PvOfssnTXK8jce93HQg18xZQ2Mhs2zdzxYLrVI6WnRZ1BZcVwQMFbwN8Yf+2n1AdQNTXliZ/elv5/4Hrh4ID/3rpXVD3dS4FdSv1t5fvm6BAQLIQIDAQAB',
    ),
);

