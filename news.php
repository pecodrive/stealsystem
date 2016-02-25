<?php
session_start();
if (!isset($_SESSION["USERID"])) {
    header("Location: logout.php");
    exit;
}

require_once(dirname(dirname(dirname(__file__))) . '/functions.php');
// require_once(dirname(__file__) . '/functions.php');
require_once(dirname(dirname(__file__)) . '/wp-load.php');
date_default_timezone_set('Asia/Tokyo');


$queryList       = getSQLQuery(getPDO());
$censorList      = getCensorList(getPDO(), $queryList["SELECT_CENSOR"]);
$regexList       = getRegex(getPDO(), $queryList["SELECT_REGEX"]);
$fixedList       = getFix(getPDO(), $queryList["SELECT_FIX"]); 
$procLockData    = getProcLockData(getPDO(), $queryList["SELECT_PROCLOCK"]);
$threadData      = getThreadDataAll(getPDO(), $queryList["SELECT_THREAD_FOR_NEWS"]);
$threadDataCount = count($threadData);
$nonce           = wp_create_nonce();
$error           = getError(getPDO(),$queryList["SELECT_ERROR"]);

?>

<!DOCTYPE html>
<html>
<head>
<title>StealSystem</title>
<meta name="viewport" content="width=device-width">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="<?php echo $fixedList["scriptUrl"]; ?>/js/matome.js"></script>
<link rel="stylesheet" type="text/css" href="style.css"> 
<script>
<?php
$threadDataCount = count($threadData);
echo "var global ={\n";
echo "threadSwith : {\n";
for ($i=0; $i < $threadDataCount; $i++) {
    if($i < $threadDataCount - 1){
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":false,\n";
    }else{
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":false\n";
    }
}
echo "},\n";
echo "choiced : {\n";
for ($i=0; $i < $threadDataCount; $i++) {
    if($i < $threadDataCount - 1){
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":{},\n";
    }else{
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":{}\n";
    }
}
echo "},\n";
$args = array(
    'type'                     => 'post',
    'taxonomy'                 => 'category',
); 
$category = get_categories($args);
echo "category : {\n";
foreach ($category as $item) {
    echo "\"{$item->name}\"". ":{$item->term_id},\n";
}
echo "},\n";
echo "categorychoiced : {\n";
for ($i=0; $i < $threadDataCount; $i++) {
    if($i < $threadDataCount - 1){
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":[],\n";
    }else{
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":[]\n";
    }
}
echo "},\n";
echo "deleted : {\n";
for ($i=0; $i < $threadDataCount; $i++) {
    if($i < $threadDataCount - 1){
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":false,\n";
    }else{
        echo "\"{$threadData[$i]["thread_sha"]}\"". ":false\n";
    }
}
echo "},\n";
echo "scriptUrl : \"{$fixedList["scriptUrl"]}/\",\n";
echo "nonce : \"{$nonce}\"\n";
echo "}";
?>
</script>
</head>
<body>
<div><?php echo "ニュース系"; ?></div>
<div id="thread">
<?php 
$countThreadData = count($threadData);
foreach ($threadData as $item) {

    static $j = 1;//As res number.

    $resData    = getResData(getPDO(), $item["thread_sha"], $queryList["SELECT_RES"]); 
    $endResDate = array_pop($resData);

    echo "<div class=\"{$item["thread_sha"]} threadDiv\">";
    $kind = "{$item["kind"]} {$item["net_or_sc"]}";
    $item["is_img"] ? $isimg = "【画像あり】" : $isimg = "";
    echo "<span class=\"kind\">{$kind} </span>";
    echo "<span class=\"threadTitle {$item["thread_sha"]}\">" . "<span>{$j} : </span><span><font style=\"font-weight:900;color:#999933;\">{$isimg}</font></span><font style=\"font-weight:900;color:#339933;\">{$item["thread_title"]}</font>" . "(" . $item["thread_now_res_no"] . ")";
    echo "</span><br>";

    echo "<span class=\"{$item["thread_sha"]} endres\">";
    $item["is_oldlog"] ? $oldlog = "【過去ログ入り】" : $oldlog = "";
    $item["datetime"] ? $date = " 取得日時 ".$item["datetime"] : $date = "";
    $endResDate["res_date"] ? $endDate = $endResDate["res_date"] : $endDate = "";
    $endResDate["res_clock"] ? $endClock = $endResDate["res_clock"] : $endClock = "";
    echo "<font style=\"font-weight:900;color:#ff3333;\">{$oldlog}</font>";
    echo "<font style=\"font-weight:900;color:#dd3366;\"> 最終レス更新 {$endDate}{$endClock}</font>";
    echo "<font style=\"font-weight:900;color:#333366;\">{$date}</font>";
    echo "</span>";
    echo "<span class=\"{$item["thread_sha"]} botton\">";
    echo "<span class=\"{$item["thread_sha"]} deleted\">delete</span>";
    echo "<span class=\"{$item["thread_sha"]} submited\">送信</span>";
    echo "<span class=\"{$item["thread_sha"]} suspend\">保留</span>";
    echo "</span>";
    echo "<span class=\"reTitle\"><input type='text' name='{$item["thread_sha"]}' size=40 value='{$item["thread_title"]}'></span><br>";
    echo "<span class=\"{$item["thread_sha"]} responcewp\"></span>";
    echo "</div>";
    $j++;
}
?>
</div>
</body>
</html>
