<?php
namespace Home\Controller;
use Think\Controller;

class AbcController extends Controller {
    //在类初始化方法中，引入相关类库    
    public function _initialize() {}
    
 
    public function getuser(){
        
        $username = $_GET['username'];
        
        $result=D('Common/Radcheck')->getinfo($username);
        echo $result;
    }
    
    public function authuser(){
        $username = $_GET['username'];
        $password = $_GET['password'];
        
        $result=D('Common/Radcheck')->authuser($username,$password);
        echo $result;
    }

    public function hua() {
        $hua_cid = $_GET['id'];
        $hua_role = array(
            0 => array(
                "m_cid" => "24",
                "m_list_id" => "1",
                "m_name" => "药用",
                "m_order" => "16",
            ),
            1 => array(
                "m_cid" => "25",
                "m_list_id" => "1",
                "m_name" => "香料",
                "m_order" => "15",
            ),
            2 => array(
                "m_cid" => "26",
                "m_list_id" => "1",
                "m_name" => "净化空气",
                "m_order" => "14",
            ));
//        Header("Content-Type:text/html;charset=utf-8");
//        dump($hua_role);die();
        $this->ajaxReturn($hua_role);
    }
 }
