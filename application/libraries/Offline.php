<?php

/**
 * 下线 [ 关闭连接 ]
 * 
 * @author cuikai 2016-5-6
 */
class Offline {

    protected $CI;

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->helper('cuikai');
    }

    public function init($params) {
        $this->fd = $fd = $params['fd'];
        $this->from_id = $from_id = isset($params['from_id']) ? $params['from_id'] : 'ws';
        $this->tag = isset($params['tag']) ? $params['tag'] : '';
        
        echo "用户 $fd 下线了 [ 所在进程池:$from_id ]\n";                

        $rdata['command'] = 'offline';
        $rdata['who'] = get_fdinfo($this->fd);
        $rdata['sendtime'] = date($this->CI->config->item('chat_date_format'));

        del_fdinfo($this->fd);
        
        if (empty($rdata['who'])) {
            return false;
        }            
        
        send_all($rdata, $this->fd);
    }

    public function clear($fd) {
        del_fdinfo($fd);
    }
}

