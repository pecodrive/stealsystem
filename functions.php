<?php

$dbname = "mysql:dbname=matome;host=localhost";
$dbuser = "matomeuser";
$dbpass = "iop26yui";

function procLockCheck($dbHandle){
    $proclock = $dbHandle->query("SELECT is_proclock FROM proclock;");
    $proclockData = $proclock->fetchAll();
    $is_proclock = [];
    foreach ($proclockData as $value) {
        $is_proclock = $value["is_proclock"];
    }
    // var_dump($is_proclock);

    if($is_proclock){
        die("Before the process was not yet finished");
    }

    $dbHandle = null;
}
function procLocking($dbHandle){
    $dbHandle->query("UPDATE matome.proclock SET is_proclock = 1 WHERE id = 0;"); 
    $dbHandle = null;
}

function procUnLocking($dbHandle){
    $dbHandle->query("UPDATE matome.proclock SET is_proclock = 0 WHERE id = 0;"); 
    $dbHandle = null;
}

function procRecode($dbHandle, $menu_id, $menu_title, $proc_time, $_kind, $_memory, $prepareSt){
    $prepare = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":menu_id", $menuId);
    $prepare->bindParam(":menu_title", $menuTitle);
    $prepare->bindParam(":proc_time", $procTime);
    $prepare->bindParam(":kind", $kind);
    $prepare->bindParam(":memory", $_memory);
    $prepare->bindParam(":datetime", $datetime);

    $menuId       = $menu_id;
    $menuTitle    = $menu_title;
    $procTime     = $proc_time;
    $kind         = $_kind;
    $memory       = $_memory;
    $datetime     = date("Y-m-d H:i:s");

    $prepare->execute();
    $dbHandle = null;
}

function memoryGetUse($ankaer){
    global $tags;
    $tags[$ankaer] = $ankaer . " (get) : " . memory_get_usage()/1000/1000 . " MB";
}

function memoryPeakUse($ankaer){
    global $tags;
    $tags[$ankaer] = $ankaer . " (peak) : " . memory_get_peak_usage(true)/1000/1000 . " MB";
}

function timeTest($point){
    global $times;
    $times[$point] = round(microtime(true), 5);
}

function timeView(){
    global $times;
    $procTime = [];
    $i = 0;
    foreach ($times as $key => $value) {
        $pres[$i] = $value;
        $hash[$i] = $key;
        if($i > 0){
            $diff = round(($pres[$i] - $pres[$i - 1]), 5);
            if($diff > 0.0001){
                array_push($procTime, "{$hash[$i - 1]} > {$hash[$i]} : {$diff} second");
            }
        }
        $i++;
    }
    $endToStart = $times['ProcEnd'] - $times['ProcStart'];
    array_push($procTime, "ProcTime startTime > endTime {$endToStart} second"); 
    return $endToStart;
}
function errorProc($dbHandle,$em,$prepareSt){
    $prepare = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":errormsg", $errormsg);
    $prepare->bindParam(":datetime", $datetime);

    $errormsg      = $em;
    $datetime      = date("Y-m-d H:i:s");

    $prepare->execute();
    $dbHandle = null;
}
function getError($dbHandle, $prepareSt){
    $list = [];
    foreach ($dbHandle->query($prepareSt) as $value) {
        $list["errormsg"] = $value["errormsg"];
        $list["datetime"] = $value["datetime"];
    }

    $dbHandle = null;
    return $list;
} 
function getHost($menuUrl){
    preg_match("/http:\/\/([a-z0-9]+\.open2ch\.net)\//",$menuUrl, $match);
    return $match[1];
}
function menuSteal($dbHandle, $ip, $access, $menuUrl, $prepareSt, $ua, $host){
    try {
        $opc = array(
            "socket"=>array("bindto"=>"{$ip}:0"),
            "http"=>array(
                "ignore_errors"=>true,
                "method"=>"GET",
                "header"=>
                "Host:{$host}\r\n"
                ."User-Agent:{$ua}\r\n"
            )
        );
        $context = stream_context_create($opc);
        $html   = file_get_contents($menuUrl);
        $encode = mb_detect_encoding($html);
        if($encode === "UTF-8"){
            $encodiedMenuListBaseHtml = $html;
        }else{
            $encodiedMenuListBaseHtml = mb_convert_encoding($html, "UTF-8", "SJIS");
        }

        if(!$html){
            $em = "Do not steal to menu!";
            throw new Exception($em);
        }
    } catch (Exception $e) {
        errorProc($e->getMessage());
        echo $e->getMessage(); 
    }
    ipAccess($dbHandle, $ip, $access, $menuUrl, $prepareSt);
    return $encodiedMenuListBaseHtml;
}

function threadSteal($dbHandle, $ip, $access, $threadListUrl,  $prepareSt, $ua, $host){
    try {
        $opc = array(
            "socket"=>array("bindto"=>"{$ip}:0"),
            "http"=>array(
                "ignore_errors"=>true,
                "method"=>"GET",
                "header"=>
                "Host:{$host}\r\n"
                ."User-Agent:{$ua}\r\n"
            )
        );
        $context = stream_context_create($opc);
        $html = file_get_contents($threadListUrl, false, $context);
        $encode = mb_detect_encoding($html);
        if($encode === "UTF-8"){
            $encodiedThreadBaseHtml = $html;
        }else{
            $encodiedThreadBaseHtml = mb_convert_encoding($html, "UTF-8", "SJIS,UTF-8");
        }
        if(!$html){
            $em = "Do not steal to thread!";
            throw new Exception($em);
        }
    } catch (Exception $e) {
        errorProc($e->getMessage());
        echo $e->getMessage(); 
    }
    ipAccess($dbHandle, $ip, $access, $threadListUrl, $prepareSt);
    return $encodiedThreadBaseHtml;
}

function resSteal($dbHandle, $ip, $access, $threadUrl,  $prepareSt, $ua, $host){
    try {
        $opc = array(
            "socket"=>array("bindto"=>"{$ip}:0"),
            "http"=>array(
                "ignore_errors"=>true,
                "method"=>"GET",
                "header"=>
                "Host:{$host}\r\n"
                ."User-Agent:{$ua}\r\n"
            )
        );
        $context = stream_context_create($opc);
        $resBaseHtml = file_get_contents($threadUrl,false,$context);
        // var_dump($http_response_header[8].$http_response_header[5]);
        $encode = mb_detect_encoding($resBaseHtml);
        if($encode === "UTF-8"){
            $encodiedResBaseHtml = $resBaseHtml;
        }else{
            $encodiedResBaseHtml = mb_convert_encoding($resBaseHtml, "UTF-8", "SJIS,UTF-8");
        }
        if(!$resBaseHtml){
            $em = "Do not steal to Res!";
            throw new Exception($em);
        }
    } catch (Exception $e) {
        errorProc($e->getMessage());
        echo $e->getMessage(); 
    }
    ipAccess($dbHandle, $ip, $access, $threadUrl, $prepareSt);
    return $encodiedResBaseHtml;
}

