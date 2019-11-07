<?php
/**
 * 公共配置文件
 */
return array(
    
    'ROND_SEED'=>"abcabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%",  //用于产生随机字符串
    'DEFAULT_PASS'=>'000000',
    'COMPANY_ID'=>25,
    'ENTER_NAME'=>'集团',
    'PUSH_URL'=>'http://192.168.5.102:8080/supplierMsg/PushMessage',  //消息推送的url
//     'PUSH_URL'=>'http://192.168.18.215/supplierMsg/PushMessage',  //消息推送的url
    'CHAT_SHOW_NUM'=>10,
    'CHAT_EVERY_TIME'=>6, //聊天内容每隔多少个小时显示时间
    'ROUTER'=>array(
        'START'=>true,
        'RULE'=>array(
            '/p/:id'=>'/Web/Index/content'
        )
    ),
    /**
     * 数据库配置部分
     */
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_USER'  => 'root',
    'DB_PASSWORD'  => 'djz082818',
    'DB_DBNAME'    => 'jiyi',
    'DB_PREFIX'    => '',
    'DB_CHARSET'   => 'utf8',
    'DB_PORT'   => '3306',
    'USE_PDO'   => 'yes',
    'SLAVE_NO'=>'2',    //指定从服务器来进行读操作
    'MASTER_NUM'=>1,    //主服务器的数量
    'DEPLOY_TYPE'=>0,   //数据库部署方式，1 表示主从分离   0 表示单一服务器
    'RW_SEPRATE'=>false,    //读写是否分离
    
    /**
     * 可访问的功能模块
     */
    'MODULE' => array('Onlinebid','News','Chat','Admin','User'),
    
    /*
     * 默认访问的功能模块
     */
    'DEFAULT_MODULE'    => 'Admin',
    'DEFAULT_ACTION'    => 'Index',
    'DEFAULT_FUNC'    => 'index',
    'URL'   => array(
        'M_NAME' => 'm', //模块参数名称
        'A_NAME' => 'a', //控制器参数名称
        'F_NAME' => 'f', //方法参数名称
        'P_NAME' => 'p', //参数键名
    ),
    
    /* SESSION设置 */
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_OPTIONS'       =>  array(), // session 配置数组 支持type name id path expire domain 等参数
    'SESSION_TYPE'          =>  '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'        =>  '', // session 前缀
    //'VAR_SESSION_ID'      =>  'session_id',     //sessionID的提交变量
    
    /*
     * 模板配置选项
     */
    'TPL_EXT_NAME' => '.html',
    
    /*
     * 预定义常量，在模板中可以直接使用的
     */
    'CONSTANTS' => array(
        'PUBLIC' => '/Module/Public',
    ),
    
    'DEFAULT_MOBILE_PASS' => '000000',
    
);