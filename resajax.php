<?php

session_start();
if (!isset($_SESSION["USERID"])) {
    header("Location: logout.php");
    exit;
}

require_once(dirname(dirname(dirname(__file__))) . '/functions.php');
require_once(dirname(dirname(__file__)) . '/wp-load.php');

$request        = json_decode( file_get_contents( "php://input" ) , true );
if(!wp_verify_nonce($request["nonce"])){
    die();
}

date_default_timezone_set('Asia/Tokyo');

$queryList               = getSQLQuery(getPDO());
$censorList              = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList               = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList               = getFix(getPDO(), $queryList["SELECT_FIX"]); 
$resData                 = getResData(getPDO(), $request["thread_sha"], $queryList["SELECT_RES"]); 
$sortedResData           = sortResByAnkaer($resData);
$threadData              = getThreadDataBySha(getPDO(), $request["thread_sha"], $queryList["SELECT_THREAD_BY_THREADSHA"]); 
$menuData                = getMenuDefaultName(getPDO(), $threadData["menu_id"], $queryList["SELECT_MENU_FOR_MENUID"]);
$html                    = "";
$resSha                  = null;
$name                    = $menuData ? $menuData : "名無しさん";
$countOfSortedResData    = count($sortedResData);

for ($k=0; $k < $countOfSortedResData; $k++) {
    $resMasterID = $sortedResData[0]["res_id"];
    $resSha[] = $sortedResData[$k]["res_sha"];

    $html .= "<span class=\"{$sortedResData[$k]["thread_sha"]} {$sortedResData[$k]["res_sha"]} rename\">";
    $html .= "<span>{$sortedResData[$k]["res_no"]} : </span>";
    $html .= "<span><input type=\"text\" name={$sortedResData[$k]["res_sha"]} size=40 value=\"{$name}\"> : </span>";
    $html .= "<span>{$sortedResData[$k]["res_date"]}</span>";
    $html .= "<span>{$sortedResData[$k]["res_clock"]}</span>";
    if($sortedResData[$k]["res_id"] === $resMasterID){
        $html .= "<span class=\"resmaster\">{$sortedResData[$k]["res_id"]}</span>";
    }else{
        $html .= "<span>{$sortedResData[$k]["res_id"]}</span>";
    }
    $html .= "</span>";

    $html .= "<span class=\"censor\">";
    $html .= "<span class=\"inputtool\">";
    $html .= "<span class=\"{$sortedResData[$k]["res_sha"]} word\"><input type=\"text\" name={$sortedResData[$k]["res_sha"]}word size=20></span>";
    $html .= "<span class=\"{$sortedResData[$k]["res_sha"]} becensor\"><input type=\"text\" name={$sortedResData[$k]["res_sha"]}becensor size=20></span>";
    $html .= "<span  class=\"{$sortedResData[$k]["thread_sha"]} {$sortedResData[$k]["res_sha"]} swich\"><input type=\"radio\" name={$sortedResData[$k]["res_sha"]}swich value=0 checked>非挿入</span>";
    $html .= "<span  class=\"{$sortedResData[$k]["thread_sha"]} {$sortedResData[$k]["res_sha"]} swich\"><input type=\"radio\" name={$sortedResData[$k]["res_sha"]}swich value=1>挿入</span>";
    $html .= "<span><input class=\"{$sortedResData[$k]["thread_sha"]} {$sortedResData[$k]["res_sha"]} manualcensor\" type=\"button\" name={$sortedResData[$k]["res_sha"]}swich value=\"修正\"></span>";
    // $html .= "<span  class=\"{$sortedResData[$k]["thread_sha"]} {$sortedResData[$k]["res_sha"]} manualcensor\">修正</span>";
    $html .= "</span>";
    $html .= "</span>";

    $html .= "<span id=\"{$sortedResData[$k]["res_sha"]}\" class=\"{$sortedResData[$k]["thread_sha"]} {$sortedResData[$k]["res_sha"]} responce\">"; 
    $imgData = unserialize($sortedResData[$k]["res_imgtag"]);
    // var_dump($imgData);
    if(!empty($imgData)){
        foreach ($imgData as $value) {
            $reImgLink = imgurCheck($value["img_link"], $value["extention"]);
            $html .= "<img src=\"{$reImgLink}\">";
        } 
        $body = "{$sortedResData[$k]["res_rowbody"]}"; 
    }else{
        $body = "{$sortedResData[$k]["res_body"]}"; 
    }
    $body = youtube($body);
    if($sortedResData[$k]["censored"]){
        $html .= "<font style=\"font-weight:900;color:#ff4444;\">{$body}</font>";
    }else{
        $html .= $body;
    }
    $html .= "</span>"; 
}
$html .= "<div class=\"threadbotton {$request["thread_sha"]}\">"; 
$html .= "<p>記事を作る</p></div>"; 
$html .= "<div class=\"html {$request["thread_sha"]}\"></div>"; 

$args = array(
    'type'                     => 'post',
    'taxonomy'                 => 'category',
    'hide_empty'               => 0,
); 
$cats = get_categories($args);

$category = "<div class=\"category\">\n";
for ($i=0; $i < count($cats); $i++) {
    $category .= "<span class=\"{$request["thread_sha"]} category\">{$cats[$i]->name}</span>\n";
}
$category .= "</div>";

$jsonData = json_encode( array($html, $category, $k, $resSha) );
header( "Content-Type: application/json; X-Content-Type-Options: nosniff; charset=utf-8" );
echo $jsonData;
unset($html);
die();
