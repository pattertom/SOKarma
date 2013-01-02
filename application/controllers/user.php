<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

    public $current_user;

    function __construct()
    {
        parent::__construct();
        $this->load->library('Reddit');
        $this->load->model('user_model');
        $this->current_user = new stdClass();
        $this->current_user->reddit = null;
    }

    public function index()
    {
        redirect('user/login');
    }
    
    public function create()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        if (!$this->current_user->reddit)
            $this->current_user->reddit = new Reddit($username, $password);
        $message = $this->current_user->reddit->errorMessage;
        
        if(!$this->current_user->reddit->loginSuccess){
            $this->session->set_flashdata('message', '<div class="fail">'.$message.'</div>');
            redirect('user/login');
        }else{
            $this->current_user->user_id = $this->user_model->save_user($username, $password, $this->current_user->reddit->getModHash());
            $this->user_model->get_liked();
            $this->user_model->get_disliked();
        }
    }

    public function login()
    {
        $this->load->helper('form');
        $this->load->view('user/login');
    }
    
}

/* End of file user_controller.php */
/* Location: ./application/controllers/user_controller.php */