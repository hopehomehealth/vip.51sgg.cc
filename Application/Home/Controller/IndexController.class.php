<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;


/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController
{

    //系统首页
    public function index()
    {
        hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }
        //添加警告信息
        $message=$_REQUEST['message'];
        $this->assign('message',$message);
        $this->display();
    }

/*    public function test(){
        action_log('reg','member',1);
    }*/
    
    public function chongzhi(){
        if(is_login()>0){
            $this->display();
        }else{
            $this->redirect('Ucenter/Member/login');
        }
    }
    
    public function index2(){
        hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }
        //添加警告信息
        $message=$_REQUEST['message'];
        $this->assign('message',$message);
        $this->display();
    }

    public function download(){
        hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }
        //添加警告信息
        $message=$_REQUEST['message'];
        $this->assign('message',$message);
        $this->display();
    }
	public function abroad(){
        hook('homeIndex');
        $default_url = C('DEFUALT_HOME_URL');//获得配置，如果为空则显示聚合，否则跳转
        if ($default_url != '') {
            redirect(get_nav_url($default_url));
        }
        //添加警告信息
        $message=$_REQUEST['message'];
        $this->assign('message',$message);
        $this->display();
    }

}