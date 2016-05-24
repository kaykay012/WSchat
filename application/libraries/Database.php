<?php

/**
 * Description
 * 
 * @author cuikai 2016-5-10
 */
class Database {
    
    public function __construct() {
        $this->CI = & get_instance();        
    }
    
    public function init() {
        $this->CI->load->database('default');
        
        $this->CI->pdo = new PDO(
                            "{$this->CI->db->dsn}",
                            "{$this->CI->db->username}",
                            "{$this->CI->db->password}",
                            array(
                                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->CI->db->char_set}';",
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_PERSISTENT => true
                            )
                        );
    }
}
