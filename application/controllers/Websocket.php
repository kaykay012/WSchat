<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 服务器端聊天支持
 * 
 * @author cuikai 2016-5-6
 */
class Websocket extends CI_Controller {

    public $serv;
    public $pdo;
    public $redis;
    public $client = NULL;
    
    // redis list 名称 [ 客户端连接信息列表 ]
    public $swoole_listname = 'swoole:fdlist';
    // redis hash 名称前缀 [ 客户端连接详情 ]
    public $swoole_hashname = 'swoole:fdinfo:';

    public function __construct() {
        parent::__construct();

        $this->load->config('swoole_server');
    }

    public function init() {
        // server 实例
        $host = $this->config->item('listen_ip');
        $port = $this->config->item('listen_port');
        $this->serv = new swoole_websocket_server($host, $port);

        // 同时监听一个tcp协议的端口 
        $tcp_listen_port = $this->config->item('tcp_listen_port');
        $sock_type = $this->config->item('sock_type');
        $this->serv->addlistener($host, $tcp_listen_port, $sock_type);

        // 配置        
        $this->serv->set($this->config->item('default'));

        //* 回调事件绑定
        // 主进程
        $this->serv->on('Start', array($this, 'on_start'));
        // worker 进程
        $this->serv->on('WorkerStart', array($this, 'on_work_start'));

        // websocket
        $this->serv->on('open', array($this, 'on_open'));
        $this->serv->on('message', array($this, 'on_message'));

        // tcp
        $this->serv->on('Connect', array($this, 'on_connect'));
        $this->serv->on('Receive', array($this, 'on_receive'));

        // 关闭连接
        $this->serv->on('Close', array($this, 'on_close'));

        // task 进程
        $this->serv->on('Task', array($this, 'on_task'));
        $this->serv->on('Finish', array($this, 'on_finish'));

        // 开始运行
        $this->run();
    }

    public function on_start() {
        cli_set_process_title("reload_master");
    }

    public function on_work_start($serv, $worker_id) {
        echo "worker start [ id:$worker_id ]\n";

        $redis = $this->redis = new Redis();
        $redis_res = $this->redis->connect($this->config->item('redis_host'), $this->config->item('redis_port'));
        if (!$redis_res) {
            echo "redis connect failed\n";
        }

        // 判定是否为Task Worker进程
        if ($worker_id >= $serv->setting['worker_num']) {
            $this->load->library('database');
            $this->database->init();
        }
        
        // 启动一个定时器, 用于心跳检测
        if(isset($this->config->item('default')['heartbeat_check_interval'])) {

            $serv->tick($this->config->item('default')['heartbeat_check_interval'] + 1, function() use ($serv) {
                $conn_list = $serv->connection_list();
                if(!$conn_list) {
                    return false;
                }
                $this->load->library('offline');
                foreach ($conn_list as $fd) {
                    if(!$serv->connection_info($fd)) {
                        $this->offline->clear($fd);
                    }
                }
            });
        }
    }

    public function on_open($serv, $request) {
        //echo "Client {$request->fd} connect\n";
        //return false;
        
        $fdinfo = $this->serv->connection_info($request->fd);
        if (!isset($fdinfo['websocket_status'])) { 
            return false;
        }

        $this->load->library('online');
        $this->online->init(array('fd' => $request->fd));
    }

    public function on_message($serv, $frame) {
        //echo "Client {$frame->fd} say:{$frame->data}\n";
        //return false;
        
        $data = trim($frame->data);
        if (empty($data)) {
            return false;
        }

        // 说话
        $this->load->library('say');
        $this->say->message(array('fd' => $frame->fd, 'data' => $frame->data));
    }

    public function on_connect($serv, $fd, $from_id) {
        //echo "Client $fd connect [ on worker id:$from_id ]\n";
        //return false;
        
        $fdinfo = $this->serv->connection_info($fd);
        if (isset($fdinfo['websocket_status'])) { 
            return false;
        }

        $this->load->library('online');
        $this->online->init(array('fd' => $fd, 'from_id' => $from_id));
    }

    public function on_receive($serv, $fd, $from_id, $data) {
        //echo "Client $fd [ worker id:$from_id ] say:$data\n";        
        //$this->serv->send($fd, "You said:$data\n");
        //return false;
        
        // 说话                
        $this->load->library('say');
        $this->say->message(array('fd' => $fd, 'from_id' => $from_id, 'data' => $data));
    }

    public function on_close($serv, $fd) {
        //echo "Client {$fd} close connection [ worker id:$from_id ]\n";        

        $this->load->library('offline');
        $this->offline->init(array('fd' => $fd));
    }

    public function on_task($serv, $task_id, $from_id, $data) {
        $sql = json_decode($data, true);
        try {
            $statement = $this->pdo->prepare($sql['sql']);
            $statement->execute($sql['param']);
            return true;
        } catch (PDOException $e) {
            $errcode = $e->errorInfo[1];
            if ($errcode == 2006 || $errcode == 2013) {
                $this->load->library('database');
                $this->database->init();

                $statement = $this->pdo->prepare($sql['sql']);
                $statement->execute($sql['param']);
                return true;
            }
            return false;
        }
    }

    public function on_finish($serv, $task_id, $data) {
        
    }

    public function send($to_fd, $data, $is_encodejson=true) {
        
        $this->load->library('transmit');
        $this->transmit->init(array('to_fd' => $to_fd, 'data' => $data, 'is_encodejson' => $is_encodejson));
    }

    private function run() {
        $this->serv->start();
    }

}