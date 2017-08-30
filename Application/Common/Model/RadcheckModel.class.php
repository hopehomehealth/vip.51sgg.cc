<?php
namespace Common\Model;

use Think\Model;

class RadcheckModel extends Model{
    
    protected $tablePrefix = '';
    
    protected $trueTableName = 'radcheck';
    /**
     * 数据库连接
     * @var string
     */
//    protected $connection = UC_DB_DSN;
    
    /**
     * 注册vpn用户
     */
    public function register($data,$uid){
           if($radcheck=$this->create(array('id'=>$uid,'username'=>$data['username'],'op'=>'!=','value'=>$data['password']))){
               $uid = $this->add($radcheck);
               if (!$uid) {
                   $this->error = 'vpn用户信息注册失败，请重试！';
                   return false;
               }else{
                   $isok=D('Common/Userrad')->free($uid);
                   return $uid;
               }
           }else {
            return $this->getError(); //错误详情见自动验证注释
        }     
    }
    
    public function userop($uid){
        $result=D('Common/Radcheck')->where(array('id'=>$uid))->getField('op');
        return $result;
    }
    
    public function getinfo($username){
        $result=D('Common/Radcheck')->where(array('username'=>$username))->getField('op');
        return $result;
    }
    
    public function authuser($username,$value){
        $result=D('Common/Radcheck')->where(array('username'=>$username,'value'=>$value))->getField('id');
        return $result;
    }
}
