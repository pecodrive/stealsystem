<?php

session_start();
if (!isset($_SESSION["USERID"])) {
    header("Location: logout.php");
    exit;
}

// require_once(dirname(__file__) . '/functions.php');
require_once(dirname(dirname(dirname(__file__))) . '/functions.php');
require_once(dirname(dirname(__file__)) . '/wp-load.php');

$threadSha  = json_decode( file_get_contents( "php://input" ) , true );
if(!wp_verify_nonce($threadSha["nonce"])){
    die();
}

date_default_timezone_set('Asia/Tokyo');

$queryList  = getSQLQuery(getPDO());
$censorList = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList  = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList  = getFix(getPDO(), $queryList["SELECT_FIX"]); 

updeateToSuspendFlag(getPDO(), $threadSha["thread_sha"], $queryList["UPDATE_THREAD_SUSPEND_FLAG"]);

$jsonData = json_encode($threadSha);
header( "Content-Type: text/html; X-Content-Type-Options: nosniff; charset=utf-8" );
echo $jsonData;
die();
