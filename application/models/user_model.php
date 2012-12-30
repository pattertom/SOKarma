<?php
class User_model extends CI_Model {
    
    function __construct()
    {
        parent::__construct();
        require_once(APPPATH.'libraries/reddit.php');
    }
    
    function record_actions($username, $password)
    {
        $reddit = new reddit($username, $password);
        
        $response = $reddit->getLiked();
        $this->insert_reddit_posts_data($response, $username, 1);
        $response = $reddit->getDisliked();
        $this->insert_reddit_posts_data($response, $username, -1);
    }
    
    function insert_reddit_posts_data($response, $username, $vote_direction)
    {
        foreach ($response->data->children as $post)
        {
            $post = $post->data;
            $post_query = $this->db->query("SELECT * FROM reddit_posts WHERE reddit_id=?", array($post->id));
            if ($post_query->num_rows() == 0)
            {
                $data = array('reddit_id' => $post->id , 'title' => $post->title , 'domain' => $post->domain, 
                'subreddit' => $post->subreddit, 'post_url' => $post->url, 'link_url' => $post->permalink);
                $this->db->insert('reddit_posts', $data);
                $post_id = $this->db->insert_id();
            }
            else
                $post_id = $post_query->row()->reddit_post_id;
            
            $user_id_query = $this->db->query("SELECT user_id FROM users WHERE reddit_username=?", array($username));
            $user_id = $user_id_query->row()->user_id;
            
            $vote_query = $this->db->query("SELECT * FROM reddit_votes, reddit_posts WHERE 
                reddit_posts.reddit_id=? AND reddit_posts.reddit_post_id=reddit_votes.reddit_post_id 
                AND reddit_votes.user_id=?", array($post->id, $user_id));
            if ($vote_query->num_rows() == 0)
            {
                $data = array('user_id' => $user_id , 'reddit_post_id' => $post_id , 'vote_direction' => $vote_direction);
                $this->db->insert('reddit_votes', $data);
            }
        }
    }
}