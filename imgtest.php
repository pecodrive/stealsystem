<?php
require_once(dirname(dirname(dirname(__file__))) . '/functions.php');
require_once(dirname(dirname(__file__)) . '/wp-load.php');
date_default_timezone_set('Asia/Tokyo');

$queryList           = getSQLQuery(getPDO());
$censorList          = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList           = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList           = getFix(getPDO(), $queryList["SELECT_FIX"]); 


// $encodiedResBaseHtml = resSteal(getPDO(), "133.130.96.221", "resSteal", "http://awabi.open2ch.net/test/read.cgi/akb/1448519334/l50", $queryList["INSERT_IP_ACCESS"]);
// var_dump($encodiedResBaseHtml);
$encodiedResBaseHtml = resSteal(getPDO(), "133.130.96.221", "resSteal", "http://awabi.open2ch.net/test/read.cgi/akb/1448519334/", $queryList["INSERT_IP_ACCESS"]);
// $encodiedResBaseHtml = file_get_contents("./imghtml.html");
$resData  =
    getResArray
    (
        getDiRegex(getPDO(), "resStealRegex", "open2ch.net", $queryList["SELECT_DIREGEX"]),
        $encodiedResBaseHtml,
        "000000000000000" 
    );
$data = resBodyImgLinkProc(getPDO(), $resData, $threadSha, $regexList["ExresBodyImgLinkStealRegex"], $queryList["SELECT_RES"], $fixedList["imgSavePath"], "open2ch.net");
var_dump($data);
// $html = file_get_contents("http://hayabusa.open2ch.net/test/read.cgi/livejupiter/1449141846/l50");

// $regexBase = "/<?[div\sclass=\"imgur]*>?<a.+><img.*data-original=\"(.+)\"\s.+>[<br.>]*<\/a><?[\/div\n]*>?/u";
// $regexImg = "/<img.+data-original=\"(.+)\"\s.+>/u";

// preg_match_all($regexBase, $html, $match, PREG_SET_ORDER);
// $splitArray = [];
//
// foreach ($match as $value) {
//     $splitArray[] = preg_replace("/<\/div>/", "</div>\n", $value); 
// }
//
// $imgAllay = [];
// foreach ($splitArray as $value) {
//     preg_match_all($regexBase, $value[0], $imgUrl, PREG_SET_ORDER);
//     $imgAllay[] = $imgUrl;
// }
// // var_dump($html);
// var_dump($imgAllay);