function menuDataPregMatch($encodiedMenuBaseHtml, $menuStealRegex){
    try {
        $menuStealBool = preg_match_all($menuStealRegex, $encodiedMenuBaseHtml, $menuData, PREG_SET_ORDER);
        if(!$menuStealBool){
            throw new Exception("Do not preg_match_all to menu!");
        }
    } catch (Exception $e){
        echo $e->getMessage();
    }
    return $menuData;
}

function menuReArray($tempMenuData){
    $reArrayedMenuData = [];
    foreach ($tempMenuData as $key => $value) {
        $reArrayedMenuData[$key]["menu_url"]        = $tempMenuData[$key][1];
        $reArrayedMenuData[$key]["menu_title"]      = $tempMenuData[$key][2];
        //DateTime will set by function of DataBase.
    }
    return $reArrayedMenuData;
}

function threadDataPregMatch($encodiedThreadBaseHtml, $threadStealRegex){
    try {
        // $threadData structure [0][match][url][title][resed]
        $threadStealBool = preg_match_all($threadStealRegex, $encodiedThreadBaseHtml, $threadData, PREG_SET_ORDER);
        if(!$threadStealBool){
            throw new Exception("Do not preg_match_all to thread!");
        }
    } catch (Exception $e){
        echo $e->getMessage();
    }
    return $threadData;
}

function getThreadListUrl($menuUrl, $fixListSuffix){
    $threadUrl = $menuUrl . $fixListSuffix;
    return $threadUrl;
}

function getThreadBaseUrl($menuUrl, $fixListMidfix, $regex){
    preg_match($regex, $menuUrl, $match);
    $threadBaseUrl = $match[1] . $fixListMidfix . $match[2] . "/";
    return $threadBaseUrl;
}

function threadReArray($tempThreadData, $threadUrlBase, $censorList){

    unset(
        $reArrayedThreadData
    );

    $reArrayedThreadData = [];
    foreach ($tempThreadData as $key => $value) {
        $reArrayedThreadData[$key]["thread_sha"]        = sha1($tempThreadData[$key][1] . $tempThreadData[$key][2]);
        $reArrayedThreadData[$key]["thread_url"]        = $threadUrlBase . $tempThreadData[$key][1];
        $reArrayedThreadData[$key]["thread_title"]      = censorShip($tempThreadData[$key][2], $censorList);
        $reArrayedThreadData[$key]["thread_now_res_no"] = $tempThreadData[$key][3];
        //DateTime will set by function of DataBase.
    }
    return $reArrayedThreadData;
}

function getResArray($resStealRegex, $encodiedResBaseHtml, $threadSha){
    $resArray = [];
    $resStealBool = preg_match_all($resStealRegex, $encodiedResBaseHtml, $resData, PREG_SET_ORDER);
    var_dump($resStealBool);
    $countOfResData = count($resData);
    for ($i=0; $i < $countOfResData; $i++) {
        $resArray[$i]["res_sha"]        = sha1($resData[$i][0]);
        $resArray[$i]["thread_sha"]     = $threadSha;
        $resArray[$i]["res_no"]         = (int)$resData[$i][1];
        $resArray[$i]["res_username"]   = $resData[$i][2];
        $resArray[$i]["res_date"]       = $resData[$i][3];
        $resArray[$i]["res_clock"]      = $resData[$i][4];
        $resArray[$i]["res_id"]         = $resData[$i][5];
        $resArray[$i]["res_body"]       = $resData[$i][6];
    }
return $resArray;
}

