<?php
namespace Api\Controller;
use Think\Controller;

//use User\Api\UserApi;

//use Alipayapp\AopClient;
//use Alipayapp\AlipayTradeAppPayRequest;
require_once APP_PATH . 'User/Conf/config.php';
require_once 'Conf/user.php';


class PayController extends Controller
{
    //在类初始化方法中，引入相关类库
    public function _initialize()
    {
        vendor('Alipayapp.aop.request.AlipayTradeAppPayRequest');
        vendor('Alipayapp.aop.AopClient');

    }


    public function getsign(){
        header("Content-type: text/html; charset=utf-8");
        $private_path =  "./rsaPrivateKey.pem";//私钥路径
        //构造业务请求参数的集合(订单信息)
        $content = array();
        $content['subject'] = "aaaa";
        $content['out_trade_no'] = "332";
        $content['timeout_express'] = "1d";
        $content['total_amount'] = "20.00";
        $content['product_code'] = "QUICK_MSECURITY_PAY";//销售产品码,固定值
       $con = json_encode($content);//$content是biz_content的值,将之转化成json字符串
        //公共参数
        $Client = new \AopClient();//实例化支付宝sdk里面的AopClient类,下单时需要的操作,都在这个类里面
        $param['app_id'] = C('app_alipay_config.app_id');
        $param['method'] = 'alipay.trade.app.pay';//接口名称，固定值
        $param['charset'] = 'utf-8';//请求使用的编码格式
        $param['sign_type'] = 'RSA2';//商户生成签名字符串所使用的签名算法类型
        $param['timestamp'] = date("Y-m-d Hi:i:s");//发送请求的时间
        $param['version'] = '1.0';//调用的接口版本，固定为：1.0
        $param['notify_url'] = C('app_alipay_config.notify_url');
        $param['biz_content'] = $con;//业务请求参数的集合,长度不限,json格式，即前面一步得到的

        $paramStr = $Client->getSignContent($param);//组装请求签名参数

        $sign = $Client->alonersaSign($paramStr, $private_path, 'RSA2', true);//生成签名
        exit(json_encode($paramStr));
        $param['sign'] = $sign;
        $str = $Client->getSignContentUrlencode($param);//最终请求参数
        echo $str;
    }

