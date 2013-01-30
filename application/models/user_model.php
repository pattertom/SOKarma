<?php
class User_model extends CI_Model {
    
    function __construct()
    {
        parent::__construct();
        $this->load->library('Reddit');
        $this->current_user = new stdClass();
    }
    
    function process_login($username, $password) {
        $result = $this->db->query("SELECT * 
                                    FROM users 
                                    WHERE reddit_username=?",
                                    array($username));
        if (!$result->row()) {
            $this->session->set_flashdata('message', '<div id="message" class="fail">Username or Password is incorrect.</div>');
            redirect('user/login');
        }
        
        $data = array(
               'username'  => $username,
               'logged_in'  => TRUE,
               'admin'  => TRUE,
               'plan' => 'free'
        );
        $this->session->set_userdata($data);
        
        $this->load_reddit($username, $password);
        $this->update_user();
    }
    
    function logout() {
        $this->current_user->reddit = null;
        $this->session->sess_destroy();
    }
    
    function load_reddit($username, $password) {
        if (!isset($this->current_user->reddit))
            $this->current_user->reddit = new Reddit($username, $password);
    }
    
    function get_reddit() {
        return $this->current_user->reddit;
    }
    
    function update_user() {
        $this->current_user->user_id = $this->user_model->get_id_for_username($this->current_user->reddit->username);
        $this->user_model->get_liked(1);
        $this->user_model->get_liked(-1);
    }
    
    function insert_user($username, $password)
    {
        $post_query = $this->db->query("SELECT * FROM users WHERE reddit_username=?", array($username));
        if ($post_query->num_rows() == 0)
        {
            $data = array(
                'reddit_username' => $username, 
                'salted_hash' => $password, 
                'signup_date' => date("Y-m-d H:i:s")
            );
            $this->db->insert('users', $data);
            $user_id = $this->db->insert_id();
            return $user_id;
        }else{
            return null;
        }
    }
    
    function get_id_for_username($username)
    {
        return $this->db->query("SELECT user_id FROM users WHERE reddit_username=?", array($username))->row()->user_id;
    }

    function get_liked($direction)
    {
        $first_pass = true;
        while ($first_pass || (isset($response->data->after) && $response->data->after != null))
        {
            $after_clause = $first_pass ? null : $response->data->after;
            $first_pass = false;
            
            if ($direction == 1) {
                $response = $this->current_user->reddit->getLiked($after_clause);
            } else {
                $response = $this->current_user->reddit->getDisliked($after_clause);
            }
            
            $continue = $this->insert_reddit_posts_data($response, $direction);
            if ($continue == 'break') {
                break;
            }
        }
    }
    
    function insert_reddit_posts_data($response, $vote_direction)
    {
        if(isset($response->data->children)){
            foreach ($response->data->children as $child)
            {
                $post = $child->data;
                $post_query = $this->db->query("SELECT * FROM reddit_posts WHERE reddit_id=?", array($post->id));
                if($post_query->num_rows() == 0){
                    $data = array(
                        'reddit_id' => $post->id ,
                        'title' => $post->title ,
                        'domain' => $post->domain,
                        'subreddit' => $post->subreddit,
                        'post_url' => $post->url,
                        'link_url' => $post->permalink
                    );
                    $this->db->insert('reddit_posts', $data);
                    $post_id = $this->db->insert_id();
                }else{
                    $post_id = $post_query->row()->reddit_post_id;
                }
                
                $vote_query = $this->db->query("SELECT * 
                                                FROM reddit_votes, reddit_posts 
                                                WHERE reddit_posts.reddit_id=? 
                                                AND reddit_posts.reddit_post_id=reddit_votes.reddit_post_id 
                                                AND reddit_votes.user_id=?", 
                                                array($post->id, $this->current_user->user_id));
                
                if ($vote_query->num_rows() == 0)
                {
                    $data = array(
                        'user_id' => $this->current_user->user_id,
                        'reddit_post_id' => $post_id,
                        'vote_direction' => $vote_direction
                    );
                    $this->db->insert('reddit_votes', $data);
                }
                else
                    return 'break';
            }
        }
    }
    
    function store_signup_attributes($user_id, $gender, $orientation, $age, $zipcode, $email)
    {
        $signup_query = $this->db->query("SELECT * FROM details WHERE user_id=?", array($user_id));
        if ($signup_query->num_rows() != 0)
            return;
        
        // GENDER INFO
        $gender_id = $gender == 'male' ? '1' : '2';
        // ORIENTATION INFO
        $orientation_mapping = array('straight' => '3', 'gay' => '4', 'bisexual' => '5');
        $orientation_id = $orientation_mapping[$orientation];
        // ZIP CODE INFO
        $coords = $this->getCoordinates($zipcode);
        $latitude = $coords['latitude'];
        $longitutde = $coords['longitude'];;
        $radius = '10';
        
        $details_data = array(
            array('user_id' => $user_id, 'key_id' => '1', 'value_id' => $gender_id, 'value_input' => null),
            array('user_id' => $user_id, 'key_id' => '2', 'value_id' => $orientation_id, 'value_input' => null),
            array('user_id' => $user_id, 'key_id' => '3', 'value_id' => null, 'value_input' => $latitude),
            array('user_id' => $user_id, 'key_id' => '4', 'value_id' => null, 'value_input' => $longitutde),
            array('user_id' => $user_id, 'key_id' => '5', 'value_id' => null, 'value_input' => $radius),
            array('user_id' => $user_id, 'key_id' => '6', 'value_id' => null, 'value_input' => $age)
        );
        $this->db->insert_batch('details', $details_data);
    }
    
    function getCoordinates($zipcode){
        $mapsApiKey = 'AIzaSyDCiA5hHDw3XD9v98TcslQTdW5UwotZHaM';
        $query = "http://maps.google.com/maps/geo?q=".urlencode($zipcode)."&output=json&key=".$mapsApiKey;
        $data = file_get_contents($query);
        if($data){
            $data = json_decode($data);
            $long = $data->Placemark[0]->Point->coordinates[0];
            $lat = $data->Placemark[0]->Point->coordinates[1];
            return array('latitude'=>$lat,'longitude'=>$long);
        }else{
            return false;
        }
    }
}