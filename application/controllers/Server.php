<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 服务器端聊天支持
 * 
 * @author cuikai 2016-5-6
 */
class Server extends CI_Controller {

    public $serv;
    public $pdo;
    public $redis;

    public function init() {
        // server 实例
        $host = '0.0.0.0';
        $port = 9501;
        $mode = SWOOLE_PROCESS;
        $sock_type = SWOOLE_SOCK_TCP;
        $this->serv = new swoole_server($host, $port, $mode, $sock_type);

        // 配置
        $setting['worker_num'] = 2;
        $setting['daemonize'] = 1;
        $setting['max_request'] = 0;
        $setting['dispatch_mode'] = 2;
        $setting['task_worker_num'] = 1;
        $setting['log_file'] = '/home/kay/logs/server.log';
        $setting['log_level'] = 0;
        $this->serv->set($setting);

        // 回调事件绑定
        $this->serv->on('Start', array($this, 'on_start'));
        $this->serv->on('WorkerStart', array($this, 'on_work_start'));
        $this->serv->on('Connect', array($this, 'on_connect'));
        $this->serv->on('Receive', array($this, 'on_receive'));
        $this->serv->on('Close', array($this, 'on_close'));
        $this->serv->on('Task', array($this, 'on_task'));
        $this->serv->on('Finish', array($this, 'on_finish'));

        // 开始运行
        $this->run();
    }

    public function on_start() {
        cli_set_process_title("reload_master");
    }

    public function on_work_start($serv, $worker_id) {
        //Seaslog::info("worker start [$worker_id]");
        echo "worker start [ id:$worker_id ]\n";

        $this->redis = new Redis();
        $redis_res = $this->redis->connect('127.0.0.1', 6379);
        if (!$redis_res) {
            echo "redis connect failed\n";
        }

        // 判定是否为Task Worker进程
        if ($worker_id >= $serv->setting['worker_num']) {
//            $this->pdo = new PDO(
//                    "mysql:host=localhost;port=3306;dbname=pendiy", "root", "", array(
//                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8';",
//                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//                PDO::ATTR_PERSISTENT => true
//                    )
//            );
        }
    }

    public function on_connect($serv, $fd, $from_id) {
        //echo "Client $fd connect [ on worker id:$from_id ]\n";
        //return;
        // 上线
        $this->load->library('online');
        $this->online->init(array('serv' => $serv, 'fd' => $fd, 'from_id' => $from_id));
    }

    public function on_receive(swoole_server $serv, $fd, $from_id, $data) {
        //echo "Client $fd [ worker id:$from_id ] say:$data\n";
        //return;

        $serv->send($fd, "You said:$data\n");

        // 说话
        $this->load->library('say');
        $this->say->message(array('serv' => $serv, 'fd' => $fd, 'from_id' => $from_id, 'data' => $data));
    }

    public function on_close($serv, $fd, $from_id) {
        //echo "Client {$fd} close connection [ worker id:$from_id ]\n";
        // 下线
        $this->load->library('offline');
        $this->offline->init(array('serv' => $serv, 'fd' => $fd, 'from_id' => $from_id));
    }
    
    public function send($to_fd, $data="\r\n") {
        $this->serv->send($to_fd, json_encode($data) . "\n");
    }
    
    public function on_task($serv, $task_id, $from_id, $data) {
        
    }

    public function on_finish($serv, $task_id, $data) {
        
    }

    private function run() {
        $this->serv->start();
    }

}
