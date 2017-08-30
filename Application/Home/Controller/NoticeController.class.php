<?php
namespace Home\Controller;
use Think\Controller;
use Common\Model\NoticeModel;

class NoticeController extends Controller {
      
    public function getnotice($data){
        $index=$data['index'];
        $count=$data['count'];
        $result = D('Common/Notice')->getNoticeList($index,$count);
        $total = D('Common/Notice')->getTotal();
        $arr['data'] = $result;
        $arr['total'] = $total[0]['count'];
        $this->ajaxReturn ($arr,'JSON');
    }
    
    public function messagelist(){
        $result = D('Common/Notice')->getMessageList();
        $arr['data']=$result;
        $this->ajaxReturn($arr,'JSON');
    }
    	 
 }
