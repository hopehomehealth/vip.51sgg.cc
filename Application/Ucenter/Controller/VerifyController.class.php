<?php
/**
 * Created by PhpStorm.
 * User: caipeichao
 * Date: 14-3-11
 * Time: PM3:40
 */

namespace Ucenter\Controller;

use Think\Controller;

class VerifyController extends Controller
{


    /**
     * sendVerify 发送验证码
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function sendVerify()
    {
        $aAccount = $cUsername = I('post.account', '', 'op_t');
        $aType = I('post.type', '', 'op_t');
        $aType = $aType == 'mobile' ? 'mobile' : 'email';
        $aAction = I('post.action', 'config', 'op_t');
        $aKind = I('post.kind', '', 'op_t');
        $aKind = $aKind == 'change' ? 'change' : 'regist';

        if (!check_reg_type($aType)) {
            $str = $aType == 'mobile' ? '手机' : '邮箱';
            $this->error($str . '选项已关闭！');
        }
        
        if(strrchr($aAccount,'gmail.com')=='gmail.com'){
            $this->error($str . '不能注册gmail账号！');
        }

        if (empty($aAccount)) {
            $this->error('帐号不能为空');
        }
        check_username($cUsername, $cEmail, $cMobile);

        if ($aType == 'email' && empty($cEmail)) {
            $this->error('请验证邮箱格式是否正确');
        }
        if ($aType == 'mobile' && empty($cMobile)) {
            $this->error('请验证手机格式是否正确');
        }

        $checkIsExist = UCenterMember()->where(array($aType => $aAccount))->find();
        if ($checkIsExist) {
            $str = $aType == 'mobile' ? '手机' : '邮箱';
            $this->error('该' . $str . '已被其他用户使用！');
        }

        $verify = D('Verify')->addVerify($aAccount, $aType);
        if (!$verify) {
            $this->error('发送失败！');
        }

        $res = A(ucfirst($aAction))->doSendVerify($aAccount, $verify, $aType, $aKind);
        if ($res === true) {
            $this->success('发送成功，请查收');
        } else {
            $this->error($res);
        }


    }

    /**
     * app发送验证码
     * sendVerify 发送验证码
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function sendAppVerify()
    {
        $aAccount = $cUsername = I('post.account', '', 'op_t');
        $aType = I('post.type', '', 'op_t');
        $aType = $aType == 'mobile' ? 'mobile' : 'email';
        $aAction = I('post.action', 'config', 'op_t');
        $bKind = I('post.bkind', '', 'op_t');
        $aKind = I('post.kind', '', 'op_t');
        $aKind = $aKind == 'change' ? 'change' : 'regist';
        $bKind = $bKind == 'iosan' ? 'iosan' : 'pc';

        if (!check_reg_type($aType)) {
            $str = $aType == 'mobile' ? '手机' : '邮箱';
            $arr = array(
                'msg'=>'选项已关闭！',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        if(strrchr($aAccount,'gmail.com')=='gmail.com'){
            $arr = array(
                'msg'=>'不能注册gmail账号！',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        if (empty($aAccount)) {
            $arr = array(
                'msg'=>'帐号不能为空',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }
        check_username($cUsername, $cEmail, $cMobile);

        if ($aType == 'email' && empty($cEmail)) {
            $arr = array(
                'msg'=>'请验证邮箱格式是否正确',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }
        if ($aType == 'mobile' && empty($cMobile)) {
            $arr = array(
                'msg'=>'请验证手机格式是否正确',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        $checkIsExist = UCenterMember()->where(array($aType => $aAccount))->find();
        if ($checkIsExist) {
            $str = $aType == 'mobile' ? '手机' : '邮箱';
            $arr = array(
                'msg'=>'已被其他用户使用！',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        $verify = D('Verify')->addVerify($aAccount, $aType);
        if (!$verify) {
            $arr = array(
                'msg'=>'发送失败！',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        $res = A(ucfirst($aAction))->doSendVerify($aAccount, $verify, $aType, $aKind, $bKind);
        if ($res == "true") {
            $arr = array(
                'msg'=>'发送成功，请查收',
                'status'=>'0000',
            );
//            $this->ajaxReturn($arr,'JSON');
            exit(json_encode($arr));
        } else {
            $arr = array(
                'msg'=>$res,
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }


    }


    public function sendAppChangeVerify()
    {
        $aAccount = $cUsername = I('post.account', '', 'op_t');
        $aType = I('post.type', '', 'op_t');
        $aType = $aType == 'mobile' ? 'mobile' : 'email';
        $aAction = I('post.action', 'config', 'op_t');
        $bKind = I('post.bkind', '', 'op_t');
        $aKind = I('post.kind', '', 'op_t');
        $aKind = $aKind == 'change' ? 'change' : 'regist';
        $bKind = $bKind == 'iosan' ? 'iosan' : 'pc';

        if (!check_reg_type($aType)) {
            $str = $aType == 'mobile' ? '手机' : '邮箱';
            $arr = array(
                'msg'=>'选项已关闭！',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        if(strrchr($aAccount,'gmail.com')=='gmail.com'){
            $arr = array(
                'msg'=>'不能注册gmail账号！',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        if (empty($aAccount)) {
            $arr = array(
                'msg'=>'帐号不能为空',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }
        check_username($cUsername, $cEmail, $cMobile);

        if ($aType == 'email' && empty($cEmail)) {
            $arr = array(
                'msg'=>'请验证邮箱格式是否正确',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }
        if ($aType == 'mobile' && empty($cMobile)) {
            $arr = array(
                'msg'=>'请验证手机格式是否正确',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }


        $verify = D('Verify')->addVerify($aAccount, $aType);
        if (!$verify) {
            $arr = array(
                'msg'=>'发送失败！',
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }

        $res = A(ucfirst($aAction))->doSendVerify($aAccount, $verify, $aType, $aKind, $bKind);
        if ($res == "true") {
            $arr = array(
                'msg'=>'发送成功，请查收',
                'status'=>'0000',
            );
//            $this->ajaxReturn($arr,'JSON');
            exit(json_encode($arr));
        } else {
            $arr = array(
                'msg'=>$res,
                'status'=>'1000',
            );
            $this->ajaxReturn($arr);
        }


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

}