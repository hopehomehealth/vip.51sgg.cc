<?php

namespace Addons\Report\Controller;

use Home\Controller\AddonsController;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminTreeListBuilder;
require_once(ONETHINK_ADDON_PATH . 'Report/Common/function.php');


class ReportController extends AddonsController
{
    /**
     * 获取管理员配置参数
     */
    public function eject()
    {
        parse_str(I('get.param'), $param);
        $param['data']=json_encode($param['data']);
        $this->assign('param', $param);
        $config = _getAddonsCinfig();$this->assign("reason", $config);
        $this->display(T('Addons://Report@Report/eject'));

    }

    /**
     * 对举报的信息进行数据库写入操作
     */
    public function addReport()
    {
        parse_str(I('get.param'), $param);      //含有url,type,data,
        $preason = I('post.reason', '', 'op_t');
        $pcontent = I('post.content', '', 'op_t');

        $data['uid'] = is_login();
        $data['url'] = $param['url'];
        $data['reason'] = $preason;        //  举报原因
        $data['content'] = $pcontent;      // 举报描述
        $data['type'] = $param['type'];
        $data['data'] = $param['data'];


        $result = D('Addons://Report/Report')->addData($data);
        if ($result) {
            D('Message')->sendMessageWithoutCheckSelf('1', '有一封举报，请到后台查看。', '您有新的系统消息','',is_login(), 0);
            $this->success('举报成功', 0);
        } else {
            $this->error('举报失败');
        }
    }








}