function resBodyAnkaerLinkProc($tempResBodyByResData, $resBodyAnkaerLinkStealRegex){

    unset(
        $resAnkaerLink,
        $resAnkaerNo, 
        $resAnkaerLinkTogetherBool, 
        $splitedAnkaer, 
        $splitedAnkaerCount
    );

    $countOfResBodyByResData = count($tempResBodyByResData);
    for ($i=0; $i < $countOfResBodyByResData; $i++) {
        //From the entire first we want to extract the anchor.
        preg_match_all($resBodyAnkaerLinkStealRegex, $tempResBodyByResData[$i]["res_body"], $resAnkaerLink, PREG_SET_ORDER);
        $countOfResAnkaerLink = count($resAnkaerLink);
        $resAnkaerNo = [];
        for ($k=0; $k < $countOfResAnkaerLink; $k++) {
            $quotedAnkaerLink = "/" . preg_quote($resAnkaerLink[$k][0],"/") . "/";
            $tempResBodyByResData[$i]["res_body"]    = preg_replace($quotedAnkaerLink, $resAnkaerLink[$k][1], $tempResBodyByResData[$i]["res_body"]);
            $resAnkaerLinkTogetherBool      = preg_match("/-/", $resAnkaerLink[$k][2], $match);
            //In the case of multiple number anchor.
            if($resAnkaerLinkTogetherBool){
                $splitedAnkaer      = preg_split("/-/", $resAnkaerLink[$k][2]);
                $splitedAnkaerCount = count($splitedAnkaer);
                for ($m=0; $m < $splitedAnkaerCount; $m++) {
                    if(!($splitedAnkaer[$m] >= $tempResBodyByResData[$i]["res_no"])){
                        array_push($resAnkaerNo, $splitedAnkaer[$m]);
                    }
                }
            }else{
                array_push($resAnkaerNo,$resAnkaerLink[$k][2]);
            }
        }
        if($resAnkaerNo){
            //The process of the res to have an anchor to a series of sequence.
            //Above four digits represent the anchor destination, the last four digits represents a res_no.
            // $resAnkaerMax = $resAnkaerNo[0];
            $resAnkaerMax = max($resAnkaerNo);
            // $resAnkaerMax = ($resAnkaerNo[0] * 10000) + $tempResBodyByResData[$i][1];
        }else{
            $resAnkaerMax = null;
            // $resAnkaerMax = $tempResBodyByResData[$i][1];
            // $resAnkaerMax = ($tempResBodyByResData[$i][1] * 10000);
        }
        $tempResBodyByResData[$i]["res_seq"] = $resAnkaerMax;
    }
    return $tempResBodyByResData;
}
function resBodyImgLinkProcO($dbHandle, $tempResBodyByResData, $threadSha, $regex, $prepareStForTUTI, $imgBaseUrl){

    $countOfResBodyByResData = count($tempResBodyByResData);
    for ($i=0; $i < $countOfResBodyByResData; $i++) {
        // if($i > 20){break;}
        $tempResBodyByResData[$i]["res_rowbody"] = $tempResBodyByResData[$i]["res_body"];

        $rehtml = preg_replace
            (
                "/<ares\snum=\"[0-9]+\">\n<div\sclass=\"[\sa-zA-Z0-9]+\">\n<span\sclass=\"[\sa-zA-Z0-9]+\">\n<font size=[0-9]+>\n<a\s[\sa-zA-Z0-9=\"#]+>\n<img\s[_\s\/\.a-zA-Z0-9=\"#:]+>\s<count>[0-9]*<\/count>.<\/a><\/font>\n<\/span>\n<\/div>\n<\/ares>/u",
                "",
                $tempResBodyByResData[$i]["res_body"]
            ); 
        //<br> change \n for Make a line break line by line
        $rehtml = preg_replace("/<br>/", "\n", $rehtml); 
        $rehtml = preg_replace("/>/", ">\n", $rehtml); 
        $rehtml = preg_replace("/</", "\n<", $rehtml); 
        //remove the excess \n 
        $rehtml = preg_replace("/\n\n/", "\n", $rehtml); 

        $resImgData = [];
        $resImgTag = "";
        $shaedImgFileName = "";


        preg_match_all("/<div.+/", $rehtml, $matchDiv, PREG_SET_ORDER);
        if($matchDiv){
            foreach ($matchDiv as $value) {
                $quotedImgLink = "/" . preg_quote($value[0],"/") . "/";
                $rehtml        = preg_replace($quotedImgLink, "", $rehtml);
            }
        }

        preg_match_all("/<\/div>/", $rehtml, $matchDivEnd, PREG_SET_ORDER);
        if($matchDivEnd){
            foreach ($matchDivEnd as $value) {
                $quotedImgLink = "/" . preg_quote($value[0],"/") . "/";
                $rehtml        = preg_replace($quotedImgLink, "", $rehtml);
            }
        }

        preg_match_all("/<br\sclear=[allboth]+>/", $rehtml, $matchBr, PREG_SET_ORDER);
        if($matchBr){
            foreach ($matchBr as $value) {
                $quotedImgLink = "/" . preg_quote($value[0],"/") . "/";
                $rehtml        = preg_replace($quotedImgLink, "", $rehtml);
            }
        }

        preg_match_all("/<a.*href=.*\.[jpegnifbmJPEGNIFBM]{3,4}.*>/", $rehtml, $matchA, PREG_SET_ORDER);
        if($matchA){
            foreach ($matchA as $value) {
                $quotedImgLink = "/" . preg_quote($value[0],"/") . "/";
                $rehtml        = preg_replace($quotedImgLink, "", $rehtml);
            }
        }

        preg_match_all("/<img.*data-original=\"?(https?:\/\/.*(\.[jpegnifbmJPEGNIFBM]{3,4}))\"?\s.*>/", $rehtml, $matchImg, PREG_SET_ORDER);
        $countOfMatchImg = count($matchImg);
        for ($k=0; $k < $countOfMatchImg; $k++) {
            if($matchImg[$k][0]){
                $shaedImgFileName = $tempResBodyByResData[$i]["res_no"] . "_" . $k . "_" . sha1($matchImg[$k][0]) . $matchImg[$k][2];
                $imgReUrl         = $imgBaseUrl . $shaedImgFileName;
                $resImgReTag      = "<a href=\"{$imgReUrl}\" target=\"_blank\"><img src=\"{$imgReUrl}\"><\/a>";
                $quotedImgLink    = "/" . preg_quote($matchImg[$k][0],"/") . "\n<\/a>/";
                $rehtml           = preg_replace($quotedImgLink, $resImgReTag, $rehtml);
                $quotedImgLink2    = "/" . preg_quote($matchImg[$k][0],"/") . "<\/a>/";
                $rehtml           = preg_replace($quotedImgLink2, $resImgReTag, $rehtml);
                $quotedImgLink3    = "/" . preg_quote($matchImg[$k][0],"/") . "/";
                $rehtml           = preg_replace($quotedImgLink3, $resImgReTag, $rehtml);
                $imgUrlForView    = $matchImg[$k][1];

                array_push($resImgData, ["img_url"=>$imgReUrl, "img_link"=>$imgUrlForView, "img_name"=>$shaedImgFileName, "extention"=>$matchImg[$k][2]]);
            }
        }

        $isp = preg_match_all("/(https?:\/\/.+(\.[jpegnifbmJPEGNIFBM]{3,4}))\n/", $rehtml, $matchAAnchor, PREG_SET_ORDER);
        if($isp){
            $countOfMatchAAnchor = count($matchAAnchor);
            for ($k=0; $k < $countOfMatchAAnchor; $k++) {
                $shaedImgFileName = $tempResBodyByResData[$i]["res_no"] . "_" . $k . "_" . sha1($matchAAnchor[$k][0]) . $matchAAnchor[$k][2];
                $imgReUrl         = $imgBaseUrl . $shaedImgFileName;
                $resImgReTag      = "<img src=\"{$imgReUrl}\">";
                $quotedImgLink    = "/" . preg_quote($matchAAnchor[$k][0],"/") . "/";
                $rehtml           = preg_replace($quotedImgLink, $resImgReTag, $rehtml);
                $imgUrlForView    = $matchAAnchor[$k][1];

                array_push($resImgData, ["img_url"=>$imgReUrl, "img_link"=>$imgUrlForView, "img_name"=>$shaedImgFileName, "extention"=>$matchAAnchor[$k][2]]);
            }
        }

        preg_match_all("/(<img\ssrc=.*\.[jpegnifbmJPEGNIFBM]{3,4}.*>)<\/a>/u", $rehtml, $matchImgA, PREG_SET_ORDER);
        foreach ($matchImgA as $value) {
            $quotedImgLink = "/" . preg_quote($value[0],"/") . "/";
            $rehtml        = preg_replace($quotedImgLink, $value[1], $rehtml);

        }

        $rehtml = preg_replace("/>\n>\n/", ">>", $rehtml); 
        $rehtml = preg_replace("/\n/", "<br>", $rehtml); 
        $rehtml = preg_replace("/<br><br>/", "", $rehtml); 

        if($resImgData){
            threadUpdateTois_img($dbHandle, $threadSha, $prepareStForTUTI);
        }
        $tempResBodyByResData[$i]["res_body"] = $rehtml;
        $tempResBodyByResData[$i]["res_imgtag"] = $resImgData;
    }
    unset($resImgUrl, $resImgTag, $shaedImgFileName);
    return $tempResBodyByResData;
}

