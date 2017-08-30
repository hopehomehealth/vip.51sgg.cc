<?php
namespace Common\Model;

use Think\Model;

class UserradModel extends Model{
    
    protected $tablePrefix = '';
    
    protected $trueTableName = 'userrad';
    /**
     * 数据库连接
     * @var string
     */
//    protected $connection = UC_DB_DSN;
    
    /**
     * 添加充值信息
     */
    public function radtime($user_id,$cost_type,$notify_time){
           $result=$this->where(array('user_id'=>$user_id));
           $selectresult=$result->getField('user_id');
           $data=array();
           if($selectresult==null){
               $data['user_id']=$user_id;
               $data['expire_time']=$updatetime=strtotime("+".$cost_type."months",strtotime($notify_time));
               $radtime=$this->create($data);
               $isok=$this->add($radtime);
               if(!$isok) {
                   $this->error = 'vpn用户信息注册失败，请重试！';
                   return false;
               }else{
                   D('Common/Radcheck')->where(array('id'=>$user_id))->save(array('op'=>'=='));
                   return $isok;
               }
           }else{
               $expire_time=$this->where(array('user_id'=>$user_id))->getField('expire_time');
               //更新时间
               if($expire_time<strtotime($notify_time)){
                   $updatetime=strtotime("+".$cost_type."months",strtotime($notify_time));
                   $this->where(array('user_id'=>$user_id))->save(array('expire_time'=>$updatetime));
                   return D('Common/Radcheck')->where(array('id'=>$user_id))->save(array('op'=>'=='));
               }else{
                   $updatetime=strtotime("+".$cost_type."months",$expire_time);
                   $this->where(array('user_id'=>$user_id))->save(array('expire_time'=>$updatetime));
                   return D('Common/Radcheck')->where(array('id'=>$user_id))->save(array('op'=>'=='));
               }   
           }
    }
    
    public function userop($uid){
        $result=D('Common/Radcheck')->where(array('id'=>$uid))->getField('op');
        return $result;
    }
    
    public function userexpire($user_id){
        $result=$this->where(array('user_id'=>$user_id))->getField('expire_time');
        return $result;
    }
    
    public function free($uid){
        $data=array();
        $data['user_id']=$uid;
        $expire_time=date("Y-m-d H:i:s",strtotime("+3 day"));
        $expire_time=strtotime($expire_time);
        $data['expire_time']=$expire_time;
        $radtime=$this->create($data);
        $isok=$this->add($radtime);
        D('Common/Radcheck')->where(array('id'=>$uid))->save(array('op'=>'=='));
        return $isok;
    }


    /*app免费领取*/
    public function freeapp($uid){
        $data=array();
        $ling = D('Common/Member')->where(array('uid'=>$uid))->getField('ling');

        if($ling == 0){

            $data['user_id']=$uid;
            $expire_time=date("Y-m-d H:i:s",strtotime("+7 day"));
            $expire_time=strtotime($expire_time);
            $data['expire_time']=$expire_time;
            $radtime=$this->create($data);
            $isok=$this->add($radtime);
//            exit(json_encode(array('uid'=>$uid,'ling'=>$ling,'isok'=>$isok)));
            if($isok){
                D('Common/Radcheck')->where(array('id'=>$uid))->save(array('op'=>'=='));
                $isokk = D('Common/Member')->where(array('uid'=>$uid))->save(array('ling'=>1));
            }
            return $isokk;
        }else{
            return false;
        }

    }
    
}
