<?php

/**
 * Description
 *
 * @author cuikai 2016-5-10
 */
$config['serv_number'] = 1;                 //服务器编号 [ 第一台socket服务器 ]

$setting['worker_num']          = 1;        //设置启动的worker进程数
$setting['task_worker_num']     = 1;        //配置task进程的数量
$setting['daemonize']           = 1;        //守护进程化,长时间运行的服务器端程序必须启用此项
$setting['max_request']         = 10000;    //设置worker进程的最大任务数
$setting['dispatch_mode']       = 2;        //数据包分发策略,2 固定模式
$setting['log_file']            = '/home/kay/logs/websocket.log';//日志路径
$setting['log_level']           = 0;        //记录日志级别
//$setting['heartbeat_check_interval'] = 3600;//心跳检测
$setting['open_eof_check']      = true;     //打开EOF检测
$setting['package_eof']         = "\r\n";   //设置EOF
$setting['open_length_check']   = true;        		 //打开包长检测特性
$setting['package_max_length']  = 1024*1024*0.5;     //设置最大数据包尺寸

$config['default'] = $setting;

// websocket协议
$config['listen_ip']    = '0.0.0.0';
$config['listen_port']  = 9502;

// tcp协议
$config['tcp_listen_port']  = 9505;
$config['sock_type']        = SWOOLE_SOCK_TCP;

// redis
$config['redis_host'] = '127.0.0.1';
$config['redis_port'] = 6379;

// forward host and port [ 消息转发服务器 ]
$config['forward_host'] = '172.16.200.44';
$config['forward_port'] = 9506;

// client 协议类型
//$config['client_sock_type'] = SWOOLE_SOCK_TCP;