    public function alipay()
    {
        header("Content-type: text/html; charset=utf-8");
        $token = I('post.token','','op_t');
        $uid = $this->getdectoken($token);
        $time=date('YmdHis');
        $subject = '';//订单名称，必填
        $total_fee ='';//付款金额，必填
        $body = '欢迎购买51sgg产品';//商品描述，可空
        $costpattern = $_POST['costpattern'];

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $uid.'_'.$costpattern.'_'.$time;

        switch ($costpattern)
        {
            case 'zfb_1':
                $subject = '会员1个月';
                $total_fee = '15.00';
                break;
            case 'zfb_3':
                $subject = '会员3个月';
                $total_fee = '40.00';
                break;
            case 'zfb_6':
                $subject = '会员半年';
                $total_fee = '80.00';
                break;
            case 'zfb_12':
                $subject = '年度会员';
                $total_fee = '150.00';
                break;
            default:
                header("location: http://vip.51sgg.cc");
        }


        $timeout_express = "1m";
        $total_amount = $total_fee;
        $notify_url = C('app_alipay_config.notify_url');


        $app_alipay_config=C('app_alipay_config');
//        $aop = new \Vendor\alipayapp\aop\AopClient();
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = C('app_alipay_config.app_id');
//        $aop->appId = $app_alipay_config['app_id'];
        $aop->rsaPrivateKey = C('app_alipay_config.private_key');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = C('app_alipay_config.public_key');
//        $aop->rsaPrivateKeyFilePath = '/rsaPrivateKey.pem';
//实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
//        $request = new \Vendor\alipayapp\aop\request\AlipayTradeAppPayRequest();
        $request = new \AlipayTradeAppPayRequest();
//SDK已经封装掉了公共参数，这里只需要传入业务参数


        $bizcontent = "{\"body\":$body,"
            . "\"subject\": $subject,"
            . "\"out_trade_no\": $out_trade_no,"
            . "\"timeout_express\": $timeout_express,"
            . "\"total_amount\": $total_amount,"
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";
//        $bizcontent = array(
//            'body' => $body,
//            'subject' => $subject,
//            'out_trade_no' => $out_trade_no,
//            'timeout_express' => $timeout_express,
//            'total_amount' => $total_amount,
//            'product_code' => "QUICK_MSECURITY_PAY",
//        );
//        $bizcontent = json_encode($bizcontent);
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
//这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
//htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
//        echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
//        echo urldecode($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        $response = urldecode($response);
//        echo "<br>";
//        echo $response;
        $arr = explode('&',$response);
        $para = array();
        foreach($arr as $k => $v){
            $name = strstr($v,'=',true);
            $value = substr(strstr($v,'=',false),1);
            $para[$name] = $value;
        }
        echo json_encode($para);
//        var_dump($para);
//        $this->ajaxReturn($response,'JSON');//就是orderString 可以直接给客户端请求，无需再做处理。


        $exter_invoke_ip = get_client_ip(); //客户端的IP地址
        $payment_type = "1"; //支付类型 //必填，不能修改
        $seller_email = C('alipay.seller_email');//卖家支付宝帐户必填


        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($app_alipay_config['app_id']),
            "payment_type"    => $payment_type,
            "notify_url"    => $notify_url,
            "seller_email"    => $seller_email,
            "out_trade_no"    => $out_trade_no,
            "subject"    => $subject,
            "total_fee"    => $total_fee,
            "body"            => $body,
            "show_url"    => '',
            "exter_invoke_ip"    => $exter_invoke_ip,
            "_input_charset"    => trim(strtolower($app_alipay_config['charset']))
        );
//        //建立请求
//        $alipaySubmit = new  \AlipaySubmit($alipay_config);
//        $html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
        //充值意向
        $ct = explode("_",$costpattern);
        error_log(json_encode($ct));
        $data = array(
            'user_id'=>$uid,
            'cost_type'=>$ct[1],
//            'cost_type'=>explode("_",$costpattern)[1],
            'out_trade_no'=>$out_trade_no,
            'total_fee'=>$total_fee,
            'trade_status'=>'TRADE_START',
        );
//        $result=D('Common/Recordrad')->record($data);
    }



        function notifyurl(){
        $alipay_config=C('app_alipay_config');
        $aop = new \AopClient;
        //$public_path = "key/rsa_public_key.pem";//公钥路径
        $aop->alipayrsaPublicKey = C('app_alipay_config.public_key');//支付宝公钥
        //此处验签方式必须与下单时的签名方式一致
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        //验签通过后再实现业务逻辑，比如修改订单表中的支付状态。
        /**
         *  ①验签通过后核实如下参数out_trade_no、total_amount、seller_id
         *  ②修改订单表
         **/
        //打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。

        if($flag) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $notify_time    = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_id       = $_POST['buyer_id'];
            $out_trade_no   = $_POST['out_trade_no'];      //商户订单号
            $trade_no       = $_POST['trade_no'];          //支付宝交易号
            $trade_status   = $_POST['trade_status'];      //交易状态
            $total_fee      = $_POST['total_amount'];         //交易金额
            $notify_id      = $_POST['notify_id'];         //通知校验ID。

            $buyer_email    = $_POST['buyer_email'];       //买家支付宝帐号；
            $otn = explode("_",$out_trade_no);
            error_log(json_encode($otn));
            $user_id        = $otn[0];  //用户id
            $cost_type      = $otn[2];   //消费方式
//           $user_id        = explode("_",$out_trade_no)[0];  //用户id
//           $cost_type      = explode("_",$out_trade_no)[2];   //消费方式
            $parameter = array(
                "out_trade_no"     => $out_trade_no, //商户订单编号；
                "trade_no"     => $trade_no,     //支付宝交易号；
                "total_fee"     => $total_fee,    //交易金额；
                "trade_status"     => $trade_status, //交易状态
                "notify_id"     => $notify_id,    //通知校验ID。
                "notify_time"   => $notify_time,  //通知的发送时间。
                "buyer_email"   => $buyer_email,  //买家支付宝帐号；
                "buyer_id"       => $buyer_id,            //买家id
                //"user_id"        => $user_id,
                "cost_type"      => $cost_type,
            );
            // $this->log_result('',"获得了参数\n");
            /*
            $isfinish=D('Common/Recordrad')->where(array('trade_no'=>$trade_no));
            if($isfinish!=null){
                $this->log_result('',"notifyurl重复提交:".$out_trade_no."\n");
                //echo "该充值已经生效";
                $this->assign('message','该订单已经充值成功了,请勿重复提交');
                $this->redirect('Index/index');
            }
            */
            $data_tradeno=D('Common/Recordrad')->where(array('out_trade_no' => $out_trade_no))->getField('trade_no');
            $data_totalfee=D('Common/Recordrad')->where(array('out_trade_no' => $out_trade_no))->getField('total_fee');
            if($data_tradeno==null||$data_tradeno==""){
                if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                    if($data_totalfee == $total_fee){
                        $traderesult=D('Common/Recordrad')->where(array('out_trade_no' => $out_trade_no))->save($parameter);
                        //更新记录
                        if($traderesult!=0){
                            D('Common/Userrad')->radtime($user_id,$cost_type,$notify_time);
                        }
                        $this->log_result('',"notifyurl支付成功:".$out_trade_no."\n");
                        //echo "支付成功!";
                        $this->ajaxReturn(array('msg'=>'支付成功','status'=>'0000'));
                    }else{
                        $this->log_result('',"支付总额不正确，notifyurl支付失败:".$out_trade_no."\n");
                        //echo "支付成功!";
                        $this->ajaxReturn(array('msg'=>'支付总额不正确，支付失败！','status'=>'1000'));
                    }

                }else {
                    D('Common/Recordrad')->where(array('user_id'=>$user_id))->save(array('trade_status'=>$trade_status));
                    $this->log_result('',"notifyurl发生错误:".$out_trade_no."\n");
                    //echo "trade_status=".$_POST['trade_status'];
                    $this->ajaxReturn(array('msg'=>'充值发生了错误:'.$_POST['trade_status'],'status'=>'1000'));
                }
            }
            echo 'success';
        }else{
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            D('Common/Recordrad')->where(array('user_id'=>$user_id))->save(array('trade_status'=>'TRADE_FAIL'));
            //echo "支付失败！";
            $this->log_result('',"notifyurl发生了严重错误:".$out_trade_no."\n");
            $this->ajaxReturn(array('msg'=>'充值发生了严重错误！','status'=>'1000'));
        }

    }



    public function getdectoken(){
        $token = I('post.token','','op_t');
        $dectoken = (int)strstr(he_decrypt($token),'-',true);
        $ucauthkey = strstr(he_decrypt($token),'-',false);
        if( '-'.UC_AUTH_KEY == $ucauthkey){
            return $dectoken;
        }else{
            $arr = array('msg'=>'签名验证失败','status'=>'1000','dectoken'=>$dectoken,'ucauthkey'=>$ucauthkey);
            $this->ajaxReturn($arr,'JSON');
        }
    }






    function log_result($log_name,$cont){
        $fp = fopen('/usr/51sgg.log',"a");
        flock($fp, LOCK_EX);
        fwrite($fp,$cont);
        flock($fp, LOCK_UN);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed  $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data, $type = '')
    {
        if (empty($type)) $type = C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                foreach ($data as $key => $value) {
                    $data[$key]=urlencode($value);
                }
                exit(urldecode(json_encode($data)));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler . '(' . json_encode($data) . ');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return', $data);
        }
    }

}



