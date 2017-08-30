<?php
/**
 * 放置用户登陆注册
 */
namespace Ucenter\Controller;


use Common\Model\FollowModel;
use Think\Controller;
use User\Api\UserApi;

require_once APP_PATH . 'User/Conf/config.php';

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class MemberController extends Controller
{

    /**
     * register  注册页面
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function register()
    {

        //获取参数
        $aUsername = $username = I('post.username', '', 'op_t');
        $aNickname = I('post.nickname', '', 'op_t');
        $aPassword = I('post.password', '', 'op_t');
        $aVerify = I('post.verify', '', 'op_t');
        $aRegVerify = I('post.reg_verify', 0, 'intval');
        $aRegType = I('post.reg_type', '', 'op_t');
        $aStep = I('get.step', 'start', 'op_t');
        $aRole = I('post.role', 0, 'intval');
        $mobile = I('post.mobile');
        $qq = I('post.qq');
        $email = I('post.email');
        $iemail = I('post.email');

        if (!modC('REG_SWITCH', '', 'USERCONFIG')) {
            $this->error('注册已关闭');
        }
        
        

        if (IS_POST) { //注册用户
            $return = check_action_limit('reg', 'ucenter_member', 1, 1, true);
            if ($return && !$return['state']) {
                $this->error($return['info'], $return['url']);
            }
            /* 检测验证码 */
            if (check_verify_open('reg')) {
                if (!check_verify($aVerify)) {
                    $this->error('验证码输入错误。');
                }
            }
            if (!$aRole) {
                $this->error('请选择角色。');
            }

            if (($aRegType == 'mobile' && modC('MOBILE_VERIFY_TYPE', 0, 'USERCONFIG') == 1) || (modC('EMAIL_VERIFY_TYPE', 0, 'USERCONFIG') == 2 && $aRegType == 'email')) {
                if (!D('Verify')->checkVerify($aUsername, $aRegType, $aRegVerify, 0)) {
                    $str = $aRegType == 'mobile' ? '手机' : '邮箱';
                    $this->error($str . '验证失败');
                }
            }
            $aUnType = 0;
            //获取注册类型
            check_username($aUsername, $email, $mobile, $aUnType);
            if ($aRegType == 'email' && $aUnType != 2) {
                $this->error('邮箱格式不正确');
            }
            if ($aRegType == 'mobile' && $aUnType != 3) {
                $this->error('手机格式不正确');
            }
            if ($aRegType == 'username' && $aUnType != 1) {
                $this->error('用户名格式不正确');
            }
            if (!check_reg_type($aUnType)) {
                $this->error('该类型未开放注册。');
            }

            $aCode = I('post.code', '', 'op_t');
            if (!$this->checkInviteCode($aCode)) {
                $this->error('非法邀请码！');
            }

            /* 注册用户 */
            if(empty($aUsername)&&!empty($mobile)){
                //$aUsername=$email;
                $aUsername=$mobile;
            }
	        if(empty($aNickname)&&!empty($mobile)){
                //$aNickname=$email;
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

                $uid = UCenterMember()->login($username, $aPassword, $aUnType); //通过账号密码取到uid
                D('Member')->login($uid, false, $aRole); //登陆
                //$this->redirect(U('home/Index/index'));
                //$this->success('', U('Ucenter/member/step', array('step' => get_next_step('start'))));
		$this->success('',U('home/Index/index'));
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else { //显示注册表单
            if (is_login()) {
                redirect(U('home/Index/index'));
            }
            $this->checkRegisterType();
            $aType = I('get.type', '', 'op_t');
            $regSwitch = modC('REG_SWITCH', '', 'USERCONFIG');
            $regSwitch = explode(',', $regSwitch);
            $this->assign('regSwitch', $regSwitch);
            $this->assign('step', $aStep);
            $this->assign('type', $aType == '' ? 'username' : $aType);
            $this->display();
        }
    }


    public function step()
    {
        $aStep = I('get.step', '', 'op_t');
        $aUid = session('temp_login_uid');
        $aRoleId = session('temp_login_role_id');
        if (empty($aUid)) {
            $this->error('参数错误');
        }
        $userRoleModel = D('UserRole');
        $map['uid'] = $aUid;
        $map['role_id'] = $aRoleId;
        $step = $userRoleModel->where($map)->getField('step');
        if (get_next_step($step) != $aStep) {
            $aStep = check_step($step);
            $_GET['step'] = $aStep;
            $userRoleModel->where($map)->setField('step', $aStep);
        }
        $userRoleModel->where($map)->setField('step', $aStep);
        if ($aStep == 'finish') {
            D('Member')->login($aUid, false, $aRoleId);
        }
        $this->assign('step', $aStep);
        //$this->display('register');
        $this->redirect('Home/Index/index');
    }

    public function inCode()
    {
        if (IS_POST) {
            $aType = I('get.type', '', 'op_t');
            $aCode = I('post.code', '', 'op_t');
            $result['status'] = 0;
            if (!mb_strlen($aCode)) {
                $result['info'] = "请输入邀请码！";
                $this->ajaxReturn($result);
            }
            $invite = D('Ucenter/Invite')->getByCode($aCode);
            if ($invite) {
                if ($invite['end_time'] > time()) {
                    $result['status'] = 1;
                    $result['url'] = U('Ucenter/Member/register', array('code' => $aCode, 'type' => $aType));
                } else {
                    $result['info'] = "该邀请码已过期！请更换其他邀请码！";
                }
            } else {
                $result['info'] = "不存在该邀请码！请核对邀请码！";
            }
            $this->ajaxReturn($result);
        } else {
            $this->display();
        }
    }

    public function upRole()
    {
        $aRoleId = I('role_id', 0, 'intval');
        if (IS_POST) {
            $uid = is_login();
            $data['status'] = 0;
            if ($uid > 0 && $aRoleId != get_login_role()) {
                $aCode = I('post.code', '', 'op_t');
                $result['status'] = 0;
                if (!mb_strlen($aCode)) {
                    $result['info'] = "请输入邀请码！";
                    $this->ajaxReturn($result);
                }
                $invite = D('Ucenter/Invite')->getByCode($aCode);
                if ($invite) {
                    if ($invite['end_time'] > time()) {
                        $map['id'] = $invite['invite_type'];
                        $map['roles'] = array('like', '%[' . $aRoleId . ']%');
                        $invite_type = D('Ucenter/InviteType')->getSimpleData($map);
                        if ($invite_type) {
                            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
                            if ($roleUser) {
                                $data['info'] = "已持有该身份！";
                            } else {
                                $memberModel = D('Common/Member');
                                $memberModel->logout();
                                $this->initInviteUser($uid, $aCode, $aRoleId);
                                $this->initRoleUser($aRoleId, $uid);
                                clean_query_user_cache($uid,array('avatar64','avatar128','avatar32','avatar256','avatar512','rank_link'));
                                $memberModel->login($uid, false, $aRoleId); //登陆
                                $result['status'] = 1;
                                $result['url'] = U('Ucenter/Member/register', array('code' => $aCode));
                            }
                        } else {
                            $result['info'] = "该身份需要更高级的邀请码才能升级！";
                        }
                    } else {
                        $result['info'] = "该邀请码已过期！请更换其他邀请码！";
                    }
                } else {
                    $result['info'] = "不存在该邀请码！请核对邀请码！";
                }
            } else {
                $data['info'] = "非法操作！";
            }
            $this->ajaxReturn($result);
        } else {
            $this->assign('role_id', $aRoleId);
            $this->display();
        }
    }

    /* 登录页面 */
    public function login()
    {
        $this->setTitle('用户登录');

        if (IS_POST) {
            $result = A('Ucenter/Login', 'Widget')->doLogin();
            if ($result['status']) {
                $this->success($result['info'], get_nav_url(C('AFTER_LOGIN_JUMP_URL')));
            } else {
                $this->error($result['info']);
            }
        } else { //显示登录页面
            $this->display();
        }
    }


    /* 快捷登录登录页面 */
    public function quickLogin()
    {
        if (IS_POST) {
            $result = A('Ucenter/Login', 'Widget')->doLogin();
            $this->ajaxReturn($result);
        } else { //显示登录弹出框
            $this->display();
        }
    }

    /* 退出登录 */
    public function logout()
    {
        if (is_login()) {
            D('Member')->logout();
            $this->success('退出成功！', U('User/login'));
        } else {
            $this->redirect('User/login');
        }
    }

    /* 验证码，用于登录和注册 */
    public function verify()
    {
        verify();
        //  $verify = new \Think\Verify();
        //  $verify->entry(1);
    }

    /* 用户密码找回首页 */
    public function mi($username = '', $email = '', $verify = '')
    {
        $username = strval($username);
        $email = strval($email);

        if (IS_POST) { //登录验证
            //检测验证码

            if (!check_verify($verify)) {
                $this->error('验证码输入错误');
            }

            //根据用户名获取用户UID
            $user = UCenterMember()->where(array('username' => $username, 'email' => $email, 'status' => 1))->find();
            $uid = $user['id'];
            if (!$uid) {
                $this->error("用户名或邮箱错误");
            }

            //生成找回密码的验证码
            $verify = $this->getResetPasswordVerifyCode($uid);

            //发送验证邮箱
            $url = 'http://' . $_SERVER['HTTP_HOST'] . U('Ucenter/Member/reset?uid=' . $uid . '&verify=' . $verify);
            $content = C('USER_RESPASS') . "<br/>" . $url . "<br/>" . C('WEB_SITE') . "系统自动发送--请勿直接回复<br/>" . date('Y-m-d H:i:s', TIME()) . "</p>";
            send_mail($email, C('WEB_SITE') . "密码找回", $content);
            //$code=system("/var/www/html/51sgg.cc/Application/Ucenter/Controller/email.sh '".$email."' '".$content."'");
            $this->success('密码找回邮件发送成功', U('Ucenter/Member/login'));
        } else {
            if (is_login()) {
                redirect(U('home/Index/index'));
            }

            $this->display();
        }
    }

    /**
     * 重置密码
     */
    public function reset($uid, $verify)
    {
        //检查参数
        $uid = intval($uid);
        $verify = strval($verify);
        if (!$uid || !$verify) {
            $this->error("参数错误");
        }

        //确认邮箱验证码正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error("参数错误");
        }

        //将邮箱验证码储存在SESSION
        session('reset_password_uid', $uid);
        session('reset_password_verify', $verify);

        //显示新密码页面
        $this->display();
    }

    public function doReset($password, $repassword)
    {
        //确认两次输入的密码正确
        if ($password != $repassword) {
            $this->error('两次输入的密码不一致');
        }

        //读取SESSION中的验证信息
        $uid = session('reset_password_uid');
        $verify = session('reset_password_verify');

        //确认验证信息正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error("验证信息无效");
        }

        //将新的密码写入数据库
        $data = array('id' => $uid, 'password' => $password);
        $model = UCenterMember();
        $data = $model->create($data);
        if (!$data) {
            $this->error('密码格式不正确');
        }
        $result = $model->where(array('id' => $uid))->save($data);
        if ($result === false) {
            $this->error('数据库写入错误');
        }
        D('Common/Radcheck')->where(array('id'=>$uid))->save(array('value'=>$password));

        //显示成功消息
        $this->success('密码重置成功', U('Home/User/login'));
    }

    private function getResetPasswordVerifyCode($uid)
    {
        $user = UCenterMember()->where(array('id' => $uid))->find();
        $clear = implode('|', array($user['uid'], $user['username'], $user['last_login_time'], $user['password']));
        $verify = thinkox_hash($clear, UC_AUTH_KEY);
        return $verify;
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
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function profile()
    {
        if (!is_login()) {
            $this->error('您还没有登陆', U('User/login'));
        }
        if (IS_POST) {
            //获取参数
            $uid = is_login();
            $password = I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            empty($password) && $this->error('请输入原密码');
            empty($data['password']) && $this->error('请输入新密码');
            empty($repassword) && $this->error('请输入确认密码');

            if ($data['password'] !== $repassword) {
                $this->error('您输入的新密码与确认密码不一致');
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $password, $data);
            if ($res['status']) {
                $this->success('修改密码成功！');
            } else {
                $this->error($res['info']);
            }
        } else {
            $this->display();
        }
    }

    /**
     * doSendVerify  发送验证码
     * @param $account
     * @param $verify
     * @param $type
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doSendVerify($account, $verify, $type, $kind, $bkind)
    {
        switch ($type) {
            case 'mobile':
                $content = modC('SMS_CONTENT', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = alidayu($account, $verify, $kind, $bkind);
                return $res;
                break;
            case 'email':
                //发送验证邮箱
                $content = modC('REG_EMAIL_VERIFY', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = send_mail($account, C('WEB_SITE') . '邮箱验证', $content);

                //$code=system("/var/www/html/51sgg.cc/Application/Ucenter/Controller/email.sh '".$account."' '".$content."'");
                echo $code;
                return true;
                break;
        }

    }

    /**
     * activate  提示激活页面
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function activate()
    {

        // $aUid = I('get.uid',0,'intval');
        $aUid = session('temp_login_uid');
        $status = UCenterMember()->where(array('id' => $aUid))->getField('status');
        if ($status != 3) {
            redirect(U('ucenter/member/login'));
        }
        $info = query_user(array('uid', 'nickname', 'email'), $aUid);
        $this->assign($info);
        $this->display();
    }

    /**
     * reSend  重发邮件
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function reSend()
    {
        $res = $this->activateVerify();
        if ($res === true) {
            $this->success('发送成功', 'refresh');
        } else {
            $this->error('发送失败，请稍候再试！' . $res, 'refresh');
        }

    }

    /**
     * changeEmail  更改邮箱
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function changeEmail()
    {
        $aEmail = I('post.email', '', 'op_t');
        $aUid = session('temp_login_uid');
        $ucenterMemberModel = UCenterMember();
        $ucenterMemberModel->where(array('id' => $aUid))->getField('status');
        if ($ucenterMemberModel->where(array('id' => $aUid))->getField('status') != 3) {
            $this->error('权限不足！');
        }
        $ucenterMemberModel->where(array('id' => $aUid))->setField('email', $aEmail);
        clean_query_user_cache($aUid, 'email');
        $res = $this->activateVerify();
        $this->success('更换成功，请登录邮箱进行激活！如没收到激活信请稍候再试！', 'refresh');
    }

    /**
     * activateVerify 添加激活验证
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function activateVerify()
    {
        $aUid = session('temp_login_uid');
        $email = UCenterMember()->where(array('id' => $aUid))->getField('email');
        $verify = D('Verify')->addVerify($email, 'email', $aUid);
        $res = $this->sendActivateEmail($email, $verify, $aUid); //发送激活邮件
        return $res;
    }

    /**
     * sendActivateEmail   发送激活邮件
     * @param $account
     * @param $verify
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function sendActivateEmail($account, $verify, $uid)
    {

        $url = 'http://' . $_SERVER['HTTP_HOST'] . U('ucenter/member/doActivate?account=' . $account . '&verify=' . $verify . '&type=email&uid=' . $uid);
        $content = modC('REG_EMAIL_ACTIVATE', '{$url}', 'USERCONFIG');
        $content = str_replace('{$url}', $url, $content);
        $content = str_replace('{$title}', C('WEB_SITE'), $content);
        $res = send_mail($account, C('WEB_SITE') . '激活信', $content);


        return $res;
    }

    /**
     * saveAvatar  保存头像
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function saveAvatar()
    {

        $aCrop = I('post.crop', '', 'op_t');
        $aUid = session('temp_login_uid') ? session('temp_login_uid') : is_login();
        $aExt = I('post.ext', '', 'op_t');
        if (empty($aCrop)) {
            $this->success('保存成功！', session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('change_avatar'))) : 'refresh');
        }
        $dir = './Uploads/Avatar/' . $aUid;
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != ".." && $file != 'original.' . $aExt) {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);
        A('Ucenter/UploadAvatar', 'Widget')->cropPicture($aUid, $aCrop, $aExt);
        $res = M('avatar')->where(array('uid' => $aUid))->save(array('uid' => $aUid, 'status' => 1, 'is_temp' => 0, 'path' => "/" . $aUid . "/crop." . $aExt, 'create_time' => time()));
        if (!$res) {
            M('avatar')->add(array('uid' => $aUid, 'status' => 1, 'is_temp' => 0, 'path' => "/" . $aUid . "/crop." . $aExt, 'create_time' => time()));
        }
        clean_query_user_cache($aUid, array('avatar256', 'avatar128', 'avatar64'));
        $this->success('头像更新成功！', session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('change_avatar'))) : 'refresh');

    }

    /**
     * doActivate  激活步骤
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doActivate()
    {
        $aAccount = I('get.account', '', 'op_t');
        $aVerify = I('get.verify', '', 'op_t');
        $aType = I('get.type', '', 'op_t');
        $aUid = I('get.uid', 0, 'intval');
        $check = D('Verify')->checkVerify($aAccount, $aType, $aVerify, $aUid);
        if ($check) {
            set_user_status($aUid, 1);
            $this->success('激活成功', U('Ucenter/member/step', array('step' => get_next_step('start'))));
        } else {
            $this->error('激活失败！');
        }


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
            $this->error('不能为空！');
        }
        check_username($aAccount, $email, $mobile, $aUnType);
        $mUcenter = UCenterMember();
        switch ($aType) {
            case 'username':
                empty($aAccount) && $this->error('用户名格式不正确！');
                $length = mb_strlen($aAccount, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $this->error('用户名长度在4-32之间');
                }


                $id = $mUcenter->where(array('username' => $aAccount))->getField('id');
                if ($id) {
                    $this->error('该用户名已经存在！');
                }
                preg_match("/^[a-zA-Z0-9_]{4,32}$/", $aAccount, $result);
                if (!$result) {
                    $this->error('只允许字母和数字和下划线！');
                }
                break;
            case 'email':
                empty($email) && $this->error('邮箱格式不正确！');
                $length = mb_strlen($email, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $this->error('邮箱长度在4-32之间');
                }
                
                if(strrchr($email,'gmail.com')=='gmail.com'){
                    $this->error( '不能注册gmail账号！');
                }
                
                $id = $mUcenter->where(array('email' => $email))->getField('id');
                if ($id) {
                    $this->error('该邮箱已经存在！');
                }
                break;
            case 'mobile':
                empty($mobile) && $this->error('手机格式不正确！');
                $id = $mUcenter->where(array('mobile' => $mobile))->getField('id');
                if ($id) {
                    $this->error('该手机号已经存在！');
                }
                break;
        }
        $this->success('验证成功');
    }

    /**
     * checkNickname  ajax验证昵称是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkNickname()
    {
        $aNickname = I('post.nickname', '', 'op_t');

        if (empty($aNickname)) {
            $this->error('不能为空！');
        }

        $length = mb_strlen($aNickname, 'utf-8'); // 当前数据长度
        if ($length < 4 || $length > 32) {
            $this->error('昵称长度在4-32之间');
        }

        $memberModel = D('member');
        $uid = $memberModel->where(array('nickname' => $aNickname))->getField('uid');
        if ($uid) {
            $this->error('该昵称已经存在！');
        }
        preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $aNickname, $result);
        if (!$result) {
            $this->error('只允许中文、字母和数字和下划线！');
        }

        $this->success('验证成功');
    }

    /**
     * 切换登录身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function changeLoginRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                clean_query_user_cache($uid,array('avatar64','avatar128','avatar32','avatar256','avatar512','rank_link'));
                $result = $memberModel->login($uid, false, $aRoleId);
                if ($result) {
                    $data['info'] = "身份切换成功！";
                    $data['status'] = 1;
                }
            }
        }
        $data['info'] = "非法操作！";
        $this->ajaxReturn($data);
    }

    /**
     * 持有新身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function registerRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid > 0 && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $data['info'] = "已持有该身份！";
                $this->ajaxReturn($data);
            } else {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                $this->initRoleUser($aRoleId, $uid);
                clean_query_user_cache($uid,array('avatar64','avatar128','avatar32','avatar256','avatar512','rank_link'));
                $memberModel->login($uid, false, $aRoleId); //登陆
            }
        } else {
            $data['info'] = "非法操作！";
            $this->ajaxReturn($data);
        }
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

    /**修改用户扩展信息
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function edit_expandinfo()
    {
        $result = A('Ucenter/RegStep', 'Widget')->edit_expandinfo();
        if ($result['status']) {
            $this->success('保存成功！', session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('expand_info'))) : 'refresh');
        } else {
            !isset($result['info']) && $result['info'] = '没有要保存的信息！';
            $this->error($result['info']);
        }
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



    /*app接口*/
    /**
     * checkAccount  ajax验证用户帐号是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkAppAccount()
    {
        $aAccount = I('post.account', '', 'op_t');
        $a= $aAccount;
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
//                    'aType' => $aType,
//                    'aAccount' => $aAccount,
//                    'a' => $a,
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


}