function resBodyImgLinkProc($dbHandle, $tempResBodyByResData, $threadSha, $regex, $prepareStForTUTI, $imgBaseUrl, $kind){

    $countOfResBodyByResData = count($tempResBodyByResData);
    for ($i=0; $i < $countOfResBodyByResData; $i++) {
        $tempResBodyByResData[$i]["res_rowbody"] = $tempResBodyByResData[$i]["res_body"];

        //うまく正規表現で取れないので、改行を入れて擬似的に分割をしている
        if($kind === "open2ch.net"){
            $splitArray = preg_replace("/<\/div>/", "</div>\n", $tempResBodyByResData[$i]["res_body"]); 
            // $splitArray = preg_replace("/<br>/", "<br>\n", $splitArray); 
        }
        $isImg = preg_match_all($regex, $splitArray, $resImgLink, PREG_SET_ORDER);

        $resImgData = [];
        $resImgTag = "";
        $shaedImgFileName = "";

        $countOfResImgLink = count($resImgLink);
        for ($k=0; $k < $countOfResImgLink; $k++) {
            $shaedImgFileName            = $tempResBodyByResData[$i]["res_no"] . "_" . $k . "_" . sha1($resImgLink[$k][0]) . $resImgLink[$k][2];
            $imgReUrl                    = $imgBaseUrl . $shaedImgFileName;
            $resImgReTag                 = "<br><img src=\"{$imgReUrl}\"><br>";
            $quotedImgLink               = "/" . preg_quote($resImgLink[$k][0],"/") . "/";
            $imgUrlForView               = preg_replace("/2ch\.io\//", "", $resImgLink[$k][1]); 
            $tempResBodyByResData[$i]["res_body"] = preg_replace($quotedImgLink, $resImgReTag, $tempResBodyByResData[$i]["res_body"]);

            array_push($resImgData, ["img_url"=>$imgReUrl, "img_link"=>$imgUrlForView, "img_name"=>$shaedImgFileName, "extention"=>$resImgLink[$k][2]]);
        }
        // if($resImgData){
        //     threadUpdateTois_img($dbHandle, $threadSha, $prepareStForTUTI);
        // }
        $tempResBodyByResData[$i]["res_imgtag"] = $resImgData;
    }
    unset($resImgUrl, $resImgTag, $shaedImgFileName);
    return $tempResBodyByResData;
}

function imgurCheck($imgUrl, $extention){
    $bool = preg_match("/imgur/", $imgUrl, $match);
    $reImgUrl = "";
    if($bool){
        $reImgUrl = preg_replace("/s{$extention}/", $extention, $imgUrl);
    }else{
        $reImgUrl = $imgUrl;
    }
    return $reImgUrl;
}

function kindChecher($res_id){
    $bool = preg_match("/net/", $res_id, $match);
    if($bool){
        $kind = "net";
    }else{
        $kind = "sc";
    }
    return $kind;
}

function resDataReArray($tempResBodyByResData, $net_or_sc, $kind){

    $reArrayedResData;
    foreach ($tempResBodyByResData as $key => $value) {
        $reArrayedResData[$key]["res_sha"]          = $tempResBodyByResData[$key]["res_sha"];
        $reArrayedResData[$key]["thread_sha"]       = $tempResBodyByResData[$key]["thread_sha"];
        $reArrayedResData[$key]["res_seq"]          = (int)$tempResBodyByResData[$key]["res_seq"];
        $reArrayedResData[$key]["res_no"]           = (int)$tempResBodyByResData[$key]["res_no"];
        $reArrayedResData[$key]["res_username"]     = $tempResBodyByResData[$key]["res_username"];
        $reArrayedResData[$key]["res_date"]         = $tempResBodyByResData[$key]["res_date"];
        $reArrayedResData[$key]["res_clock"]        = $tempResBodyByResData[$key]["res_clock"];
        $reArrayedResData[$key]["res_id"]           = $tempResBodyByResData[$key]["res_id"];
        if($kind === "open2ch.net"){
            $reArrayedResData[$key]["res_body"]         = $tempResBodyByResData[$key]["res_body"] . "<br>";
            $reArrayedResData[$key]["res_rowbody"]      = $tempResBodyByResData[$key]["res_rowbody"] . "<br>";
        }else{
            $reArrayedResData[$key]["res_body"]         = $tempResBodyByResData[$key]["res_body"];
            $reArrayedResData[$key]["res_rowbody"]      = $tempResBodyByResData[$key]["res_rowbody"];
        }
        $reArrayedResData[$key]["res_imgtag"]       = $tempResBodyByResData[$key]["res_imgtag"];
        $reArrayedResData[$key]["censored"]         = $tempResBodyByResData[$key]["censored"];
        $reArrayedResData[$key]["net_or_sc"]        = $net_or_sc;
        //DateTime will set by resInsert().
    }
    unset($value,$key);
    return $reArrayedResData;
}

function censorShip($censoredString, $censorList){
    foreach ($censorList as $value) {
        $censoredString = preg_replace($value["censor_word"], $value["be_censored"], $censoredString);
    }
    return $censoredString;
}

function censorShipForRes($resDataArray, $censorList){
    $countOfResDataArray = count($resDataArray);
    for($i = 0; $i < $countOfResDataArray; $i++){
        foreach ($censorList as $value) {
            $censored            = preg_match($value["censor_word"], $resDataArray[$i]["res_body"], $macth);
            $resDataArray[$i]["res_body"] = preg_replace($value["censor_word"], $value["be_censored"], $resDataArray[$i]["res_body"]);
            if($censored){
                $resDataArray[$i]["censored"] = $censored;
            }else{
                $resDataArray[$i]["censored"] = 0;
            }
        }
    }
    return $resDataArray;
}

function insertCensorWord($dbHandle, $censor_word, $be_censor, $prepareSt){
    $formatCensor_word = "/" . $censor_word . "/";
    $prepare = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":censor_word", $censorWord);
    $prepare->bindParam(":be_censored", $beCensor);
    $censorWord= $formatCensor_word;
    $beCensor = $be_censor;
    $prepare->execute();
    $dbHandle = null;
}

function resBodyManualCensorUpdert($dbHandle, $censored_res_body, $res_sha, $prepareSt){
    $prepare  = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":res_sha", $resSha);
    $prepare->bindParam(":res_body", $resBody);
    $resSha   = $res_sha;
    $resBody  = $censored_res_body;
    $prepare->execute();
    $dbHandle = null;
}

function resBodyCensorProc($dbHandle, $censor_word, $be_censored, $res_sha, $prepareStSELECT){
    $prepare  = $dbHandle->prepare($prepareStSELECT);
    $prepare->bindParam(":res_sha", $resSha);
    $resSha   = $res_sha;
    $prepare->execute();
    $selectedResData = $prepare->fetchAll();
    $ResData = [];
    foreach ($selectedResData as $value) {
        $resBody = $value["res_body"];
    }

    $formatCensor_word = "/".$censor_word."/";
    $censored_Res_Body = preg_replace($formatCensor_word, $be_censored, $resBody);
    $dbHandle = null;

    return $censored_Res_Body;
}

function menuDataProc ($dbHandle, $menuListUrl, $kind, $menuStealRegex, $menuQuery) {

    $menuDataArray = menuReArray
        (
            menuDataPregMatch
            (
                menuSteal
                (
                    $menuListUrl
                ),
                $menuStealRegex
            )
        );

    menuInsert
    (
        $menuDataArray,
        $kind,
        $menuQuery,
        $dbHandle
    );
}

function threadDataProc
    (
        $threadList,
        $threadUrl,
        $menuId,
        $censorList,
        $threadStealRegex,
        $prepareSt,
        $dbHandle
){

timeTest("TreadDPStart");
unset(
    $threadDataArray
);

$threadDataArray = threadReArray
    (
        threadDataPregMatch
        (
            threadSteal
            (
                $threadList
            ),
            $threadStealRegex
        ),
        $threadUrl,
        $censorList
    );

timeTest("TreadDPEnd");

threadInsert
(
    $threadDataArray,
    $menuId,
    $prepareSt,
    $dbHandle
);

return $threadDataArray;
}

