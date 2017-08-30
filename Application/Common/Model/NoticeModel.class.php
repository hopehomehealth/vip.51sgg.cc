<?php
namespace Common\Model;

use Think\Model;

class NoticeModel extends Model{
    
    protected $tablePrefix = '';
    
    protected $trueTableName = 'notice';
    /**
     * 数据库连接
     * @var string
     */
//    protected $connection = UC_DB_DSN;
    
    
    
    public function getNoticeList($index,$count){
        $result=D('Common/Notice')->where(array('status'=>0,'type'=>array('neq',9)))->order('create_time desc')->page($index+1,$count)->select();
        for($m=0 ;$m<count($result); $m++){
            $temp=$result[$m];
            $date=date("Y-m-d",$temp['create_time']/1000);
            $result[$m]['create_time']=$date;
        }
        return $result;
    }
    
    public function getTotal(){
        $result=D('Common/Notice')->query('select count(*) count from notice');
        return $result;
    }
    
    public function getMessageList(){
        $start=strtotime(date('Y-m-d'))*1000;
        $now=time()*1000;
        $result=D('Common/Notice')->query('select * from notice where type=9 and create_time>'.$start.' and  create_time<'.$now);
        return $result;
    }
}
