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
            $new_user = $this->user_model->insert_user($username, $password, $this->current_user->reddit->getModHash());
            $this->current_user->user_id = $this->user_model->get_id_for_username($username);
            $this->user_model->get_liked();
            $this->user_model->get_disliked();
            if ($new_user)
                redirect('user/signup?page=2');
        }
    }
    
    public function login()
    {
        $this->load->helper('form');
        $this->load->view('user/login');
    }
    
    public function signup($id=0)
    {
        $page = $this->input->post('page');
        switch ($page) {
            case null:
                $data['page'] = 2;
                $data['id'] = $id;
                
                $this->load->helper('form');
                $this->load->view('user/signup', $data);
                break;
            case 2:
                $this->user_model->store_signup_attributes(
                    $this->input->post('user_id'),
                    $this->input->post('gender'),
                    $this->input->post('orientation'),
                    $this->input->post('age'),
                    $this->input->post('zipcode'),
                    $this->input->post('email')
                );
                
                $this->load->view('user/signup_complete');
                break;
        }
    }
    
}

/* End of file user_controller.php */
/* Location: ./application/controllers/user_controller.php */