function resDataProc ( $dbHandle, $threadData, $kind, $resStealRegex, $resBodyAnkaerLinkStealRegex, $resBodyImgLinkStealRegex, $threadOldLogedRegex, $prepareStForRI, $prepareStForTUPTR, $prepareStForTUIO, $prepareStForTUTI, $prepareStForTUNOS, $imgBaseUrl, $censorList){

    $i = 0;
    foreach ($threadData as $value) {

        if($i > 2){break;}
        $encodiedResBaseHtml    = resSteal($value["thread_url"]);
        $isoldLog               = threadIsOldLog($encodiedResBaseHtml, $threadOldLogedRegex);

        if($isoldLog){
            threadUpdateTois_oldlog($value["thread_sha"], $prepareStForTUIO, $dbHandle);
        }

        $resData  =
            getResArray
            (
                $resStealRegex,
                $encodiedResBaseHtml,
                $value["thread_sha"]
            );

        $ankaerLinkProced =
            resBodyAnkaerLinkProc
            (
                $resData,
                $resBodyAnkaerLinkStealRegex
            ); 

        $imgLinkProced  =
            resBodyImgLinkProc(
                $dbHandle,
                $ankaerLinkProced,
                $value["thread_sha"],
                $resBodyImgLinkStealRegex,
                $prepareStForTUTI,
                $imgBaseUrl
            );

        $censoredProced =
            censorShipForRes
            (
                $imgLinkProced,
                $censorList
            );

        $net_or_sc_kind = kindChecher($censoredProced[0]["res_id"]);

        $resDataArray = 
            resDataReArray
            (
                $censoredProced,
                $net_or_sc_kind
            );

        $countOfResDataArray = count($resDataArray);//MAX RES number.

        resInsert
            (
                $dbHandle,
                $resDataArray,
                $kind,
                $prepareStForRI
            );

        threadUpdateTonet_or_sc
            (
                $dbHandle,
                $net_or_sc_kind,
                $value["thread_sha"],
                $prepareStForTUNOS
            );

        threadUpdateToRes_end
            (
                $dbHandle,
                $value["thread_sha"],
                $countOfResDataArray,
                $prepareStForTUPTR
            ); 

        unset(
            $resDataArray,
            $censoredProced,
            $imgLinkProced,
            $ankaerLinkProced,
            $encodiedResBaseHtml,
            $resStealBool
        );

        $i++;
    }
    unset($value);

}

function getPDO(){
    global $dbname, $dbuser, $dbpass;
    $dbHandle = new PDO($dbname, $dbuser, $dbpass);
    return $dbHandle;
}

function getLoggingData($dbHandle){
    $list = [];
    foreach ($dbHandle->query("SELECT * FROM loging") as $value) {
        $list["password"] = $value["password"];
        $list["user"] = $value["user"];
    }
    return $list;
}


function menuInsert($dbHandle, $menuDataArray, $_kind, $prepareSt){
    $prepare = $dbHandle->prepare($prepareSt);
    foreach ($menuDataArray as $value) {
        $prepare->bindParam(":menu_url", $menu_url);
        $prepare->bindParam(":menu_title", $menu_title);
        $prepare->bindParam(":kind", $kind);
        $prepare->bindParam(":datetime", $datetime);

        $menu_url      = $value["menu_url"];
        $menu_title    = $value["menu_title"];
        $kind          = $_kind;
        $datetime      = date("Y-m-d H:i:s");

        $prepare->execute();
    }
    $dbHandle = null;
}

function threadInsert($dbHandle, $resDataArray, $menuId, $_kind, $prepareSt){
    $prepare = $dbHandle->prepare($prepareSt);
    foreach ($resDataArray as $value) {
        $prepare->bindParam(":thread_sha", $thread_sha);
        $prepare->bindParam(":thread_url", $thread_url);
        $prepare->bindParam(":thread_title", $thread_title);
        $prepare->bindParam(":thread_now_res_no", $thread_now_res_no);
        $prepare->bindParam(":menu_id", $menu_id);
        $prepare->bindParam(":kind", $kind);
        $prepare->bindParam(":datetime", $datetime);

        $thread_sha           = $value["thread_sha"];
        $thread_url           = $value["thread_url"];
        $thread_title         = $value["thread_title"];
        $thread_now_res_no    = $value["thread_now_res_no"];
        $menu_id              = $menuId;
        $kind                 = $_kind;
        $datetime             = date("Y-m-d H:i:s");

        $prepare->execute();
    }
    $dbHandle = null;
}

function resInsert($dbHandle, $resDataArray, $_kind, $prepareSt){
    $prepare = $dbHandle->prepare($prepareSt);
    foreach ($resDataArray as $value) {
        $prepare->bindParam(":thread_sha", $thread_sha);
        $prepare->bindParam(":res_sha", $res_sha);
        $prepare->bindParam(":res_seq", $res_seq);
        $prepare->bindParam(":res_no", $res_no);
        $prepare->bindParam(":res_username", $res_username);
        $prepare->bindParam(":res_date", $res_date);
        $prepare->bindParam(":res_clock", $res_clock);
        $prepare->bindParam(":res_id", $res_id);
        $prepare->bindParam(":res_body", $res_body);
        $prepare->bindParam(":res_rowbody", $res_rowbody);
        $prepare->bindParam(":res_imgtag", $res_imgtag);
        $prepare->bindParam(":censored", $censored);
        $prepare->bindParam(":kind", $kind);
        $prepare->bindParam(":net_or_sc", $net_or_sc);
        $prepare->bindParam(":datetime", $datetime);

        $thread_sha      = $value["thread_sha"];
        $res_sha         = $value["res_sha"];
        $res_seq         = $value["res_seq"];
        $res_no          = $value["res_no"];
        $res_username    = $value["res_username"];
        $res_date        = $value["res_date"];
        $res_clock       = $value["res_clock"];
        $res_id          = $value["res_id"];
        $res_body        = $value["res_body"];
        $res_rowbody     = $value["res_rowbody"];
        $res_imgtag      = serialize($value["res_imgtag"]);
        $censored        = $value["censored"];
        $kind            = $_kind;
        $net_or_sc       = $value["net_or_sc"];
        $datetime        = date("Y-m-d H:i:s");

        $prepare->execute();
    }
    $dbHandle = null;
}

function threadUpdateToRes_end($dbHandle, $DIthread_sha, $DIres_end, $prepareSt){
    $prepare      = $dbHandle->prepare($prepareSt);

    $prepare->bindParam(":thread_sha", $thread_sha);
    $prepare->bindParam(":res_end", $res_end);

    $thread_sha   = $DIthread_sha;
    $res_end      = $DIres_end;

    $prepare->execute();

    $dbHandle = null;
}

function threadUpdateTois_oldlog($DIthread_sha, $prepareSt, $dbHandle){
    $prepare      = $dbHandle->prepare($prepareSt);

    $prepare->bindParam(":thread_sha", $thread_sha);
    $prepare->bindParam(":is_oldlog", $is_oldlog);

    $thread_sha   = $DIthread_sha;
    $is_oldlog    = 1;

    $prepare->execute();

    $dbHandle = null;
}

