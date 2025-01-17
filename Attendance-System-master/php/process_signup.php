<?php include 'defines.php'; ?>
<?php

/**  Validation  ***/
foreach($_POST as $p)
  if(empty($p) || !isset($p))
    respond("error","empty");

if($_POST['password'] != $_POST['password2'])
  respond("error","mismatch");
if(strlen($_POST['password']) < 6)
  respond("error","small");

$pass = hashPass($_POST['password']);
$email = strtolower(sqlReady($_POST['email']));
$name = sqlReady($_POST['name']);
$phone = sqlReady($_POST['phone']);

/****   Regex    ******/
if(verify(EMAIL,$email) === false) respond("error","email");
if(verify(PHONE,$phone) === false) respond("error","phone");
if(verify(NAME,$name) === false) respond("error","name");

/*** Search   ****/
$con = connectTo();
$exists = $con->query("select * from `attendance`.`teacher` where email = '$email'");
if($exists && $con->affected_rows) {
  $con->close();
  respond("error","exists");
}

/*****   Insert   ******/
$addTeacher = $con->query("insert into `attendance`.`teacher` (`name`, `email`, `phone`, `password`) values ('$name', '$email', '$phone', '$pass')");
if(!$addTeacher && $con->errno()) {
  $con->close();
  die(json_encode(array("error"=>"db_error","code"=>$con->errno(),"msg"=>$con->error())));
}

/*****  Respond  ******/
$con->close();
respond("error","none");
?>