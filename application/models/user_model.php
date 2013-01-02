<?php
class User_model extends CI_Model {
    
    function __construct()
    {
        parent::__construct();
    }
    
    function save_user($username, $password, $modHash)
    {
        $post_query = $this->db->query("SELECT * FROM users WHERE reddit_username=?", array($username));
        if ($post_query->num_rows() == 0)
        {
            $data = array('reddit_username' => $username , 'salted_hash' => $password , 'mod_hash' => $modHash , 'signup_date' => date("Y-m-d H:i:s"));
            $this->db->insert('users', $data);
            $user_id = $this->db->insert_id();
        }else{
            $user_id = $post_query->row()->user_id;
        }

        return $user_id;
    }

    function get_liked()
    {
        $response = $this->current_user->reddit->getLiked(null);
        $this->insert_reddit_posts_data($response, 1);
        
        while (isset($response->data->after) && $response->data->after != null)
        {
            $response = $this->current_user->reddit->getLiked($response->data->after);
            $this->insert_reddit_posts_data($response, 1);
        }
    }

    function get_disliked()
    {
        $response = $this->current_user->reddit->getDisliked(null);
        $this->insert_reddit_posts_data($response, -1);
        
        while (isset($response->data->after) && $response->data->after != null)
        {
            $response = $this->current_user->reddit->getDisliked($response->data->after);
            $this->insert_reddit_posts_data($response, 1);
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
                    $data = array('reddit_id' => $post->id , 'title' => $post->title , 'domain' => $post->domain, 
                    'subreddit' => $post->subreddit, 'post_url' => $post->url, 'link_url' => $post->permalink);
                    $this->db->insert('reddit_posts', $data);
                    $post_id = $this->db->insert_id();
                }else{
                    $post_id = $post_query->row()->reddit_post_id;
                }
                
                $vote_query = $this->db->query("SELECT * FROM reddit_votes, reddit_posts WHERE 
                    reddit_posts.reddit_id=? AND reddit_posts.reddit_post_id=reddit_votes.reddit_post_id 
                    AND reddit_votes.user_id=?", array($post->id, $this->current_user->user_id));
                
                if ($vote_query->num_rows() == 0)
                {
                    echo 'unique';
                    $data = array('user_id' => $this->current_user->user_id , 'reddit_post_id' => $post_id , 'vote_direction' => $vote_direction);
                    $this->db->insert('reddit_votes', $data);
                }
            }
        }
    }
}