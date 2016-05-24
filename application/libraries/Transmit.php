<?php

/**
 * 发送消息
 *  
 * @author cuikai 16-5-13 上午10:02
 */
class Transmit {

    protected $CI;    

    public function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->helper('cuikai');
    }
    
    public function init($params) {
    	$to_fd = $params['to_fd'];
    	$data  = $params['data'];


        $hashname = $this->CI->swoole_hashname . $to_fd;        
        $fdinfo = $this->CI->redis->hGetAll($hashname);
        
        if($this->CI->serv->connection_info($fdinfo['fd']) == false) {
            return false;
        }
        
        if (isset($fdinfo['websocket_status'])) {            
            $this->CI->serv->push($fdinfo['fd'], json_encode($data) . "\r\n");
        } else {
            $this->CI->serv->send($fdinfo['fd'], json_encode($data) . "\r\n");
        }
    }
}

