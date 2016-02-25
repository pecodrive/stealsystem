<?php
require_once(dirname(dirname(dirname(__file__))) . '/functions.php');
$queryList           = getSQLQuery(getPDO());
$menuList  = getMenuListForProcOrder(getPDO(), $queryList["SELECT_MENU_FOR_PROCORDER"]);
$menuData  = getProced(getPDO(), $menuList);
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
var_dump($menuData);
var_dump(getUa(getPDO(),$queryList["SELECT_UA"]));
var_dump(getHost($menuData["menu_url"]));
$html = threadSteal(getPDO(), "133.130.96.221", "threadSteal", $threadList, $queryList["INSERT_IP_ACCESS"], getUa(getPDO(),$queryList["SELECT_UA"]), getHost($menuData["menu_url"]));
var_dump($html);
