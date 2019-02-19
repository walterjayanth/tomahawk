<?php
if (isset($_POST['submit'])){
  $OP['room_name']=$_POST['room'];
  $OP['selected_dates']=$_POST['seldates'];
  $OP['total_rate']=$_POST['total'];
  $OP['guest_firstname']=$_POST['fname'];
  $OP['guest_lastname']=$_POST['lname'];
  $OP['guest_email']=$_POST['email'];

  $json=json_encode($OP);

  echo "<pre>$json</pre>";
}
?>