//   public function doalipay(){
//       $private_path =  "/Alipay/key/rsa_private_key.pem";//私钥路径
//       $privatekey =  "";//私钥路径
//       //构造业务请求参数的集合(订单信息)
//       $content = array();
//       $content['subject'] = "会员3个月";
//       $content['out_trade_no'] = "$out_trade_no";
//       $content['timeout_express'] = "1d";
//       $content['total_amount'] = $total_amount;//订单总金额(必须定义成浮点型)
//       $content['product_code'] = "QUICK_MSECURITY_PAY";//销售产品码,固定值
//       $con = json_encode($content);//$content是biz_content的值,将之转化成json字符串
//
//
//       $Client = new \AopClient();//实例化支付宝sdk里面的AopClient类,下单时需要的操作,都在这个类里面
//       $uid=get_uid();
//       $time=date('YmdHis');
//       $subject = '';//订单名称，必填
//       $total_fee ='';//付款金额，必填
//
//       $body = '欢迎购买51sgg产品';//商品描述，可空
//       $costpattern = $_POST['costpattern'];//商户订单号，商户网站订单系统中唯一订单号，必填
//       $out_trade_no = $uid.'_'.$costpattern.'_'.$time;
//       switch ($costpattern)
//       {
//           case 'zfb_1':
//               $subject = '会员1个月';
//               $total_fee = '15.00';
//               break;
//           case 'zfb_3':
//               $subject = '会员3个月';
//               $total_fee = '40.00';
//               break;
//           case 'zfb_6':
//               $subject = '会员半年';
//               $total_fee = '80.00';
//               break;
//           case 'zfb_12':
//               $subject = '年度会员';
//               $total_fee = '150.00';
//               break;
//           default:
//               header("location: http://vip.51sgg.cc");
//       }
//
//
//
//       //这里我们通过TP的C函数把配置项参数读出，赋给$alipay_config；
//       $alipay_config=C('app_alipay_config');
//
//       /**************************请求参数**************************/
//       // $payment_type = "1"; //支付类型 //必填，不能修改
//       $notify_url = C('app_alipay.notify_url'); //服务器异步通知页面路径
//       $app_id = C("app_alipay_config.app_id");
//       $sign = yanzheng($out_trade_no,$total_fee);
//       /************************************************************/
//
//       $content = array(
//           "body"            => $body,
//           "subject"    => $subject,
//           "out_trade_no"    => $out_trade_no,
//           'timeout_express' => "1d",
//           "total_amount"    => $total_fee,//订单总金额(必须定义成浮点型)
//           'product_code'    => 'QUICK_MSECURITY_PAY',//销售产品码,固定值
//       );
//       $con = json_encode($content);//$content是biz_content的值,将之转化成json字符串
//       //构造要请求的参数数组，无需改动
//       $parameter = array(
//           "app_id"         => $app_id,
//           "method"         => 'alipay.trade.app.pay',
//           "charset"        => 'utf-8',
//           "sign_type"      => "RSA2",
//           "timestamp"     => date("Y-m-d H:i:s",$time),
//           "version"       => "1.0",
//           "notify_url"   => "https://api.xx.com/receive_notify.htm",
//           "body"            => $body,
//           "subject"    => $subject,
//           "out_trade_no"    => $out_trade_no,
//           'timeout_express' => "1d",
//           "total_amount"    => $total_fee,//订单总金额(必须定义成浮点型)
//           'product_code'    => 'QUICK_MSECURITY_PAY',//销售产品码,固定值
//           'biz_content'  => $con,
//       );
//       $paramStr = $Client->getSignContent($parameter);//组装请求签名参数
//       $sign = $Client->alonersaSign($paramStr, $private_path, 'RSA2', true);//生成签名
////        $sign = $Client->alonersaSign($paramStr, $privatekey, 'RSA2', true);//生成签名
//       $param['sign'] = $sign;
//       $str = $Client->getSignContentUrlencode($param);//最终请求参数
//       $parameter['sign'] = $str;
//
//       $html_text = $Client->buildRequestForm($parameter);
//       echo $html_text;
//
//   }
//
//

