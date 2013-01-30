<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        // $this->load->model('dashboard_model');
    }

    public function index()
    {
        $this->load->view('dashboard/index');
    }
}

/* End of file dashboard_controller.php */
/* Location: ./application/controllers/dashboard_controller.php */