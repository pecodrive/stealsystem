<?php

session_start();
if (!isset($_SESSION["USERID"])) {
    header("Location: logout.php");
    exit;
}

require_once(dirname(dirname(dirname(__file__))) . '/functions.php');
// require_once(dirname(__file__) . '/functions.php');
require_once(dirname(dirname(__file__)) . '/wp-load.php');

$requestData = json_decode( file_get_contents( "php://input" ) , true );
if(!wp_verify_nonce($requestData["nonce"])){
    die();
}

date_default_timezone_set('Asia/Tokyo');

$queryList               = getSQLQuery(getPDO());
$censorList              = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList               = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList               = getFix(getPDO(), $queryList["SELECT_FIX"]); 

$censoredResBody = resBodyCensorProc
    (
        getPDO(),
        $requestData["censor_word"],
        $requestData["be_censor"],
        $requestData["res_sha"],
        $queryList["SELECT_RES_BODY_FOR_MANUAL"]
    );

resBodyManualCensorUpdert
(
    getPDO(),
    $censoredResBody, 
    $requestData["res_sha"],
    $queryList["UPDATE_RES_BODY_FOR_MANUAL"]
);

if((int)$requestData["is_insert"] === 1){
    insertCensorWord(getPDO(), $requestData["censor_word"], $requestData["be_censor"], $queryList["INSERT_CENSOR"]);
}

$jsonData = json_encode($censoredResBody);
header( "Content-Type: application/json; X-Content-Type-Options: nosniff; charset=utf-8" );
echo $jsonData;
unset($html);
die();



