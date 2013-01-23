<?php
$this->load->view('header', array('title'=>'Signup'));
echo $this->session->flashdata('message');

echo form_open_multipart('/user/signup');
?>
<input type="hidden" name="user_id" value="<?= $id ?>">
<input type="hidden" name="page" value="<?= $page ?>">
<div>
    <label for="gender">Gender</label>
    <select name = "gender">
      <option value="male">Male</option>
      <option value="female">Female</option>
    </select>
</div>
<div>
    <label for="orientation">Orientation</label>
    <select name="orientation">
      <option value="straight">Straight</option>
      <option value="gay">Gay</option>
      <option value="bisexual">Bisexual</option>
    </select>
</div>
<div>
    <label for="age">Age</label>
    <input id="age" name="age" type="text" />
</div>
<div>
    <label for="zipcode">Zipcode</label>
    <input id="zipcode" name="zipcode" type="text" />
</div>
<div>
    <label for="email">Email</label>
    <input id="email" name="email" type="text" />
</div>

<div>
    <input type="submit" value="submit">
</div>

<?php
echo form_close();
$this->load->view('footer');
?>