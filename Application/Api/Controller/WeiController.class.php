<?php
namespace Api\Controller;
use Think\Controller;

require_once APP_PATH . 'User/Conf/config.php';
require_once 'Conf/user.php';
class WeiController extends Controller
{
    //初始化
    public function _initialize()
    {
        //引入WxPayPubHelper
        vendor('WxPayPubHelper.WxPayPubHelper');
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





    private function getNonceStr() {
        $code = "";
        for ($i=0; $i > 10; $i++) {
            $code .= mt_rand(1000);        //获取随机数
        }
        $nonceStrTemp = md5($code);
        $nonce_str = mb_substr($nonceStrTemp, 5,37);      //MD5加密后截取32位字符
        return $nonce_str;
    }

    /*下单*/
    public function doweipay(){
        $token = I('post.token','','op_t');
        $uid = $this->getdectoken($token);
        $costpattern = I('post.costpattern','','op_t');
        $time=date('YmdHis');
        $out_trade_no = $uid.'_'.$costpattern.'_'.$time;
        switch ($costpattern)
        {
            case 'wx_1':
                $subject = '会员1个月';
                $total_fee = '1500';
                break;
            case 'wx_3':
                $subject = '会员3个月';
                $total_fee = '4000';
                break;
            case 'wx_6':
                $subject = '会员半年';
                $total_fee = '8000';
                break;
            case 'wx_12':
                $subject = '会员1年';
                $total_fee = '15000';
                break;
            default:
                $total_fee = '18000';
                header("location: http://vip.51sgg.cc");
        }

        //设置统一支付接口参数
        //设置必填参数
        $param = array(
            'appid' => '',
            'mch_id' => '',
            'nonce_str' => '',
            'device_info' => '',
            'body' => '欢迎购买外贸浏览器产品',
        );
        $nonce_str = $this->getNonceStr();
        $common_pub = new \Common_util_pub();
        $sign = $common_pub->getSign($param);
        $app_weipay_config = C('app_weipay_config');
        $params = array(
            'appid' => $app_weipay_config['appid'],
            'mch_id' => $app_weipay_config['mch_id'],
            'nonce_str' => $nonce_str,
//            'device_info' => '',
            'body' => $app_weipay_config['body'],
            'sign' => $sign,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'notify_url' => $app_weipay_config['notify_url'],
            'trade_type' => 'APP',
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
        );
        echo json_encode($params);




        $otn = explode("_",$costpattern);

            //用户充值意向入库
            $data = array(
                'user_id'=>$uid,
                'cost_type'=>$otn[1],
                'out_trade_no'=>$out_trade_no,
                'total_fee'=>$total_fee,
                'trade_status'=>'TRADE_START',
            );
            $result=D('Common/Recordrad')->record($data);


    }


    public function notify(){
        //使用通用通知接口
        $notify = new \Notify_pub();
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;

        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======

        //以log文件形式记录回调信息
        //         $log_ = new Log_();
        $log_name= __ROOT__."/Public/notify_url.log";//log文件路径

        $this->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
        if($notify->checkSign() == TRUE){
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                $this->log_result($log_name,"【通信出错】:\n".$xml."\n");
                $this->error("1");
            }
            elseif($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                $this->log_result($log_name,"【业务出错】:\n".$xml."\n");
                $this->error("失败2");
            }
            else{
                //此处应该更新一下订单状态，商户自行增删操作
                $this->log_result($log_name,"【支付成功】:\n".$xml."\n");
                $this->success("支付成功！");
                //待更新数据
                $out_trade_no=$notify->data["out_trade_no"];
                $otn=explode("_",$out_trade_no);
                $a = $notify->data["time_end"];
                $notify_time = substr($a, 0,4).'-'.substr($a, 4, 2).'-'.substr($a, 6, 2).' '.substr($a, 8, 2).':'.substr($a, 10, 2).':'.substr($a, 12, 2);
                $this->log_result($log_name,$notify_time."\n");
                $user_id        = $otn[0];  //用户id
                $this->log_result($log_name,$user_id."\n");
                $cost_type      = $otn[2];   //消费方式
                $this->log_result($log_name,$cost_type."\n");
                $parameter = array(
                    "trade_no"       => $notify->data["transaction_id"],          //支付宝交易号；
                    "trade_status"   => 'WX_SUCCESS',      //交易状态
                    "notify_time"    => $notify_time,       //通知的发送时间。付款时间
                );

                //更新订单数据
                $data_tradeno=$traderesult=D('Common/Recordrad')->where(array('out_trade_no' => $out_trade_no))->getField('trade_no');
                if($data_tradeno==null||$data_tradeno==""){
                    $traderesult=D('Common/Recordrad')->where(array('out_trade_no' => $out_trade_no))->save($parameter);
                    //更新记录
                    error_log('trad:'.$traderesult);
                    error_log('u:'.$user_id);
                    error_log('c:'.$cost_type);
                    error_log('n:'.$notify_time);
                    if($traderesult!=0){
                        D('Common/Userrad')->radtime($user_id,$cost_type,$notify_time);
                    }
                }
            }
            $this->ajaxReturn(array('msg'=>'支付成功','status'=>'0000'));

            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息
        }else{

        }
    }
    //查询订单
    public function orderQuery()
    {
//         out_trade_no='+$('out_trade_no').value,
        //退款的订单号
        if (!isset($_POST["out_trade_no"]))
        {
            $out_trade_no = " ";
        }else{
            $out_trade_no = $_POST["out_trade_no"];
            //使用订单查询接口
            $orderQuery = new \OrderQuery_pub();
            //设置必填参数
            //appid已填,商户无需重复填写
            //mch_id已填,商户无需重复填写
            //noncestr已填,商户无需重复填写
            //sign已填,商户无需重复填写
            $orderQuery->setParameter("out_trade_no","$out_trade_no");//商户订单号
            //非必填参数，商户可根据实际情况选填
            //$orderQuery->setParameter("sub_mch_id","XXXX");//子商户号
            //$orderQuery->setParameter("transaction_id","XXXX");//微信订单号

            //获取订单查询结果
            $orderQueryResult = $orderQuery->getResult();

            //商户根据实际情况设置相应的处理流程,此处仅作举例
            if ($orderQueryResult["return_code"] == "FAIL") {
                $this->error($out_trade_no);
            }
            elseif($orderQueryResult["result_code"] == "FAIL"){
//     			$this->ajaxReturn('','支付失败！',0);
                $this->error($out_trade_no);
            }
            else{
                $i=$_SESSION['i'];
                $i--;
                $_SESSION['i'] = $i;
                //判断交易状态
                switch ($orderQueryResult["trade_state"])
                {
                    case SUCCESS:
                        $arr = array('msg'=>'支付成功!','status'=>'1000');
                        $this->ajaxReturn($arr,'JSON');
                        break;
                    case REFUND:
                        $arr = array('msg'=>'超时关闭订单：'.$i,'status'=>'1000');
                        $this->ajaxReturn($arr,'JSON');
                        break;
                    case NOTPAY:
                        $arr = array('msg'=>'超时关闭订单：'.$i,'status'=>'1000');
                        $this->ajaxReturn($arr,'JSON');
//     		              $this->ajaxReturn($orderQueryResult["trade_state"], "支付成功", 1);
                        break;
                    case CLOSED:
                        $arr = array('msg'=>'超时关闭订单：'.$i,'status'=>'1000');
                        $this->ajaxReturn($arr,'JSON');
                        break;
                    case PAYERROR:
                        $arr = array('msg'=>"支付失败".$orderQueryResult["trade_state"],'status'=>'1000');
                        $this->ajaxReturn($arr,'JSON');
                        break;
                    default:
                        $arr = array('msg'=>"未知失败".$orderQueryResult["trade_state"],'status'=>'1000');
                        $this->ajaxReturn($arr,'JSON');
                        break;
                }
            }
        }
    }

    private function log_result($log_name,$cont){
        $fp = fopen('/usr/ocean.log',"a");
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






?>