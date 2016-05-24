<?php

/**
 * Description
 * 
 * @author cuikai 2016-5-6
 */
function say() {
    echo 'hi, my name is cuikai';
}

/**
 * [printffdlist description]
 * @return [type] [description]
 */
function printffdlist() {
    $fdlist = $CI->serv->connection_list();
    print_r($fdlist);
}

/**
 * 打印
 * @param type $param
 */
function P($param) {
    echo "<pre>";
    print_r($param);
}

/**
 * 添加客户端连接
 * @param type $fd
 * @return boolean
 */
function lpush_fdlist($fd) {
    $CI = get_instance();

    $CI->redis->lrem($CI->swoole_listname, $CI->config->item('serv_number') . $fd);
    $CI->redis->lpush($CI->swoole_listname, $CI->config->item('serv_number') . $fd);

    $fdinfo = $CI->serv->connection_info($fd);
    $fdinfo['fd'] = $fd;    

    $hashname = $CI->swoole_hashname . $CI->config->item('serv_number') . $fd;

    $CI->redis->del($hashname);
    return $CI->redis->hMset($hashname, $fdinfo);
}

/**
 * 获取所有客户端信息
 * @param type $start
 * @param type $size
 * @param type $fd 如果 >0 则忽略此客户端
 * @return type
 */
function get_fdlist($start = 0, $size = 10, $fd = NULL) {
    $CI = get_instance();

    $connection_list = $CI->redis->lrange($CI->swoole_listname, $start, $size);
    $fdlist = array();
    foreach ($connection_list as $fdval) {
        if ($CI->config->item('serv_number') . $fd == $fdval) {
            continue;
        }

        $hashname = $CI->swoole_hashname . $fdval;
        $fdinfo = $CI->redis->hGetAll($hashname);
        $fdlist[] = array('fd' => $fdval, 'name' => htmlspecialchars($fdinfo['name']));
    }

    return $fdlist;
}

/**
 * 设置客户端信息
 * @param type $fd
 * @param type $key
 * @param type $val
 * @return boolean
 */
function set_fdinfo($fd, $key, $val) {    
    $CI = get_instance();

    if(empty($val)) {
        return false;
    }

    $hashname = $CI->swoole_hashname . $CI->config->item('serv_number') . $fd;
    
    return $CI->redis->hSetNx($hashname, $key, $val);
}

/**
 * 获取客户端信息
 * @param type $fd
 */
function get_fdinfo($fd) {
    $CI = get_instance();

    $hashname = $CI->swoole_hashname . $CI->config->item('serv_number') . $fd;
    $fdinfo = $CI->redis->hGetAll($hashname);

    if (!$fdinfo) {
        return '';
    }
    
    $arr['fd']      = $CI->config->item('serv_number') . $fd;
    $arr['name']    = htmlspecialchars(isset($fdinfo['name']) ? $fdinfo['name'] : '');
    
    return $arr;
}

/**
 * 清除客户端连接信息
 * @param type $fd
 * @return boolean
 */
function del_fdinfo($fd) {
    $CI = get_instance();

    $CI->redis->lrem($CI->swoole_listname, $CI->config->item('serv_number') . $fd);

    $hashname = $CI->swoole_hashname . $CI->config->item('serv_number') . $fd;

    return $CI->redis->del($hashname);
}

/**
 * 发送消息给所有客户端
 * @param type $data 数据体
 * @param type $fd 是否包括发送者
 * @return boolean
 */
function send_all($data, $fd = NULL) {
    $CI = get_instance();

    $connection_list = $CI->redis->lrange($CI->swoole_listname, 0, -1);
    $fdvals = array();
    foreach ($connection_list as $fdval) {
        if ($CI->config->item('serv_number') . $fd == $fdval) {
            continue;
        }
        
        // 判断是否需要转发消息
        if ($data !== NULL && is_forward($fdval)) {
            $fdvals[] = $fdval;
            continue;
        }

        $CI->send($fdval, $data);
    }
    
    if(!empty($fdvals)) {
        send_forward($data, $fdvals);
    }
    
    return true;
}

/**
 * 判断是否需要转发消息
 * @param type $to_fd
 */
function is_forward($to_fd) {
    $CI = get_instance();

    if (substr($to_fd, 0, strlen($CI->config->item('serv_number'))) != $CI->config->item('serv_number')) {
        return true;
    }

    return false;
}

/**
 * 把消息转发到另一台服务器
 * @param type $data
 */
function send_forward($data, $to_fd) {
    $CI = get_instance();

    $forward_host = $CI->config->item('forward_host');
    $forward_port = $CI->config->item('forward_port');

    if ($CI->client === NULL) {
        $CI->client = new swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
        $CI->client->connect($forward_host, $forward_port);        
    }

    $forward_data['forward'] = 1;    
    $forward_data['to_fd'] = $to_fd;
    $forward_data['data'] = $data;
    
    echo "send_forward:" . json_encode($forward_data) . "\n";
    
    return $CI->client->send(json_encode($forward_data) . "\r\n");
}