//
//

//    /*服务端生成签名，验证签名*/
//    public function yanzheng($out_trade_no,$total_amount){
//        $private_path =  "/Alipay/key/rsa_private_key.pem";//私钥路径
//        $privatekey =  "";//私钥路径
//        //构造业务请求参数的集合(订单信息)
//        $content = array();
//        $content['subject'] = "会员3个月";
//        $content['out_trade_no'] = "$out_trade_no";
//        $content['timeout_express'] = "1d";
//        $content['total_amount'] = $total_amount;//订单总金额(必须定义成浮点型)
//        $content['product_code'] = "QUICK_MSECURITY_PAY";//销售产品码,固定值
//        $con = json_encode($content);//$content是biz_content的值,将之转化成json字符串
//
//
//        //公共参数
//        $Client = new \AopClient();//实例化支付宝sdk里面的AopClient类,下单时需要的操作,都在这个类里面
//        $param['app_id'] = '$app_id';
//        $param['method'] = 'alipay.trade.app.pay';//接口名称，固定值
//        $param['charset'] = 'utf-8';//请求使用的编码格式
//        $param['sign_type'] = 'RSA2';//商户生成签名字符串所使用的签名算法类型
//        $param['timestamp'] = date("Y-m-d Hi:i:s");//发送请求的时间
//        $param['version'] = '1.0';//调用的接口版本，固定为：1.0
//        $param['notify_url'] = C("app_alipay_config.notify_url");//支付宝服务器异步回调地址
//        $param['biz_content'] = $con;//业务请求参数的集合,长度不限,json格式，即前面一步得到的
//
//        $paramStr = $Client->getSignContent($param);//组装请求签名参数
//        $sign = $Client->alonersaSign($paramStr, $private_path, 'RSA2', true);//生成签名
////        $sign = $Client->alonersaSign($paramStr, $privatekey, 'RSA2', true);//生成签名
//        $param['sign'] = $sign;
//        $str = $Client->getSignContentUrlencode($param);//最终请求参数
//        return $str;
//    }







?>