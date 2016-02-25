<?php

require_once(dirname(__file__) . '/functions.php');

date_default_timezone_set('Asia/Tokyo');

$queryList  = getSQLQuery(getPDO());
$censorList = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList  = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList  = getFix(getPDO(), $queryList["SELECT_FIX"]); 
$threadShaList = getThreadDataForDelete(getPDO(), $queryList["SELECT_DELETE_THREAD"]);
foreach($threadShaList as $value){
    updeateToDeleatFlag(getPDO(), $value["thread_sha"], $queryList["UPDATE_THREAD_DELEATE_FLAG"]);
}
$threadDeleteList = getThreadDataForDelete(getPDO(), $queryList["SELECT_FINAL_DELETE_THREAD"]);
foreach($threadDeleteList as $value){
    updeateToDeleatFlag(getPDO(), $value["thread_sha"], $queryList["UPDATE_RES_DELEATE_FLAG"]);
}

killDelete(getPDO(), $queryList["KILL_DELETE"]);
