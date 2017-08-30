<?php
namespace Common\Model;

use Think\Model;

class ServerModel extends Model{
    
    protected $tablePrefix = '';
    
    protected $trueTableName = 'server';
    /**
     * 数据库连接
     * @var string
     */
    protected $connection = UC_DB_DSN;
      
    
    public function getServerList($type){
        $result=D('Common/Server')->where(array('type'=>intval($type)))->order('create_time desc')->select();
        for($m=0 ;$m<count($result); $m++){
            $temp=$result[$m];
            $date=date("Y-m-d H:i:s",$temp['create_time']);
            $result[$m]['create_time']=$date;
        }
        return $result;
    }
    
    public function getAreaList($type){
        $result=D('Common/Server')->query('select area from server where type='.$type.' group by area');
        return $result;
    }
    
}
