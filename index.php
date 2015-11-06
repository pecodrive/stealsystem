<?php
session_start();
date_default_timezone_set('Asia/Tokyo');
require_once(dirname(__file__) . '/functions.php');

$logingData = getLoggingData(getPDO());

if(isset($_POST["login"])){
    $passSha = sha1($_POST["password"]);
    if($logingData["user"] === $_POST["user"] && sha1($_POST["password"]) === $logingData["password"]){
        session_regenerate_id(true);
        $_SESSION["USERID"] = sha1($logingData["user"].$logingData["password"]);
        header("Location: main.php");
        exit;
    } 
} 
?>

<!doctype html>
<html>
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title>銀河英雄伝説</title>
  </head>
  <body>
  <form id="loginForm" name="loginForm" action="" method="POST">
  <fieldset>
  <input type="text" id="user" name="user" value="">
  <br>
  <input type="password" id="password" name="password" value="">
  <br>
  <input type="submit" id="login" name="login" value="???">
  </fieldset>
  </form>
  </body>
</html>
