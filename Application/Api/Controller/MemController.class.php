<?php
/**
 * created by heyanliang
 */
namespace Api\Controller;

use Common\Model\FollowModel;
use Think\Controller;
use User\Api\UserApi;


require_once APP_PATH . 'User/Conf/config.php';
require_once 'Conf/user.php';

class   MemController extends Controller
{
    /**
     * register  注册页面
     */



    public function register()
    {

        //获取参数
        $aUsername = $username = I('post.username', '', 'op_t');
        $aNickname = I('post.nickname', '', 'op_t');
        $aPassword = I('post.password', '', 'op_t');
        $bPassword = I('post.repassword', '', 'op_t');
        $aVerify = I('post.verify', '', 'op_t');
        $aRegVerify = I('post.reg_verify', 0, 'intval');
        $aRegType = I('post.reg_type', '', 'op_t');
//        $aStep = I('get.step', 'start', 'op_t');
        $aRole = I('post.role', 0, 'intval');
        $mobile = I('post.mobile');
        $email = I('post.email');
        $qq = I('post.qq');
        $iemail = I('post.email');


        $data = array();
        if (!modC('REG_SWITCH', '', 'USERCONFIG')) {
            $arr = array(
                'data' => $data ? $data : "",
                'msg' => '注册已关闭',
                'status' => '1000',
            );
            $this->ajaxReturn($arr,'JSON');
        }



        if (IS_POST) { //注册用户
            $return = check_action_limit('reg', 'ucenter_member', 1, 1, true);
            if ($return && !$return['state']) {
                $data = array(
                    'data' => array($return['info'], $return['url']),
                    'msg'  => '',
                    'status' => '1000',
                );
                exit(json_encode($data));
            }

            /* 检测验证码 */
            if (check_verify_open('reg')) {
                if (!check_verify($aVerify)) {
                    $data = array(
                        'data' => '',
                        'msg'  => '验证码输入错误',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($data,'JSON');
                }
            }
//            if (!$aRole) {
//                $data = array(
//                    'data' => '',
//                    'msg'  => '请选择角色。',
//                    'status' => '1000',
//                );
//                $this->ajaxReturn($data,'JSON');
//            }

            if (($aRegType == 'mobile' && modC('MOBILE_VERIFY_TYPE', 0, 'USERCONFIG') == 1) || (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 2 && $aRegType == 'email')) {
                if (!D('Verify')->checkVerify($aUsername, $aRegType, $aRegVerify, 0)) {
                    $str = $aRegType == 'mobile' ? '手机' : '邮箱';
                    $data = array(
                        'data' => '',
                        'msg'  => $str . '验证失败',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($data,'JSON');
                }
            }
            $aUnType = 0;
            //获取注册类型
            check_username($aUsername, $email, $mobile, $aUnType);
            if ($aRegType == 'email' && $aUnType != 2) {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '邮箱格式不正确',
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }
            if ($aPassword !== $bPassword) {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '两次输入密码不相同',
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }
            if ($aRegType == 'mobile' && $aUnType != 3) {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '手机格式不正确',
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }
            if ($aRegType == 'username' && $aUnType != 1) {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '用户名格式不正确',
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }
            if (!check_reg_type($aUnType)) {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '该类型未开放注册',
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }

            $aCode = I('post.code', '', 'op_t');
            if (!$this->checkInviteCode($aCode)) {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '非法邀请码！',
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }



            /* 注册用户 */
            if(empty($aUsername)&&!empty($mobile)){
                $aUsername=$mobile;
            }
            if(empty($aNickname)&&!empty($mobile)){
                $aNickname=$mobile;
            }
            $uid = UCenterMember()->register($aUsername, $aNickname, $aPassword, $email, $mobile, $aRegType);
            if (0 < $uid) { //注册成功
                $result = D('Common/Member')->where(array('uid'=>$uid))->save(array('qq'=>$qq));
                $result = D('User/UcenterMember')->where(array('id'=>$uid))->save(array('email'=>$iemail));
                $this->initInviteUser($uid, $aCode, $aRole);
                $this->initRoleUser($aRole, $uid); //初始化角色用户
                if (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 1 && $aUnType == 2) {
                    set_user_status($uid, 3);
                    $verify = D('Verify')->addVerify($email, 'email', $uid);
                    $res = $this->sendActivateEmail($email, $verify, $uid); //发送激活邮件
                    // $this->success('注册成功，请登录邮箱进行激活');
                }

//                $uid = UCenterMember()->login($username, $aPassword, $aUnType); //通过账号密码取到uid
                $data = array('id'=>$uid);
                $arr=array(
                    'data' => $data ? $data : "",
                    'msg' => '注册成功',
                    'status' => '0000',
                );
                exit(json_encode($arr));
//                $this->ajaxReturn($arr,'json');
//                D('Member')->login($uid, false, $aRole); //登陆
//                $this->success('',U('home/Index/index'));
            } else { //注册失败，显示错误信息
                $arr=array(
                    'data' => $data ? $data : "",
                    'msg' => $this->showRegError($uid),
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'json');
            }
        } else { //显示注册表单
            if (is_login()) {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '您已经登录',
                    'status' => '0000',
                );
                $this -> ajaxReturn($arr,'JSON');
            }else{
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => '必须是post提交表单',
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }

        }
    }
    public function login(){
        $data = '';
        if (IS_POST) {
            $result = $this->doLogin();
            if ($result['status']) {
                $token = he_encrypt($result['uid'].'-'.UC_AUTH_KEY);
                $arr = array(
                    'token' => $token,
                    'msg' => $result['info']."登陆成功",
                    'status' => '0000',
                );
                $this->ajaxReturn($arr,'JSON');
            } else {
                $arr = array(
                    'data' => $data ? $data : "",
                    'msg' => $result['info'],
                    'status' => '1000',
                );
                $this->ajaxReturn($arr,'JSON');
            }
        } else { //显示登录页面
            $arr = array(
                'data' => $data ? $data : "",
                'msg' => '必须post表单提交',
                'status' => '1000',
            );
            $this->ajaxReturn($arr,'JSON');
        }
    }

    /*登录页面*/
    public function doLogin(){
        $data = array();
        if(IS_POST){
            $aUsername = $username = I('post.username','','op_t');
            $aPassword = I('post.password','','op_t');
            $aVerify = I('post.verify', '', 'op_t');
//            $aRemember = I('post.remember',0,'intval');

            /* 检测验证码 */
            if (check_verify_open('login')) {
                if (!check_verify($aVerify)) {
                    $res['info']="验证码输入错误。";
                    return $res;
                }
            }

            /*调用UC登录接口登录*/
            check_username($aUsername,$email,$mobile,$aUnType);
            if(!check_reg_type($aUnType)){
                $res['info'] = "该类型未开放登录。";
            }


            $uid = UcenterMember()->login($username,$aPassword,$aUnType);
            if(0 < $uid){//UC登录成功
                /*登录用户*/
                $Member = D('Member');
                $args['uid'] = $uid;
                $args = array('uid'=>$uid,'nickname'=>$username);
                check_and_add($args);

                if ($Member->login($uid, $aRemember == 1)) { //登录用户
                    //TODO:跳转到登录前页面


                    if (UC_SYNC && $uid != 1) {
                        //同步登录到UC
                        $ref = M('ucenter_user_link')->where(array('uid' => $uid))->find();
                        $html = '';
                        $html = uc_user_synlogin($ref['uc_uid']);
                    }

                    $oc_config =  include_once './OcApi/oc_config.php';
                    if ($oc_config['SSO_SWITCH']) {
                        include_once  './OcApi/OCenter/OCenter.php';
                        $OCApi = new \OCApi();
                        $html = $OCApi->ocSynLogin($uid);
                    }

                    $res['status']=1;
                    $res['info']=$html;
                    $res['uid']=$uid;
                    //$this->success($html, get_nav_url(C('AFTER_LOGIN_JUMP_URL')));
                } else {
                    $res['info']=$Member->getError();
                }

            } else { //登录失败
                switch ($uid) {
                    case -1:
                        $res['info']= '用户不存在或被禁用！';
                        break; //系统级别禁用
                    case -2:
                        $res['info']= '密码错误！';
                        break;
                    default:
                        $res['info']= $uid;
                        break; // 0-接口参数错误（调试阶段使用）
                }
            }
            return $res;
        }


    }

    public function getdectoken($token){
        $dectoken = (int)strstr(he_decrypt($token),'-',true);
        $ucauthkey = strstr(he_decrypt($token),'-',false);
        if($ucauthkey == '-'.UC_AUTH_KEY){
            return $dectoken;
        }else{
            $arr = array('msg'=>'签名验证失败','status'=>'1000');
            $this->ajaxReturn($arr,'JSON');
        }

    }
    /*修改密码*/
    public function changepassword(){
        $token = I('post.token','','op_t');
        $old_password=I('post.old_password',"","op_t");
        $new_password=I('post.new_password',"","op_t");
        $dec_token = $this->getdectoken($token);
        $result=D('User/UcenterMember')->changePassword($old_password, $new_password, $dec_token);
        $data = array();
        if($result==true) {
            $msg='修改成功';
            $status="0000";
        }else{
            $msg='修改失败';
            $status="1000";
        }
        $arr=array(
            'data'=>$data,
            'msg'=>$msg,
            'status'=>$status,
        );
        $this->ajaxReturn($arr,'JSON');
    }

    public function checkAppVerify(){
        $data = array();
        $aRegType = I('post.reg_type',"","op_t");
        $aUsername = I('post.username',"","op_t");
        $uid = D('User/UcenterMember')->where(array('username'=>$aUsername))->getField('id');
        $aRegVerify = I('post.reg_verify',"","op_t");
        if (($aRegType == 'mobile' && modC('MOBILE_VERIFY_TYPE', 0, 'USERCONFIG') == 1) || (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 2 && $aRegType == 'email')) {
            if (!D('Common/Verify')->checkVerify($aUsername, $aRegType, $aRegVerify,0)) {
                $str = $aRegType == 'mobile' ? '手机' : '邮箱';
                $arr=array(
                'data' => $data ? $data : '',
                'msg'=> $str . '验证失败',
                'status'=>'1000',
                 );
               $this->ajaxReturn($arr,'JSON');
            }else{
                $str = $aRegType == 'mobile' ? '手机' : '邮箱';
                $data = array('uid'=>$uid,'username'=>$aUsername);
                $arr=array(
                    'data' => $data ? $data : '',
                    'msg'=> $str . '验证成功',
                    'status'=>'0000',
                );
                exit(json_encode($arr));
            }
        }

    }
    /*找回密码*/
    public function changepasswordapp(){
        $data = '';
        $new_password = I('post.new_password',"","op_t");
        $new_re_password = I('post.new_re_password',"","op_t");
        $username = I('post.username',"","op_t");
        if ($new_password !== $new_re_password) {
            $arr = array(
                'data' => $data ? $data : "",
                'msg' => '两次输入密码不相同',
                'status' => '1000',
            );
            $this->ajaxReturn($arr,'JSON');
        }
        $uid = D('User/UcenterMember')->where(array('username'=>$username))->getField('id');
        $result = D('Api/Mem')->changePassword($new_password,$uid);
        $data = array();
        if($result==true) {
            $msg='修改成功';
            $status="0000";
        }else{
            $msg='修改失败';
            $status="1000";
        }
        $arr=array(
            'data'=>$data,
            'msg'=>$msg,
            'status'=>$status,
        );
        $this->ajaxReturn($arr,'JSON');
    }


    public function getdata(){
        $token = I('post.token','','op_t');
        $user_id = $this->getdectoken($token);
//        $user_id = 268;
        if($user_id != 0){
        $username = D('Common/Member')->where(array('uid'=>$user_id))->getField('nickname');
        $mobile = D('User/UcenterMember')->where(array('id'=>$user_id))->getField('mobile');
        $expire = D('Common/Userrad')->userexpire($user_id);
        $result = D('Common/Recordrad')->usertype($user_id);
        $type = $result[0]['cost_type'];
        if(empty($type)){
            $type = '免费用户';
        }else if($type==1){
            $type = '月度用户';
        }else if($type==3){
            $type = '季度用户';
        }else if($type==6){
            $type = '半年用户';
        }else if($type==12){
            $type = '年度用户';
        }else{
            $type = '其他用户';
        }
        $currentvpn = D('Common/Radcheck')->userop(get_uid());
        if($currentvpn=='=='){
            $currentvpn='可用';
        }else{
            $currentvpn='不可用';
        }
        $message=null;
        if($expire==0||$expire==""){
            $message = '您是新注册用户';
        }else{
            $now = time();
            $exdate = date('Y-m-d H:i:s',$expire);
            if($now < $expire){
                $message = '到期时间为:<span style="color:#ff6a00">'.$exdate.'</span>';
            }else{
                $message = '已经于<span style="color:#ff6a00">'.$exdate.'</span>到期';
            }
        }
        $data['nickname'] = $username;
        $data['mobile'] = $mobile;
        $data['exdate'] = $exdate;
        $arr['data'] = $data;
        $arr['msg'] = $message;
        $arr['status'] = '0000';
        }else{
            $arr = array(
                'data' => '',
                'msg' => '无此用户',
                'status' => '1000',
            );
        }
//        $this->ajaxReturn($arr,'JSON');
        exit(json_encode($arr));
    }

    public function getpaylist(){
        $token = I('post.token','','op_t');
        $user_id = $this->getdectoken($token);
        $index = I('post.index',"",'intval');
        $count = I('post.count',"",'intval');
        $result = D('Common/Recordrad')->paylist($user_id,$index,$count);
        $total = D('Common/Recordrad')->getTotal($user_id);
        $arr['data'] = $result;
        $arr['total'] = $total[0]['count'];
        exit(json_encode($arr));

    }

    public function logout(){
        $data = array();
        $token = I('post.token','','op_t');
        $user_id = $this->getdectoken($token);
        if ($user_id) {
            D('Member')->logout();
            $msg = '退出登录成功';
            $status = '0000';
        } else {
            $msg = '未登录';
            $status = '1000';
        }
        $arr = array(
            'data'=>$data,
            'msg'=>$msg,
            'status'=>$status,
        );
        $this->ajaxReturn($arr,'JSON');
    }

    public function free($uid){
        $data=array();
        $ling = D('Common/Member')->where(array('uid'=>$uid))->getField('ling');

        if($ling == 0){

            $data['user_id']=$uid;
            $expire_time=date("Y-m-d H:i:s",strtotime("+7 day"));
            $expire_time=strtotime($expire_time);
            $data['expire_time']=$expire_time;
//            $radtime=D('Common/userrad')->create($data);
            $isok=D('Common/userrad')->data($data)->add();$this->ajaxReturn(array('uid'=>$uid,'ling'=>$ling,'isok'=>$isok));
            if($isok){
                D('Common/Radcheck')->where(array('id'=>$uid))->save(array('op'=>'=='));
                $isokk = D('Common/Member')->where(array('uid'=>$uid))->save(array('ling'=>1));
            }
            return $isokk;
        }else{
            return false;
        }

    }
    /*app接口免费领取*/
    public function lingqu(){
        $token = I('post.token','','op_t');
        $uid = $this->getdectoken($token);
//        $free=$this->free($uid);
        $free=D('Common/Userrad')->freeapp($uid);
        $data=array();
        if(!$free){
            $arr = array(
                'data'=>$data,
                'msg'=>'已经领取，不能再领哦',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr,'JSON');
        }else{
            $arr = array(
                'data'=>$data,
                'msg'=>'领取成功',
                'status'=>'0000',
            );
            $this->ajaxReturn($arr,'JSON');
        }

    }


    public function fenxiang(){
        $str = I('post.status','','op_t');
        $token = I('post.token','','op_t');
        $uid = $this->getdectoken($token);
        if(!$uid){
            $this->ajaxReturn(array('msg'=>'非法用户操作','status'=>'1000'));
        }
        if($str != 'success'){
            $this->ajaxReturn(array('msg'=>'分享失败','status'=>'1000'));
        }
        $time = time();
        $day_num =  5;
        $ex_time = D('Common/Userrad')->where(array('user_id'=>$uid))->getField('expire_time');

        if($time >= $ex_time){
            $expire_time = date("Y-m-d H:i:s",strtotime("+".$day_num." days"));
        }else{
            $expire_time = date("Y-m-d H:i:s",$ex_time + ($day_num * 3600 * 24));
        }
        $expire_time=strtotime($expire_time);
        D('Common/Userrad')->where(array('user_id'=>$uid))->save(array('expire_time'=>$expire_time));
    }



    public function getserver(){
        $token = I('post.token','','op_t');
        $uid = $this->getdectoken($token);
        $freetype = 0;
        $freedata = D('Common/server')->getServerList($freetype);
        $feetype = 1;
        $feedata = D('Common/server')->getServerList($feetype);
//        echo "<pre>";
//        var_dump($data);
        $arr = array(
            'freedata' => $freedata,
            'feedata' => $feedata,
            'msg' =>'',
            'status' => '0000',
        );
//        echo json_encode($arr);
        exit(json_encode($arr));
//        var_dump(json_decode(json_encode($arr)));
    }

    /**
     * checkAccount  ajax验证用户帐号是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkAccount()
    {
        $aAccount = I('post.account', '', 'op_t');
        $aType = I('post.type', '', 'op_t');
        if (empty($aAccount)) {
            $arr = array(
                'msg' => '不能为空！',
                'status' => '1000',
            );
            $this->ajaxReturn($arr,'JSON');
        }
//        check_username($aAccount, $email, $mobile, $aUnType);
        $mUcenter = UCenterMember();
        switch ($aType) {
            case 'username':
                $arr = array(
                    'msg' => '用户名格式不正确！',
                    'status' => '1000',
                );
                empty($aAccount) && $this->ajaxReturn($arr,'JSON');
                $length = mb_strlen($aAccount, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $arr = array(
                        'msg' => '用户名长度在4-32之间',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($arr,'JSON');
                }


                $id = $mUcenter->where(array('username' => $aAccount))->getField('id');
                if ($id) {
                    $arr = array(
                        'msg' => '该用户名已经存在!',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($arr,'JSON');
                }
                preg_match("/^[a-zA-Z0-9_]{4,32}$/", $aAccount, $result);
                if (!$result) {
                    $arr = array(
                        'msg' => '只允许字母和数字和下划线!',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($arr,'JSON');
                }
                break;
            case 'email':
                $arr = array(
                    'msg' => '邮箱格式不正确!',
                    'status' => '1000',
                );
                empty($email) && $this->ajaxReturn($arr,'JSON');
                $length = mb_strlen($email, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $arr = array(
                        'msg' => '邮箱长度在4-32之间!',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($arr,'JSON');
                }

                if(strrchr($email,'gmail.com')=='gmail.com'){
                    $arr = array(
                        'msg' => '不能注册gmail账号!',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($arr,'JSON');
                }

                $id = $mUcenter->where(array('email' => $email))->getField('id');
                if ($id) {
                    $arr = array(
                        'msg' => '该邮箱已经存在!',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($arr,'JSON');
                }
                break;
            case 'mobile':
                $arr = array(
                    'msg' => '手机格式不正确!',
                    'status' => '1000',
                );
                empty($mobile) && $this->ajaxReturn($arr,'JSON');
                $id = $mUcenter->where(array('mobile' => $mobile))->getField('id');
                if ($id) {
                    $arr = array(
                        'msg' => '该手机号已经存在!',
                        'status' => '1000',
                    );
                    $this->ajaxReturn($arr,'JSON');
                }
                break;
        }
        $arr = array(
            'msg' => '验证成功',
            'status' => '0000',
        );
        $this->ajaxReturn($arr,'JSON');
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    public function showRegError($code = 0)
    {
        switch ($code) {
            case -1:
                $error = '用户名长度必须在4-32个字符以内！';
                break;
            case -2:
                $error = '用户名被禁止注册！';
                break;
            case -3:
                $error = '用户名被占用！';
                break;
            case -4:
                $error = '密码长度必须在6-30个字符之间！';
                break;
            case -5:
                $error = '邮箱格式不正确！';
                break;
            case -6:
                $error = '邮箱长度必须在4-32个字符之间！';
                break;
            case -7:
                $error = '邮箱被禁止注册！';
                break;
            case -8:
                $error = '邮箱被占用！';
                break;
            case -9:
                $error = '手机格式不正确！';
                break;
            case -10:
                $error = '手机被禁止注册！';
                break;
            case -11:
                $error = '手机号被占用！';
                break;
            case -20:
                $error = '用户名只能由数字、字母和"_"组成！';
                break;
            case -30:
                $error = '昵称被占用！';
                break;
            case -31:
                $error = '昵称被禁止注册！';
                break;
            case -32:
                $error = '昵称只能由数字、字母、汉字和"_"组成！';
                break;
            case -33:
                $error = '昵称不能少于两个字！';
                break;
            default:
                $error = '未知错误24';
        }
        return $error;
    }


    /**
     * 判断注册类型
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function checkRegisterType()
    {
        $aCode = I('get.code', '', 'op_t');
        $register_type = modC('REGISTER_TYPE', 'normal', 'Invite');
        $register_type = explode(',', $register_type);

        if (!in_array('invite', $register_type) && !in_array('normal', $register_type)) {
            $this->error("网站已关闭注册！");
        }

        if (in_array('invite', $register_type) && $aCode != '') { //邀请注册开启且有邀请码
            $invite = D('Ucenter/Invite')->getByCode($aCode);
            if ($invite) {
                if ($invite['end_time'] <= time()) {
                    $this->error("该邀请码或邀请链接已过期！");
                } else { //获取注册角色
                    $map['id'] = $invite['invite_type'];
                    $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
                    if ($invite_type) {
                        if (count($invite_type['roles'])) {
                            //角色
                            $map_role['status'] = 1;
                            $map_role['id'] = array('in', $invite_type['roles']);
                            $roleList = D('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                            if (!count($roleList)) {
                                $this->error('邀请码绑定角色错误！');
                            }
                            //角色end
                        } else {
                            //角色
                            $map_role['status'] = 1;
                            $map_role['invite'] = 0;
                            $roleList = D('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                            //角色end
                        }
                        $this->assign('code', $aCode);
                        $this->assign('invite_user', $invite['user']);
                    } else {
                        $this->error("该邀请码或邀请链接已被禁用！");
                    }
                }
            } else {
                $this->error("不存在该邀请码或邀请链接！");
            }
        } else { //（开启邀请注册且无邀请码）或（只开启了普通注册）
            if (in_array('invite', $register_type)) {
                $this->assign('open_invite_register', 1);
            }

            if (in_array('normal', $register_type)) {
                //角色
                $map_role['status'] = 1;
                $map_role['invite'] = 0;
                $roleList = D('Admin/Role')->selectByMap($map_role, 'sort asc', 'id,title');
                //角色end
            } else { //（只开启了邀请注册）
                $this->error("收到邀请的用户才能注册该网站！");
            }
        }
        $this->assign('role_list', $roleList);
        return true;
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed  $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data, $type = '')
    {
        if (empty($type)) $type = C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                foreach ($data as $key => $value) {
                    $data[$key]=urlencode($value);
                }
                exit(urldecode(json_encode($data)));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler . '(' . json_encode($data) . ');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return', $data);
        }
    }

    /**
     * 判断邀请码是否可用
     * @param string $code
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function checkInviteCode($code = '')
    {
        if($code==''){
            return true;
        }
        $invite = D('Ucenter/Invite')->getByCode($code);
        if ($invite['end_time'] >= time()) {
            $map['id'] = $invite['invite_type'];
            $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
            if ($invite_type) {
                return true;
            }
        }
        return false;
    }

    private function initInviteUser($uid = 0, $code = '', $role = 0)
    {
        if ($code != '') {
            $inviteModel = D('Ucenter/Invite');
            $invite = $inviteModel->getByCode($code);
            $data['inviter_id'] = abs($invite['uid']);
            $data['uid'] = $uid;
            $data['invite_id'] = $invite['id'];
            $result = D('Ucenter/InviteLog')->addData($data, $role);
            if ($result) {
                D('Ucenter/InviteUserInfo')->addSuccessNum($invite['invite_type'], abs($invite['uid']));

                $invite_info['already_num'] = $invite['already_num'] + 1;
                if ($invite_info['already_num'] == $invite['can_num']) {
                    $invite_info['status'] = 0;
                }
                $inviteModel->where(array('id' => $invite['id']))->save($invite_info);

                $map['id'] = $invite['invite_type'];
                $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
                if ($invite_type['is_follow']) {
                    $followModel = D('Common/Follow');
                    $followModel->addFollow($uid, abs($invite['uid']));
                    $followModel->addFollow(abs($invite['uid']), $uid);
                }
                if($invite['uid']>0){
                    D('Ucenter/Score')->setUserScore(array($invite['uid']),$invite_type['income_score'],$invite_type['income_score_type'],'inc');//扣积分
                }
            }
        }
        return true;
    }

    /**
     * 初始化角色用户信息
     * @param $role_id
     * @param $uid
     * @return bool
     * @author 郑钟良<zzl@ourstu.com>
     */
    private function initRoleUser($role_id = 0, $uid)
    {
        $memberModel = D('Member');
        $role = D('Role')->where(array('id' => $role_id))->find();
        $user_role = array('uid' => $uid, 'role_id' => $role_id, 'step' => "start");
        if ($role['audit']) { //该角色需要审核
            $user_role['status'] = 2; //未审核
        } else {
            $user_role['status'] = 1;
        }
        $result = D('UserRole')->add($user_role);
        if (!$role['audit']) { //该角色不需要审核
            $memberModel->initUserRoleInfo($role_id, $uid);
        }
        $memberModel->initDefaultShowRole($role_id, $uid);

        return $result;
    }

}


?>