function threadUpdateToArticled($dbHandle, $thread_sha, $prepareSt){
    $prepare = $dbHandle->prepare($prepareSt);

    $prepare->bindParam(":articled", $articled);
    $prepare->bindParam(":thread_sha", $threadSha);

    $articled = 1;
    $threadSha = $thread_sha;

    $prepare->execute();

    $dbHandle = null;
}

function threadUpdateTois_img($dbHandle, $thread_sha, $prepareSt){
    $prepare = $dbHandle->prepare($prepareSt);

    $prepare->bindParam(":is_img", $isImg);
    $prepare->bindParam(":thread_sha", $threadSha);

    $isImg = 1;
    $threadSha = $thread_sha;

    $prepare->execute();

    $dbHandle = null;
}

function updeateToDeleatFlag($dbHandle, $thread_sha, $prepareSt){
    $prepare =$dbHandle->prepare($prepareSt);

    $prepare->bindParam(":thread_sha", $threadSha);
    $threadSha = $thread_sha;

    $prepare->execute();

    $dbHandle = null;
}

function updeateToSuspendFlag($dbHandle, $thread_sha, $prepareSt){
    $prepare =$dbHandle->prepare($prepareSt);

    $prepare->bindParam(":thread_sha", $threadSha);
    $threadSha = $thread_sha;

    $prepare->execute();

    $dbHandle = null;
}
function killDelete($dbHandle, $query){
    $dbHandle->query($query);
}

function threadUpdateTonet_or_sc($dbHandle, $net_or_sc, $thread_sha, $prepareSt){
    $prepare =$dbHandle->prepare($prepareSt);

    $prepare->bindParam(":net_or_sc", $netOrSc);
    $prepare->bindParam(":thread_sha", $threadSha);
    $threadSha = $thread_sha;
    $netOrSc   = $net_or_sc;

    $prepare->execute();

    $dbHandle = null;
}

function threadIsOldLog($html, $regex){
    $isoldLoged = preg_match($regex, $html, $match);
    if($isoldLoged){
        return true;
    }
}

function getUa($dbHandle, $query){
    $list = [];
    $i = 0;
    foreach ($dbHandle->query($query) as $value) {
        $list[$i] = ["id"=>$value["id"], "ua"=>$value["ua"]];
        $i++;
    }
    $id = rand(0,count($list) - 1);
    $dbHandle = null;
    return $list[$id]["ua"];
}

