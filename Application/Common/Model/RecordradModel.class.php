<?php
namespace Common\Model;

use Think\Model;

class RecordradModel extends Model{
    
    protected $tablePrefix = '';
    
    protected $trueTableName = 'recordrad';
    /**
     * 数据库连接
     * @var string
     */
//    protected $connection = UC_DB_DSN;
    
    /**
     * 添加充值信息
     */
    public function record($data,$uid){
           if($radcheck=$this->create($data)){
               $uid = $this->add($radcheck);
               if (!$uid) {
                   $this->error = 'vpn订单信息创建失败，请重试！';
                   return false;
               }else{
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
    
    public function usertype($user_id){
        $result=$this->where('user_id='.$user_id.' and trade_status in (\'TRADE_SUCCESS\' , \'WX_SUCCESS\')')->order('id desc')->limit(1)->select();
        return $result;
    }
    
    public function paylist($user_id,$index,$count){
        $result=$this->where('user_id='.$user_id.' and trade_status in (\'TRADE_SUCCESS\' , \'WX_SUCCESS\')')->order('id desc')->page($index+1,$count)->select();
        return $result;
    }
    
    public function getTotal($user_id){
        $result=$this->query('select count(*) count from recordrad where user_id='.$user_id.' and trade_status in (\'TRADE_SUCCESS\' , \'WX_SUCCESS\')');
        return $result;
    }
    
}
