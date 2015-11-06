<?php
require_once(dirname(__file__) . '/functions.php');
date_default_timezone_set('Asia/Tokyo');

$threadSha  = json_decode( file_get_contents( "php://input" ) , true );
$queryList  = getSQLQuery(getPDO());
$censorList = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList  = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList  = getFix(getPDO(), $queryList["SELECT_FIX"]); 

updeateToDeleatFlag(getPDO(), $threadSha["thread_sha"], $queryList["UPDATE_THREAD_DELEATE_FLAG"]);
updeateToDeleatFlag(getPDO(), $threadSha["thread_sha"], $queryList["UPDATE_RES_DELEATE_FLAG"]);

$jsonData = json_encode($threadSha);
header( "Content-Type: text/html; X-Content-Type-Options: nosniff; charset=utf-8" );
echo $jsonData;
die();
