<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function index()
    {
        redirect('user/login');
    }
    
    public function create()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        
        if ($username && $password)
            $this->user_model->validate_login($username, $password);
        else {
            $this->session->set_flashdata('message', '<div class="fail">You must supply a username and password.</div>');
            redirect('user/login');
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