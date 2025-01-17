<?php
  session_start();
  include 'node_class.php';
  $code = $_POST['code'];
  $section = $_POST['section'];
  $year = $_POST['year'];

  //check and delete class
  $classNode = new Node;
  $node = $classNode->retrieveObject($code,$section,$year) or respond("error","not_found");
  if($node->getTeacherID() == $_SESSION['teacher_id']) {
    if($node->deleteNode()) {
      updateSession($_SESSION['email']);
      respond("error","none");
    }
    respond("error","failure");
  }
  respond("error","illegal");
?>
