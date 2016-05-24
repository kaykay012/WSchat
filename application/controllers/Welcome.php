<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function __construct() {
        parent::__construct();
         $this->load->helper(array(
                        //'form', 
                        'cuikai')
                    );
    }
    public function index() {
        /*$this->load->helper('smiley');
        $this->load->library('table');

        $image_array = get_clickable_smileys('/static/smileys/', 'content');
        $col_array = $this->table->make_columns($image_array, 8);

        $data['smiley_table'] = $this->table->generate($col_array);*/
        
        $this->load->view('welcome_message');
    }
    
    public function smileys() {
        $this->load->helper('smiley');
        $this->load->library('table');

        $image_array = get_clickable_smileys('/static/smileys/', 'comments');
        $col_array = $this->table->make_columns($image_array, 8);

        $data['smiley_table'] = $this->table->generate($col_array);
        $this->load->view('smileys', $data);
    }
    
    public function upload(){
        $this->load->view('upload_form', array('error' => ' '));
    }
    
    public function do_upload(){
        $path = '/upload/' . date('Ym') . '/';
        $config['upload_path']      = FCPATH . $path;
        $config['allowed_types']    = 'gif|jpg|png';
        $config['file_ext_tolower'] = true;        
        $config['encrypt_name']     = true;
        
        if(!file_exists($config['upload_path'])) {
            mkdir($config['upload_path'], 0777);
        }
        
        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('userfile'))
        {
            $error = array('error' => $this->upload->display_errors());
            $data['errno'] = 1;
            $data['errmsg'] = $error['error'];
        }
        else
        {
            $res = $this->upload->data();
            $data['errno'] = 0;
            $data['errmsg'] = $path . $res['file_name'];
        }
        
        exit(json_encode($data));
    }
}
