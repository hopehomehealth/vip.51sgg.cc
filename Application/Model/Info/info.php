<?php

return array(
    //模块名
    'name' => 'Model',
    //别名
    'alias' => '模型管理',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => 'MYsql数据库生成管理工具',
    //开发者
    'developer' => '基于Onethink模型管理修改而来',
    //开发者网站
    'website' => 'http://www.onethink.cn',
    //前台入口，可用U函数
    'entry' => '无',

    'admin_entry' => 'Admin/Model/index',

    'icon' => 'list-alt',

    'can_uninstall' => 1
);