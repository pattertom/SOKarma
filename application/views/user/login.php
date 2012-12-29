<?php
$this->load->view('header', array('title'=>'Login'));
echo $this->session->flashdata('message');

echo form_open_multipart('/user/create');
?>
<div>
    <label for="username">Username</label>
    <input id="username" name="username" type="text" />
    <span id="username_info">Username</span>
</div>
<div>
    <label for="new_password">Password</label>
    <input id="new_password" name="password" type="password" />
    <span id="new_password_info">Input a password</span>
</div>

<div>
    <input type="submit" value="submit">
</div>

<?php
echo form_close();
$this->load->view('footer');
?>