<?php
namespace Home\Controller;
use Think\Controller;
use Common\Model\NoticeModel;

class SpendController extends Controller {
      
    public function getusertype(){
        $user_id=get_uid();
        $expire_time=D('Common/Userrad')->userexpire($user_id);
        $result=D('Common/Recordrad')->usertype($user_id);
        $username=D('Common/Member')->where(array('uid'=>$user_id))->getField('nickname');
        if(!empty($expire_time)){
            $expire_time = date("Y-m-d H:i:s",$expire_time);
        }
        $now = date("Y-m-d H:i:s");
        $type= $result[0]['cost_type'];
        $arr= array();
        if(empty($type)){
            $arr['type']='免费用户';
        }else if($type==1){
            $arr['type']='月度用户';
        }else if($type==3){
            $arr['type']='季度用户';
        }else if($type==6){
            $arr['type']='半年用户';
        }else if($type==12){
            $arr['type']='年度用户';
        }else{
            $arr['type']='其他用户';
        }
        if(empty($expire_time)){
            $arr['expire_time']=0;
        }else{
            $arr['expire_time']=$expire_time;
        }    
        $this->ajaxReturn ($arr,'JSON');
    }
    
    public function changepassword($data){
        $old_password=$data['old_password'];
        $new_password=$data['new_password'];
        $result=D('User/UcenterMember')->changePassword($old_password, $new_password);
        $arr['data']=$result;
        $this->ajaxReturn($arr,'JSON');
    }
    
    public function getpaylist($data){
        $index=$data['index'];
        $count=$data['count'];
        $user_id=get_uid();
        $result=D('Common/Recordrad')->paylist($user_id,$index,$count);
        $total=D('Common/Recordrad')->getTotal($user_id);
        $arr['data'] = $result;
        $arr['total'] = $total[0]['count'];
        $this->ajaxReturn($arr,'JSON');
    }
    
    public function getdata(){
        $user_id=get_uid();
        $qq = D('Common/Member')->where(array('uid'=>$user_id))->getField('qq');
        $mobile = D('User/UcenterMember')->where(array('id'=>$user_id))->getField('mobile');
        $arr['qq']=$qq;
        $arr['mobile']=$mobile;
        $this->ajaxReturn($arr,'JSON');
    }
    
    public function setdata($data){
        $user_id=get_uid();
        $mobile=$data['mobile'];
        $qq=$data['qq'];
        $result1 = D('Common/Member')->where(array('uid'=>$user_id))->save(array('qq'=>$qq));
        $result2 = D('User/UcenterMember')->where(array('id'=>$user_id))->save(array('mobile'=>$mobile));
        $result=false;
        if($result==0&&$result2==0){
            $result=ture;
        }
        $arr[data]=$result;
        $this->ajaxReturn($arr,'JSON');
    }
    
    public function ocean($data){
        $username=$data['username'];
        $password=$data['password'];
        $resultID=D('Common/Radcheck')->where(array('username'=>$username,'value'=>$password))->select();
        $arr=array();
        $arr['result']=false;
        if($resultID!=null){
            $arr['result']=true;
            $user_id=$resultID[0]['id'];
            $expire_time=D('Common/Userrad')->userexpire($user_id);
            $result=D('Common/Recordrad')->usertype($user_id);
            if(!empty($expire_time)){
                $expire_time = date("Y-m-d H:i:s",$expire_time);
            }
            $now = date("Y-m-d H:i:s");
            $type= $result[0]['cost_type'];
            if(empty($type)){
                $arr['type']='免费用户';
            }else if($type==1){
                $arr['type']='月度用户';
            }else if($type==3){
                $arr['type']='季度用户';
            }else if($type==6){
                $arr['type']='半年用户';
            }else if($type==12){
                $arr['type']='年度用户';
            }else{
                $arr['type']='其他用户';
            }
            if(empty($expire_time)){
                $arr['expire_time']=0;
            }else{
                $arr['expire_time']=$expire_time;
            }
        }
        $this->ajaxReturn($arr,'JSON');
    }
    
    public function serverlist($type){
        $result = D('Common/Server')->getServerList($type);
        $arr['list']=$result;
        $this->ajaxReturn($arr,'JSON');
    }
    
    public function arealist($type){
        $result = D('Common/Server')->getAreaList($type);
        $arr['list']=$result;
        $this->ajaxReturn($arr,'JSON');
    }
    

    private function jiami($txt, $key = null)
    {
        empty($key) && $key = $this->change();
    
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
        $nh = rand(0, 64);
        $ch = $chars[$nh];
        $mdKey = md5($key . $ch);
        $mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
        $txt = base64_encode($txt);
        $tmp = '';
        $i = 0;
        $j = 0;
        $k = 0;
        for ($i = 0; $i < strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = ($nh + strpos($chars, $txt [$i]) + ord($mdKey[$k++])) % 64;
            $tmp .= $chars[$j];
        }
        return $ch . $tmp;
    }
    
    private function change()
    {
        preg_match_all('/\w/', C('DATA_AUTH_KEY'), $sss);
        $str1 = '';
        foreach ($sss[0] as $v) {
            $str1 .= $v;
        }
        return $str1;
    }
 }
