<?php
require_once(dirname(__file__) . "/functions.php");

$queryList      = getSQLQuery(getPDO());

$html = file_get_contents("http://anago.open2ch.net/test/read.cgi/dqnplus/1428717252");
var_dump($html);
preg_match_all(
    getDiRegex(getPDO(), "resStealRegex", "open2ch.net", $queryList["SELECT_DIREGEX"]),
    $html,
    $match
);

var_dump($match);
