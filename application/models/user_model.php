<?php
class User_model extends CI_Model {
    
    function validate_login($username, $password)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://www.reddit.com/api/login/tompatterson");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "user=$username&passwd=$password&api_type=json");
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $result = curl_exec($ch);
        curl_close ($ch);
        
        // parse response
        $json = json_decode($result);
        if (count($json->json->errors) == 0) {
            $cookie =  $json->json->data->cookie;
            $modhash =  $json->json->data->modhash;
            echo ("COOKIE: $cookie<br /><br />MODHASH: $modhash");
        }
        else
            echo ("bad credentials");
    }
}