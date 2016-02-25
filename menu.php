<?php
session_start();
if (!isset($_SESSION["USERID"])) {
    header("Location: logout.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>StealSystem</title>
<meta name="viewport" content="width=device-width">
<link rel="stylesheet" type="text/css" href="style.css"> 
</head>
<body>
<div class="menu"><a href="./main.php">すべて</a></div>
<div class="menu"><a href="./news.php">ニュース系</a></div>
<div class="menu"><a href="./jv.php">なんｊ・ニュー速vip</a></div>
<div class="menu"><a href="./suspend.php">保留</a></div>
<div class="menu"><a href="./tested.php">テスト</a></div>
</body>
</html>
