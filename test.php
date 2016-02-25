<?php
$bind = array("socket"=>array("bindto"=>"133.130.96.221"));
$context = stream_context_create($bind);
$html = file_get_contents("http://open2ch.net/",false, $context);
// $html = file_get_contents("http://omoro.top/");
var_dump($html);
// function youtube($resBody){
//     $reTagBody = $resBody;
//     $bool = preg_match_all("/<img\srel=\"nofollow\"\swidth=[0-9]+\sheight=[0-9]+\ssrc=https?:\/\/img.youtube.com\/.+>[<br>]*\s*[<br>]*<a\sonMouseOver=\"funcYoutube\(this\)\"\starget=_blank\shref=\'https?:\/\/youtu\.be+\/(.{1,11})\'\svid=\".+\"\sclass=youtube>[<br>]*https?:\/\/youtu\.be\/.{1,11}[<br>]*<\/a>/", $reTagBody, $match, PREG_SET_ORDER);
//     if($bool){
//         $countMatch = count($match);
//         for ($i=0; $i < $countMatch; $i++) {
//             $embedTag = "<div class=\"youtube\" style=\"width:200px;height:140px\"><iframe width=100% height=100% src=\"https://www.youtube.com/embed/{$match[$i][1]}\" frameborder=\"0\" allowfullscreen></iframe></div>";
//             $quotedImgLink    = "/" . preg_quote($match[$i][0],"/") . "/";
//             $reTagBody        = preg_replace($quotedImgLink, $embedTag, $reTagBody);
//         }
    // unset($match);
    // }
    //
    // $bool2 = preg_match_all("/<a\srel=\"nofollow\"\shref=\"https?:\/\/www\.youtube\.com\/watch\?.*v=(.{1,11})\"\starget=\"_blank\">[<br>]*https?:\/\/www\.youtube\.com\/watch\?.*v=.{1,11}[<br>]*<\/a>/", $reTagBody, $match, PREG_SET_ORDER);
    // if($bool2){
    //     $countMatch = count($match);
    //     for ($i=0; $i < $countMatch; $i++) {
    //         $embedTag = "<div class=\"youtube\" style=\"width:250px;height:180px\"><iframe width=100% height=100% src=\"https://www.youtube.com/embed/{$match[$i][1]}\" frameborder=\"0\" allowfullscreen></iframe></div>";
    //         $quotedImgLink    = "/" . preg_quote($match[$i][0],"/") . "/";
    //         $reTagBody        = preg_replace($quotedImgLink, $embedTag, $reTagBody);
//         }
//     unset($match);
//     }
//     return $reTagBody;
// }
//
// $html = file_get_contents("./matchtext.txt");
// $youtube = youtube($html);
// echo "<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">";
// var_dump($youtube);
