<?php

/**
 * 上线 [ 成功建立连接 ]
 * 
 * @author cuikai 2016-5-6
 */
class Online {

    protected $CI;

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->helper('cuikai');
    }

    public function init($params) {
        $this->fd = $fd = $params['fd'];
        $this->from_id = $from_id = isset($params['from_id']) ? $params['from_id'] : 'ws';
        
        // 客户端连接信息 存入redis
        lpush_fdlist($fd);

        echo "用户 $fd 上线了 [ 所在进程池:$from_id ]\n";

        $rdata['command'] = 'clientlist';
        $rdata['fdlist'] = get_fdlist(0, 10, $fd);
        
        $this->CI->send($this->CI->config->item('serv_number') . $fd, $rdata);
    }

}