function getCensorList($dbHandle, $query){
    $list = [];
    $i = 0;
    foreach ($dbHandle->query($query) as $value) {
        $list[$i] = ["censor_word"=>$value["censor_word"], "be_censored"=>$value["be_censored"]];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getSQLQuery($dbHandle){
    $list = [];
    $i = 0;
    foreach ($dbHandle->query("SELECT * FROM query;") as $key => $value) {
        $list[$value[0]] = $value[1];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getIp($dbHandle, $query){
    $list = [];
    $i = 0;
    foreach ($dbHandle->query($query) as $key => $value) {
        $list["ip"] = $value[0];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getRegex($dbHandle, $query){
    $list = [];
    $i = 0;
    foreach ($dbHandle->query($query) as $key => $value) {
        $list[$value[0]] = $value[1];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getFix($dbHandle, $query){
    $list = [];
    $i    = 0;
    foreach ($dbHandle->query($query) as $key => $value) {
        $list[$value[0]] = $value[1];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getMenu($dbHandle, $query){
    $list = [];
    $i    = 0;
    foreach ($dbHandle->query($query) as $key => $value) {
        $list[$i]["menu_url"] = $value[0];
        $list[$i]["kind"]     = $value[1];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getProcLockData($dbHandle, $query){
    $list = [];
    $i    = 0;
    foreach ($dbHandle->query($query) as $value) {
        $list["id"]             = $value["id"];
        $list["list_length"]    = $value["list_length"];
        $list["proced"]         = (int)$value["proced"];
        $list["thread_limit"]   = (int)$value["thread_limit"];
        $list["thread_offset"]  = (int)$value["thread_offset"];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getMenuList($dbHandle, $query){
    $list = [];
    $i    = 0;
    foreach ($dbHandle->query($query) as $key => $value) {
        $list [$value["id"]] = 
            [
                "id" => $value["id"],
                "menu_url" => $value["menu_url"],
                "menu_title" => $value["menu_title"]
            ];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getDiRegex($dbHandle, $regex_name, $_kind, $prepareSt){
    $list = "";

    $prepare  = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":name", $name);
    $prepare->bindParam(":kind", $kind);
    $name     = $regex_name;
    $kind     = $_kind;
    $prepare->execute();
    $regex = $prepare->fetchAll();

    foreach ($regex as $value) {
        $list = $value["regex"];
    }
    $dbHandle = null;
    return $list;
}

function getDiFix($dbHandle, $fix_name, $_kind, $prepareSt){
    $list = "";

    $prepare  = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":name", $name);
    $prepare->bindParam(":kind", $kind);
    $name     = $fix_name;
    $kind     = $_kind;
    $prepare->execute();
    $regex = $prepare->fetchAll();

    foreach ($regex as $value) {
        $list = $value["fix"];
    }
    $dbHandle = null;
    return $list;
}

function getMenuListForProcOrder($dbHandle, $query){
    $list = [];
    $i    = 0;
    $prepare = $dbHandle->prepare($query); 
    $prepare->execute();
    $menuList = $prepare->fetchAll();

    foreach ($menuList as $key => $value) {
        if($value["menu_order"]){
            $list[$i] =
                [
                    "id" => $value["id"],
                    "menu_url" => $value["menu_url"],
                    "menu_title" => $value["menu_title"],
                    "menu_defaultname"=>$value["menu_defaultname"],
                    "kind"=>$value["kind"],
                    "menu_order"=>(int)$value["menu_order"]
                ];
            $i++;
        }
    }
    $dbHandle = null;
    return $list;
}

function getProced($dbHandle, $menuList){
    $proced = [];
    foreach ($dbHandle->query("SELECT * FROM proclock;") as $value) {
        $proced = $value["proced"];
    }
    $prepare = $dbHandle->prepare("UPDATE matome.proclock SET proced = :proced WHERE id = 0;"); 
    $prepare->bindParam(":proced", $newProced);
    $countOfMenuList = count($menuList);
    if($proced >= $countOfMenuList){ 
        $newProced = 1;
    }else{
        $newProced = $proced + 1;
    }
    // var_dump($newProced);

    $prepare->execute();
    $dbHandle = null;
    $mod = $newProced % $countOfMenuList;

    return $menuList[$mod];
}


function getMenuDefaultName($dbHandle, $menu_id, $prepareSt){
    $list = [];
    $prepare    = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":menu_id", $menuId);
    $menuId     = $menu_id;
    $prepare->execute();
    $menuData = $prepare->fetchAll();
    foreach ($menuData as $value) {
        $list["menu_defaultname"]  = $value["menu_defaultname"];
    }
    $dbHandle = null;
    return $list["menu_defaultname"];

}

function getThreadData($dbHandle, $menu_id, $prepareSt){
    $list = [];
    $i    = 0;

    $prepare    = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":menu_id", $menuId);
    $menuId     = $menu_id;
    $prepare->execute();
    $threadData = $prepare->fetchAll();

    foreach ($threadData as $value) {
        $list[$i]["thread_sha"]        = $value["thread_sha"];
        $list[$i]["thread_url"]        = $value["thread_url"];
        $list[$i]["thread_title"]      = $value["thread_title"];
        $list[$i]["thread_now_res_no"] = (int)$value["thread_now_res_no"];
        $list[$i]["res_end"]           = (int)$value["res_end"];
        $list[$i]["articled"]          = (int)$value["articled"];
        $list[$i]["is_oldlog"]         = (int)$value["is_oldlog"];
        $list[$i]["menu_id"]           = (int)$value["menu_id"];
        $list[$i]["is_img"]            = (int)$value["is_img"];
        $list[$i]["delete_flag"]       = (int)$value["delete_flag"];
        $list[$i]["kind"]              = $value["kind"];
        $list[$i]["net_or_sc"]         = $value["net_or_sc"];
        $list[$i]["datetime"]          = $value["datetime"];
        $i++;
    }
    $dbHandle = null;
    return $list;
}
function getThreadDataForDelete($dbHandle, $prepareSt){
    $list = [];
    $i    = 0;

    $prepare    = $dbHandle->prepare($prepareSt);
    $prepare->execute();
    $threadData = $prepare->fetchAll();

    foreach ($threadData as $value) {
        $list[$i]["thread_sha"] = $value["thread_sha"];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getThreadDataBySha($dbHandle, $thread_sha, $prepareSt){
    $list = [];

    $prepare    = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":thread_sha", $threadSha);
    $threadSha  = $thread_sha;
    $prepare->execute();
    $threadData = $prepare->fetchAll();

    foreach ($threadData as $value) {
        $list["thread_sha"]        = $value["thread_sha"];
        $list["thread_url"]        = $value["thread_url"];
        $list["thread_title"]      = $value["thread_title"];
        $list["thread_now_res_no"] = (int)$value["thread_now_res_no"];
        $list["res_end"]           = (int)$value["res_end"];
        $list["articled"]          = (int)$value["articled"];
        $list["is_oldlog"]         = (int)$value["is_oldlog"];
        $list["menu_id"]           = (int)$value["menu_id"];
        $list["is_img"]            = (int)$value["is_img"];
        $list["delete_flag"]       = (int)$value["delete_flag"];
        $list["kind"]              = $value["kind"];
        $list["net_or_sc"]         = $value["net_or_sc"];
        $list["datetime"]          = $value["datetime"];
    }
    $dbHandle = null;
    return $list;
}

function getThreadDataAll($dbHandle, $prepareSt){
    $list = [];

    $prepare = $dbHandle->prepare($prepareSt);
    $prepare->execute();
    $threadData = $prepare->fetchAll();

    $i = 0;
    foreach ($threadData as $value) {
        $list[$i]["thread_sha"]        = $value["thread_sha"];
        $list[$i]["thread_url"]        = $value["thread_url"];
        $list[$i]["thread_title"]      = $value["thread_title"];
        $list[$i]["thread_now_res_no"] = (int)$value["thread_now_res_no"];
        $list[$i]["res_end"]           = (int)$value["res_end"];
        $list[$i]["articled"]          = (int)$value["articled"];
        $list[$i]["is_oldlog"]         = (int)$value["is_oldlog"];
        $list[$i]["menu_id"]           = (int)$value["menu_id"];
        $list[$i]["is_img"]            = (int)$value["is_img"];
        $list[$i]["delete_flag"]       = (int)$value["delete_flag"];
        $list[$i]["kind"]              = $value["kind"];
        $list[$i]["net_or_sc"]         = $value["net_or_sc"];
        $list[$i]["datetime"]          = $value["datetime"];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function getResData($dbHandle, $thread_sha, $prepareSt){
    $list = [];
    $i    = 0;

    $prepare    = $dbHandle->prepare($prepareSt);
    $prepare->bindParam(":thread_sha", $threadSha);
    $threadSha  = $thread_sha;
    $prepare->execute();
    $resData = $prepare->fetchAll();

    foreach ($resData as $value) {
        $list[$i]["res_sha"]          = $value["res_sha"];
        $list[$i]["thread_sha"]       = $value["thread_sha"];
        $list[$i]["res_seq"]          = (int)$value["res_seq"];
        $list[$i]["res_no"]           = (int)$value["res_no"];
        $list[$i]["res_username"]     = $value["res_username"];
        $list[$i]["res_date"]         = $value["res_date"];
        $list[$i]["res_clock"]        = $value["res_clock"];
        $list[$i]["res_id"]           = $value["res_id"];
        $list[$i]["res_body"]         = $value["res_body"];
        $list[$i]["res_rowbody"]      = $value["res_rowbody"];
        $list[$i]["res_imgtag"]       = $value["res_imgtag"];
        $list[$i]["censored"]         = $value["censored"];
        $list[$i]["kind"]             = $value["kind"];
        $list[$i]["net_or_sc"]        = $value["net_or_sc"];
        $list[$i]["datetime"]         = $value["datetime"];
        $i++;
    }
    $dbHandle = null;
    return $list;
}

function sortResByAnkaer($array){
    $countOfArray = count($array);
    $reArray = [];
    for ($i=0; $i < $countOfArray; $i++) {
        if($array[$i]["res_seq"] !== 0){
            $lengthOfReArray = count($reArray);
            $reArrayIndex = searchIndex($lengthOfReArray, $array, $reArray, $i);
            $sameCount = 0;
            //This process is to find a response with the same parent
            for ($k=$reArrayIndex; $k < $lengthOfReArray ; $k++) {
                if($reArray[$k]["res_seq"] === $array[$i]["res_seq"]){
                    $sameCount++;
                }
            }
            //This process will be inserted using the count results
            //As it is inserted if the counter is 0
            if($sameCount === 0){
                $upper   = array_slice($reArray, 0, $reArrayIndex + 1) ; 
                $bottom  = array_slice($reArray, $reArrayIndex + 1, $lengthOfReArray - $reArrayIndex); 
            }else{
                $upper   = array_slice($reArray, 0, ($reArrayIndex + 1 ) + $sameCount) ; 
                $bottom  = array_slice($reArray, ($reArrayIndex + 1) + $sameCount, $lengthOfReArray - $reArrayIndex); 
            }
            array_push($upper, $array[$i]);
            $reArray = array_merge($upper,$bottom);
        }else{
            array_push($reArray, $array[$i]);
        }
    }
    return $reArray;
}

function searchIndex($lengthOfReArray, $array, $reArray, $i){
    for ($k=0; $k < $lengthOfReArray; $k++) {
        if($array[$i]["res_seq"] === $reArray[$k]["res_no"]){;
        return $k;
        }
    }
}

function sizeSet($inputImgSize, $img) {
    $maxwidthsize = 1000;
    if($inputImgSize[0] > $maxwidthsize){
        $rasio = $maxwidthsize / $inputImgSize[0]; 
        $w = $inputImgSize[0] * $rasio;
        $h = $inputImgSize[1] * $rasio;
        $newImg = imagecreatetruecolor($w, $h);
        imagecopyresampled($newImg, $img, 0,0,0,0, $w, $h, $inputImgSize[0], $inputImgSize[1]);
        return $newImg; 
    }else{
        return $img;
    }
}
function sizeSetTumb($inputImgSize, $img) {
    $maxwidthsize = 150;
    if($inputImgSize[0] > $inputImgSize[1]){
        $rasio = $maxwidthsize / $inputImgSize[1]; 
        $w = round($inputImgSize[0] * $rasio);
        $h = round($inputImgSize[1] * $rasio);
        $diff = ($w - 150)*0.5;
        $newImg = imagecreatetruecolor($w, $h);
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $w, $h, $inputImgSize[0], $inputImgSize[1]);
        $newImg150 = imagecreatetruecolor(150, 150);
        imagecopyresampled($newImg150, $newImg, 0, 0, $diff, 0, 150, 150, 150, 150);
        return $newImg150; 
    }else{
        $rasio = $maxwidthsize / $inputImgSize[0]; 
        $w = round($inputImgSize[0] * $rasio);
        $h = round($inputImgSize[1] * $rasio);
        $diff = ($h - 150)*0.5;
        $newImg = imagecreatetruecolor($w, $h);
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $w, $h, $inputImgSize[0], $inputImgSize[1]);
        $newImg150 = imagecreatetruecolor(150, 150);
        imagecopyresampled($newImg150, $newImg, 0, 0, 0, $diff, 150, 150, 150, 150);
        return $newImg150; 
    }
}


function getUseIpMod($dbHandle){
    $ipUse = [];
    foreach ($dbHandle->query("SELECT * FROM proclock;") as $value) {
        $ipUse = $value["ipuse"];
    }
    
    $prepare = $dbHandle->prepare("SELECT * FROM ips WHERE ipuse = :ipuse;"); 
    $prepare->bindParam(":ipuse", $markedIp);
    $markedIp = 1;
    $prepare->execute();
    $useIp = $prepare->fetchAll();
    $useingIp = [];
    foreach ($useIp as $value) {
        $useingIp[] = $value["ip"];
    }
    $countOfUseingIp = count($useingIp);

    $prepare = $dbHandle->prepare("UPDATE matome.proclock SET ipuse = :ipuse WHERE id = 0;"); 
    $prepare->bindParam(":ipuse", $newIpuse);
    if($ipUse >= $countOfUseingIp){
        $newIpuse = 1;
    }else{
        $newIpuse = $ipUse + 1;
    }
    $prepare->execute();
    $mod = $newIpuse % $countOfUseingIp;
    
    $dbHandle = null;
    return $mod + 1;
}

function useIp($dbHandle, $mod){
    $prepare = $dbHandle->prepare("SELECT * FROM ips WHERE id = :id;"); 
    $prepare->bindParam(":id", $id);
    $id = $mod;

    $prepare->execute();
    $useIp = $prepare->fetchAll();
    $ip = [];
    foreach ($useIp as $value) {
        $ip["ip"] = $value["ip"];
    }

    $dbHandle = null;
    return $ip["ip"];
}

function timeWeit($min, $max){
    $weitTime = mt_rand($min, $max);
    usleep($weitTime);
}

function getEndResUpDate($dbHandle, $thread_sha, $res_end, $prepareSt){
    $prepare =$dbHandle->prepare($prepareSt);

    $prepare->bindParam(":thread_sha", $threadSha);
    $prepare->bindParam(":res_no", $resEnd);
    $threadSha = $thread_sha;
    $resEnd = $res_end;

    $prepare->execute();

    $endResData = $prepare->fetchAll();
    $endResDate = [];
    foreach ($endResData as $value) {
        $endResDate = ["res_date"=>$value["res_date"], "res_clock"=>$value["res_clock"]];
    }
    $dbHandle = null;
    return $endResDate;
}
function ipAccess($dbHandle, $_ip, $use_proc, $_access, $prepareSt){
    $prepare =$dbHandle->prepare($prepareSt);
    $prepare->bindParam(":ip", $ip);
    $prepare->bindParam(":access", $access);
    $prepare->bindParam(":use_proc", $useProc);
    $prepare->bindParam(":datetime", $datetime);

    $ip         = $_ip;
    $access     = $_access;
    $useProc    = $use_proc;
    $datetime   = date("Y-m-d H:i:s");

    $prepare->execute();
    $dbHandle = null;
}

function youtube($resBody){
    $reTagBody = $resBody;
    $bool = preg_match_all("/<img\srel=\"nofollow\"\swidth=[0-9]+\sheight=[0-9]+\ssrc=https?:\/\/img.youtube.com\/.+>[<br>]*\s*[<br>]*<a\sonMouseOver=\"funcYoutube\(this\)\"\starget=_blank\shref=\'https?:\/\/youtu\.be+\/(.{1,11})\'\svid=\".+\"\sclass=youtube>[<br>]*https?:\/\/youtu\.be\/.{1,11}[<br>]*<\/a>/", $reTagBody, $match, PREG_SET_ORDER);
    if($bool){
        $countMatch = count($match);
        for ($i=0; $i < $countMatch; $i++) {
            $embedTag = "<div class=\"youtube\" style=\"width:200px;height:140px\"><iframe width=100% height=100% src=\"https://www.youtube.com/embed/{$match[$i][1]}\" frameborder=\"0\" allowfullscreen></iframe></div>";
            $quotedImgLink    = "/" . preg_quote($match[$i][0],"/") . "/";
            $reTagBody        = preg_replace($quotedImgLink, $embedTag, $reTagBody);
        }
    unset($match);
    }

    $bool2 = preg_match_all("/<a\srel=\"nofollow\"\shref=\"https?:\/\/www\.youtube\.com\/watch\?.*v=(.{1,11})\"\starget=\"_blank\">[<br>]*https?:\/\/www\.youtube\.com\/watch\?.*v=.{1,11}[<br>]*<\/a>/", $reTagBody, $match, PREG_SET_ORDER);
    if($bool2){
        $countMatch = count($match);
        for ($i=0; $i < $countMatch; $i++) {
            $embedTag = "<div class=\"youtube\" style=\"width:250px;height:180px\"><iframe width=100% height=100% src=\"https://www.youtube.com/embed/{$match[$i][1]}\" frameborder=\"0\" allowfullscreen></iframe></div>";
            $quotedImgLink    = "/" . preg_quote($match[$i][0],"/") . "/";
            $reTagBody        = preg_replace($quotedImgLink, $embedTag, $reTagBody);
        }
    unset($match);
    }
    return $reTagBody;
}
