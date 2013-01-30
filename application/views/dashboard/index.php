<?php
$this->load->view('header', array('title'=>'Login'));
echo $this->session->flashdata('message');
?>
<div>USER PROFILE</div>
<?
$this->load->view('footer');
?>