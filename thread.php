<?php
// require_once(dirname(dirname(dirname(__file__))) . "/config.php");
// require_once(dirname(dirname(dirname(__file__))) . "/functions.php");
require_once(dirname(__file__) . "/functions.php");

procLockCheck(getPDO());
procLocking(getPDO());

date_default_timezone_set('Asia/Tokyo');

$tags = [];
$times = [];

timeTest("ProcStart");
memoryGetUse("base");

$queryList      = getSQLQuery(getPDO());
$censorList     = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList      = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList      = getFix(getPDO(), $queryList["SELECT_FIX"]); 
$menuUrl        = getMenu(getPDO(), $queryList["SELECT_MENUURL"]);

$isMenuSteal    = false;
$isThreadSteal  = true;
$isResSteal     = true;
$isProc         = true;

if($isMenuSteal){
    foreach ($menuUrl as $value) {
        $ip             = useIp(getPDO(),getUseIpMod(getPDO()));
        $html           = menuSteal(getPDO(), $ip, "menuSteal", $value["menu_url"], $queryList["INSERT_IP_ACCESS"], getUa(getPDO(), $queryList["SELECT_UA"]), "open2ch.net");
        $regex          = getDiRegex(getPDO(), "menuStealRegex", $value["kind"], $queryList["SELECT_DIREGEX"]);
        $menuListArray  = menuReArray(menuDataPregMatch($html, $regex)); 
        menuInsert(getPDO(), $menuListArray, $value["kind"], $queryList["INSERT_MENU"]); 
    }
    unset($value);
}
// $menuList = [
//     [0]=>
//     [
//         ["id"]=> "850",
//         ["menu_url"]=> "http://hayabusa3.open2ch.net/news",
//         ["menu_title"]=> "ニュース速報",
//         ["menu_defaultname"]=> "",
//         ["kind"]=> "open2ch.net",
//         ["menu_order"]=> 1
//     ],
//     [1]=>
//     [
//         ["id"]=> "840",
//         ["menu_url"]=> "http://uni.open2ch.net/newsplus",
//         ["menu_title"]=> "ニュース速報＋",
//         ["menu_defaultname"]=> "",
//         ["kind"]=> "open2ch.net",
//         ["menu_order"]=> 1
//     ],
//     [2]=>
//     [
//         ["id"]=> "1608",
//         ["menu_url"]=> "http://hayabusa.open2ch.net/news4vip",
//         ["menu_title"]=> "ニュー速ｖｉｐ",
//         ["menu_defaultname"]=> "",
//         ["kind"]=> "open2ch.net",
//         ["menu_order"]=> 1
//     ],
//     [3]=>
//     [
//         ["id"]=> "1612",
//         ["menu_url"]=> "http://anago.open2ch.net/neet4vip",
//         ["menu_title"]=> "ニー速",
//         ["menu_defaultname"]=> "",
//         ["kind"]=> "open2ch.net",
//         ["menu_order"]=> 1
//     ]
// ];

    if($isProc){

        $menuList  = getMenuListForProcOrder(getPDO(), $queryList["SELECT_MENU_FOR_PROCORDER"]);
        // var_dump($menuList);
        $menuData  = getProced(getPDO(), $menuList);

        if($isThreadSteal){
            $threadUrl = 
                getThreadBaseUrl
                (
                    $menuData["menu_url"],
                    getDiFix
                    (
                        getPDO(),
                        "threadListUrlMidfix",
                        $menuData["kind"],
                        $queryList["SELECT_DIFIX"]
                    ),
                    getDiRegex
                    (
                        getPDO(),
                        "threadBaseUrlRegex",
                        $menuData["kind"],
                        $queryList["SELECT_DIREGEX"]
                    )
                );

            $threadList =
                getThreadListUrl
                (
                    $menuData["menu_url"],
                    getDiFix
                    (
                        getPDO(),
                        "threadListUrlSuffix",
                        $menuData["kind"],
                        $queryList["SELECT_DIFIX"]
                    )
                );

            $ip = useIp(getPDO(),getUseIpMod(getPDO()));
            $encodiedThreadBaseHtml = threadSteal(getPDO(), $ip, "threadSteal", $threadList, $queryList["INSERT_IP_ACCESS"], getUa(getPDO(), $queryList["SELECT_UA"]), getHost($menuData["menu_url"]));

            $threadData =
                threadReArray(
                    threadDataPregMatch
                    (
                        $encodiedThreadBaseHtml,
                        getDiRegex
                        (
                            getPDO(),
                            "threadStealRegex",
                            $menuData["kind"],
                            $queryList["SELECT_DIREGEX"]
                        )
                    ),
                    $threadUrl,
                    $censorList
                );
            threadInsert(getPDO(), $threadData, $menuData["id"], $menuData["kind"], $queryList["INSERT_THREAD"]); 
            unset($threadData);
        }

        if($isResSteal){

            $threadData = getThreadData(getPDO(), $menuData["id"], $queryList["SELECT_THREAD"]);

            $i = 0;
            foreach ($threadData as $value) {
                if(($value["thread_now_res_no"] - $value["res_end"]) < (int)$fixedList["updateWaiting"]){
                    continue;
                }else{
                    timeWeit((int)$fixedList["weitTimeMin"], (int)$fixedList["weitTimeMax"]);
                } 
                $ip = useIp(getPDO(),getUseIpMod(getPDO()));
                $encodiedResBaseHtml = resSteal(getPDO(), $ip, "resSteal", $value["thread_url"], $queryList["INSERT_IP_ACCESS"],getUa(getPDO(), $queryList["SELECT_UA"]), getHost($menuData["menu_url"]));
                if(!$encodiedResBaseHtml){continue;}

                $isoldLog = threadIsOldLog
                    (
                        $encodiedResBaseHtml,
                        getDiRegex
                        (
                            getPDO(),
                            "threadOldlogedRegex",
                            $menuData["kind"],
                            $queryList["SELECT_DIREGEX"]
                        )
                    );

                if($isoldLog){
                    threadUpdateTois_oldlog($value["thread_sha"], $prepareStForTUIO, $dbHandle);
                }

            $resData  =
                getResArray
                (
                    getDiRegex(getPDO(), "resStealRegex", $menuData["kind"], $queryList["SELECT_DIREGEX"]),
                    $encodiedResBaseHtml,
                    $value["thread_sha"]
                );
                if(!$resData){continue;}

            $ankaerLinkProced =
                resBodyAnkaerLinkProc
                (
                    $resData,
                    getDiRegex(getPDO(), "resBodyAnkaerLinkStealRegex", $menuData["kind"], $queryList["SELECT_DIREGEX"])
                ); 

                if($menuData["kind"] === "open2ch.net"){
                    $imgLinkProced  =
                        resBodyImgLinkProcO(
                            getPDO(),
                            $ankaerLinkProced,
                            $value["thread_sha"],
                            getDiRegex(getPDO(), "resBodyImgLinkStealRegex", $menuData["kind"], $queryList["SELECT_DIREGEX"]),
                            $queryList["UPDATE_THREAD_IS_IMG"],
                            $fixedList["imgDirUrl"],
                            $menuData["kind"]
                        );
                }else{
                    $imgLinkProced  =
                        resBodyImgLinkProc(
                            getPDO(),
                            $ankaerLinkProced,
                            $value["thread_sha"],
                            getDiRegex(getPDO(), "resBodyImgLinkStealRegex", $menuData["kind"], $queryList["SELECT_DIREGEX"]),
                            $queryList["UPDATE_THREAD_IS_IMG"],
                            $fixedList["imgDirUrl"],
                            $menuData["kind"]
                        );


                }

                $censoredProced =
                    censorShipForRes
                    (
                        $imgLinkProced,
                        $censorList
                    );

                // $net_or_sc_kind = kindChecher($censoredProced[0]["res_id"]);
                $net_or_sc_kind = "null";

                $resDataArray = resDataReArray
                    (
                        $censoredProced,
                        $net_or_sc_kind,
                        $menuData["kind"]
                    );

                resInsert
                    (
                        getPDO(),
                        $resDataArray,
                        $menuData["kind"],
                        $queryList["INSERT_RES"]
                    );

                threadUpdateTonet_or_sc
                    (
                        getPDO(),
                        $net_or_sc_kind,
                        $value["thread_sha"],
                        $queryList["UPDATE_THREAD_NET_OR_SC"]
                    );

                $countOfResDataArray = count($resDataArray);//MAX RES number.

                threadUpdateToRes_end
                    (
                        getPDO(),
                        $value["thread_sha"],
                        $countOfResDataArray,
                        $queryList["UPDATE_THREAD_RES_END"]
                    ); 
                unset
                    (
                        $isoldLog,
                        $resData,
                        $resDataArray,
                        $censoredProced,
                        $imgLinkProced,
                        $ankaerLinkProced,
                        $encodiedResBaseHtml,
                        $resStealBool,
                        $countOfResDataArray,
                        $net_or_sc_kind
                    );
                $i++;
        }
        unset
            (
                $value,
                $threadData
            );
    }
}

memoryPeakUse("end");

timeTest("ProcEnd");
$proc_time = timeView();

procRecode(getPDO(), $menuData["id"], $menuData["menu_title"], $proc_time, $menuData["kind"], $tags["end"], $queryList["INSERT_PROC_RECODE"]);

procUnLocking(getPDO());
