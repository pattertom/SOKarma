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
        $this->user_model->load_reddit($username, $password);
        
        $message = $this->user_model->get_reddit()->errorMessage;
        if(!$this->user_model->get_reddit()->loginSuccess){
            $this->session->set_flashdata('message', '<div class="fail">'.$message.'</div>');
            redirect('user/login');
        }else{
            $new_user = $this->user_model->insert_user($username, $password);

            if ($new_user)
                redirect('user/signup?page=2');
        }
    }
    
    public function login()
    {
        $this->load->helper('form');
        $this->load->view('user/login');
    }
    
    public function process_login()
    {
        $username = $this->input->post('username');
        $password  = $this->input->post('password');
        $this->user_model->process_login($username, $password);
        redirect('dashboard/index');
    }
    
    public function logout()
    {
        $this->user_model->logout();
        redirect('user/login');
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
    
    
    public function script()
    {
        $query = $this->db->query("SELECT * FROM reddit_votes WHERE user_id=?", array('1'));
        $counter = 0;
        foreach ($query->result() as $row) {
            if ($counter % 4 == 0) {
                echo $counter.'<br />';
                $data = array(
                    'user_id' => 6,
                    'reddit_post_id' => $row->reddit_post_id,
                    'vote_direction' => $row->vote_direction
                );
                $this->db->insert('reddit_votes', $data);
            }
            $counter++;
        }
    }
}

/* End of file user_controller.php */
/* Location: ./application/controllers/user_controller.php */