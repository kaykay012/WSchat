<?php

/**
 * 说话 [ 处理收到的消息 ]
 * 
 * {"command":"reg",    "info":{"name":"Kenith"}}                 登记注册
 * {"command":"sayto",  "info":{"to":3,"msg":"hi,i am cuikai"}}   指定客户端说话
 * {"command":"sayall", "info":{"msg":"hello,guys"}}              向所有客户端说话
 * 
 * @author cuikai 2016-5-6
 */
class Say {

    protected $CI;
    protected $commands = array('reg', 'sayto', 'sayall');
    protected $message_type = array('txt' => 1, 'img' => 2);

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->helper('cuikai');
    }

    public function message($params) {
        $this->fd = $fd = $params['fd'];
        $this->from_id = $from_id = isset($params['from_id']) ? $params['from_id'] : '';

        $data = $params['data'];

        echo "用户 {$this->fd} [ 所在进程池:{$this->from_id} ] 说:{$data}\n";

        //$this->CI->send($this->CI->config->item('serv_number') . $this->fd, 'You said:' . $data, false);
        
        // 处理客户端发来的消息
        $this->analyse($data);
    }

    private function analyse($data) {
        $data = json_decode(trim($data), TRUE);

        if(!is_array($data)) {            
            $this->CI->send($this->CI->config->item('serv_number') . $this->fd, "Error:The data format is not correct\r\n", false);
            return false;
        }

        // 检测是否是转发的消息
        if (isset($data['forward']) && $data['forward'] == 1) {
            // 接收并处理转发的消息
            $this->recive_forward($data);
            return true;
        }

        $command = isset($data['command']) ? trim($data['command']) : '';   // 指令 | 动作
        $msgid = isset($data['msgid']) ? intval($data['msgid']) : -1;
        $info = isset($data['info']) ? $data['info'] : '';               // 消息详情        

        $to = isset($info['to']) ? intval($info['to']) : 0;            // 发送给谁
        $msg = isset($info['msg']) ? trim($info['msg']) : '';           // 消息内容
        $type = isset($info['type']) ? intval($info['type']) : 1;        // 消息类型
        $name = isset($info['name']) ? trim($info['name']) : '';         // 用户名称

        if($msgid >= 0) {
            $confirm_data['command'] = 'confirm';
            $confirm_data['msgid'] = $msgid;
            $confirm_data['sendtime'] = date($this->CI->config->item('chat_date_format'));

            $this->CI->send($this->CI->config->item('serv_number') . $this->fd, $confirm_data);
        }

        // 检测指令
        if (!in_array($command, $this->commands)) {
            $this->CI->send($this->CI->config->item('serv_number') . $this->fd, "command only accept [ ". join(', ', $this->commands) ." ]\r\n", false);
            return false;
        }

        // 检测消息类型
        if (!in_array($type, $this->message_type)) {
            $type = 1; // 1 txt, 2 img
        }

        /* $this->$command;
          $this->CI->load->library($command);
          $this->CI->$command($this->fd, $info); */

        switch ($command) {
            // 注册用户名
            case 'reg':
                $this->reg($this->fd, $name);
                break;

            // 向某个用户说话
            case 'sayto':
                $this->sayto($this->fd, $to, $msg, $type);
                break;

            // 向所有人说话
            case 'sayall':
                $this->sayall($this->fd, $msg, $type);
                break;

            default:
                echo "no command\n";
                break;
        }
    }

    /**
     * 接收并处理转发的消息
     * @param type $forward_data
     */
    private function recive_forward($forward_data) {
        $to_fd = $forward_data['to_fd']; // 接收者
        $data = $forward_data['data'];  // 数据体
        
        //echo "recive_forward:$to_fd [ ".  json_encode($data)." ]\n";
        
        if(is_array($to_fd)) {
            foreach($to_fd as $fd) {
                $this->CI->send($fd, $data);
            }
            return true;
        }
        
        $this->CI->send($to_fd, $data);
        
        return true;
    }

    private function reg($fd, $name) {
        // 其他配置
        $this->CI->load->config('other');

        if (!$this->CI->serv->exist($fd)) {
            $rdata['command'] = 'reg';
            $rdata['msg'] = '注册失败 connect status is closed';
            $this->CI->send($fd, $rdata);
        }

        // 设置姓名
        if(empty($name)) {
            $name = '匿名' . $fd;
        }
        set_fdinfo($fd, 'name', $name);

        // 登记/注册 成功后欢迎语
        $name = htmlspecialchars($name);
        $rdata['command'] = 'reg';
        $rdata['msg'] = sprintf($this->CI->config->item('welcome'), $name);
        $rdata['sendtime'] = date($this->CI->config->item('chat_date_format'));

        $this->CI->send($this->CI->config->item('serv_number') . $fd, $rdata);

        // 通知其他所有用户 [ 我上线了 ]       
        $rdata2['command'] = 'online';
        $rdata2['who'] = get_fdinfo($fd);
        $rdata2['sendtime'] = date($this->CI->config->item('chat_date_format'));

        send_all($rdata2, $fd);

        return true;
    }

    private function sayto($fd, $to_fd, $msg, $type) {
        if (empty($msg)) {
            return false;
        }
        $_msg = $msg;

        $info['msg'] = htmlspecialchars($msg);
        $info['who'] = get_fdinfo($fd);
        $info['type'] = $type;

        $rdata['command'] = 'sayto';
        $rdata['info'] = $info;
        $rdata['sendtime'] = date($this->CI->config->item('chat_date_format'));
        
        // 如果需要转发
        $is_forward = is_forward($to_fd);
        if ($is_forward) {
            send_forward($rdata, $to_fd);
            return true;
        }

        $this->CI->send($to_fd, $rdata);

        $toinfo = get_fdinfo($to_fd);

        $sql = array(
            'sql' => 'Insert into chat values("",?,?,?,?,?)',
            'param' => array($info['who']['name'], $toinfo['name'], $type, $_msg, time())
        );
        $this->CI->serv->task(json_encode($sql));
        
        return true;
    }

    private function sayall($fd, $msg, $type) {
        if (empty($msg)) {
            return false;
        }
        $_msg = $msg;

        $info['msg'] = htmlspecialchars($msg);
        $info['who'] = get_fdinfo($fd);
        $info['type'] = $type;

        $rdata['command'] = 'sayall';
        $rdata['info'] = $info;
        $rdata['sendtime'] = date($this->CI->config->item('chat_date_format'));

        send_all($rdata, $fd);

        $sql = array(
            'sql' => 'Insert into chat values("",?,?,?,?,?)',
            'param' => array($info['who']['name'], '-8000', $type, $_msg, time())
        );
        $this->CI->serv->task(json_encode($sql));
        
        return true;
    }

}
