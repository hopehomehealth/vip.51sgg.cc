<?php
namespace Home\Controller;
use Think\Controller;

class WeiController extends Controller {
 //初始化
    public function _initialize()
    {
        //引入WxPayPubHelper
        vendor('WxPayPubHelper.WxPayPubHelper');
    }
    //生成二维码
    public function doweipay($data){
        $uid=get_uid();
        $time=date('YmdHis');
        $out_trade_no = $uid.'_'.$data.'_'.$time;
        switch ($data)
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
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        //设置统一支付接口参数
        //设置必填参数
        $unifiedOrder->setParameter("body","欢迎购买外贸浏览器产品");//商品描述
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号 
        $unifiedOrder->setParameter("total_fee",$total_fee);//总金额
        $unifiedOrder->setParameter("notify_url", 'http://vip.51sgg.cc/home/wei/notify');//通知地址 
        $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
        $unifiedOrder->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);
//         echo($_SERVER['REMOTE_ADDR']);
//         return;
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号 
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据 
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
        
        //获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();
	$otn = explode("_",$data);
        //商户根据实际情况设置相应的处理流程
        if ($unifiedOrderResult["return_code"] == "FAIL") 
        {
            //商户自行增加处理流程
            echo "通信出错：".$unifiedOrderResult['return_msg']."<br>";
        }
        elseif($unifiedOrderResult["result_code"] == "FAIL")
        {
            //商户自行增加处理流程
            echo "错误代码：".$unifiedOrderResult['err_code']."<br>";
            echo "错误代码描述：".$unifiedOrderResult['err_code_des']."<br>";
        }
        elseif($unifiedOrderResult["code_url"] != NULL)
        {
            //从统一支付接口获取到code_url
            $code_url = $unifiedOrderResult["code_url"];
            //用户充值意向入库
            $data = array(
                'user_id'=>get_uid(),
                'cost_type'=>$otn[1],
                'out_trade_no'=>$out_trade_no,
                'total_fee'=>$total_fee,
                'trade_status'=>'TRADE_START',
            );
            $result=D('Common/Recordrad')->record($data);
        }
        $arr['url'] = $code_url;
        $arr['out_trade_no'] = $out_trade_no;
        $this->ajaxReturn ($arr,'JSON');

    }
    
    public function verify($out_trade_no){
        $trade_status=D('Common/Recordrad')->where(array('out_trade_no' => $out_trade_no))->getField('trade_status');
        $arr['trade_status']=$trade_status;
        $this->ajaxReturn($arr,'JSON');
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
    		              $this->success("支付成功！");
    		              break;
    		          case REFUND:
    		              $this->error("超时关闭订单：".$i);
    		              break;
    		          case NOTPAY:
    		              $this->error("超时关闭订单：".$i);
//     		              $this->ajaxReturn($orderQueryResult["trade_state"], "支付成功", 1);
    		              break;
    		          case CLOSED:
    		              $this->error("超时关闭订单：".$i);
    		              break;
    		          case PAYERROR:
    		              $this->error("支付失败".$orderQueryResult["trade_state"]);
    		              break;
    		          default:
    		              $this->error("未知失败".$orderQueryResult["trade_state"]);
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
}
    
       

