<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        $this->load->database('default');
    }

    public function index() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if($id<1){
            $query = $this->db->query("SELECT * FROM chat order by id desc limit 10");
        }else{
            $query = $this->db->query("SELECT * FROM chat where id < $id order by id desc limit 10");
        }
        
        foreach ($query->result_array() as $row)
        {
            $row['addtime'] = date('m-d H:i', $row['addtime']);
            $row['from'] = htmlspecialchars($row['from']);
            $row['to'] = htmlspecialchars($row['to']);
            $row['content'] = htmlspecialchars($row['content']);
            $list[] = $row;
        }

        exit(json_encode($list));
    }
    
    public function uploadpic() {
        $path = '/uploads/pic/' . date('Ym') . '/';
        $path_thumb = '/uploads/picthumb/' . date('Ym') . '/';

        $config['upload_path']      = FCPATH . $path;
        $config['allowed_types']    = 'gif|jpg|png|jpeg';
        $config['file_ext_tolower'] = true;        
        $config['encrypt_name']     = true;
        $config['max_size']         = 1024 * 5;
        $this->load->library('upload', $config);
        
        if(!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777);
        }        

        if ( ! $this->upload->do_upload('uploadpic'))
        {            
            $data['errno'] = 1;
            $data['errmsg'] = $this->upload->display_errors() . json_encode($_FILES['uploadpic']);
        }
        else
        {
            $source_image = $this->upload->data();
            

            // 生成缩略图
            $config_thumb['image_library']  = 'gd2';
            $config_thumb['source_image']   = $source_image['full_path'];
            $config_thumb['quality']        = '90%';
            $config_thumb['create_thumb']   = TRUE;
            $config_thumb['thumb_marker']   = '_thumb';
            $config_thumb['maintain_ratio'] = TRUE;
            $config_thumb['width']          = 500;
            $config_thumb['height']         = 500;
            
            $this->load->library('image_lib', $config_thumb);
            $this->image_lib->resize();

            $thumb_name_suffix = isset($config_thumb['thumb_marker']) ? $config_thumb['thumb_marker'] : '_thumb';

            $data['errno'] = 0;
            $data['imgurl'] = $path . $source_image['raw_name'] . $thumb_name_suffix . $source_image['file_ext'];
        }
        
        exit(json_encode($data));
    }
}
