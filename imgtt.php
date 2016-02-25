<?php
// require_once(dirname(__file__) . '/files.php');
// function sizeSetTumb($inputImgSize, $img) {
//     $maxwidthsize = 150;
//     if($inputImgSize[0] > $inputImgSize[1]){
//         $rasio = $maxwidthsize / $inputImgSize[1]; 
//         $w = (int)round($inputImgSize[0] * $rasio, 0);
//         $h = (int)round($inputImgSize[1] * $rasio, 0);
//         $diff = ($w - 150)*0.5;
//         $newImg = imagecreatetruecolor($w, $h);
//         imagecopyresampled($newImg, $img, 0, 0, 0, 0, $w, $h, $inputImgSize[0], $inputImgSize[1]);
//         $newImg150 = imagecreatetruecolor(150, 150);
//         imagecopyresampled($newImg150, $newImg, 0, 0, (int)$diff, 0, 150, 150, 150, 150);
//         return $newImg150; 
//     }else{
//         $rasio = $maxwidthsize / $inputImgSize[0]; 
//         $w = round($inputImgSize[0] * $rasio);
//         $h = round($inputImgSize[1] * $rasio);
//         $diff = ($h - 150)*0.5;
//         $newImg = imagecreatetruecolor($w, $h);
//         imagecopyresampled($newImg, $img, 0, 0, 0, 0, (int)$w, (int)$h, $inputImgSize[0], $inputImgSize[1]);
//         $newImg150 = imagecreatetruecolor(150, 150);
//         imagecopyresampled($newImg150, $newImg, 0, 0, 0, (int)$diff, 150, 150, 150, 150);
//         return $newImg150; 
//     }
// }
// function sizeSet($inputImgSize, $img) {
//     $maxwidthsize = 1000;
//     if($inputImgSize[0] > $maxwidthsize){
//         $rasio = $maxwidthsize / $inputImgSize[0]; 
//         $w = $inputImgSize[0] * $rasio;
//         $h = $inputImgSize[1] * $rasio;
//         $newImg = imagecreatetruecolor($w, $h);
//         imagecopyresampled($newImg, $img, 0,0,0,0, $w, $h, $inputImgSize[0], $inputImgSize[1]);
//         return $newImg; 
//     }else{
//         return $img;
//     }
// }
//
//
// // foreach ($files as $filename) {
// // $filename = "2_0_57e32495b5695bab99e9e225f460d256378a2b3e.jpg";
// // $filename = "6_0_bb70d4bed71e8db1d51b67255200fa99be8583a1.jpg";
// $filename = "1_0_df0a66871a6815e404a5356938ae821ce0be9a5a.jpg";
// $url = "http://omoro.top/wp-content/uploads/".$filename;
// var_dump($url);
// $savePath = "/var/www/html/wp-content/uploads/".$filename;
// $dir = "/var/www/html/wp-content/uploads/";
// $savePathTumb = $dir."tumb150_".$filename;
//     $inputImgSize = getimagesize($url);
//     if($inputImgSize !== false){
//         if($inputImgSize["mime"] === "image/jpeg"){
//             $tumbInputImgSize = getimagesize($savePath);
//             $imgTumb = imagecreatefromjpeg($dir.$filename);
//             $newImgTumb = sizeSetTumb($tumbInputImgSize, $imgTumb);
//             imagejpeg($newImgTumb, $savePathTumb, 50);
//             imagedestroy($newImgTumb);
//         }elseif($inputImgSize["mime"] === "image/png"){
//             $tumbInputImgSize = getimagesize($savePath);
//             $imgTumb = imagecreatefrompng($dir.$filename);
//             $newImgTumb = sizeSetTumb($tumbInputImgSize, $imgTumb);
//             imagejpeg($newImgTumb, $savePathTumb, 50);
//             imagedestroy($newImgTumb);
//         }elseif($inputImgSize["mime"] === "image/gif"){
//             $tumbInputImgSize = getimagesize($savePath);
//             $imgTumb = imagecreatefromgif($dir.$filename);
//             $newImgTumb = sizeSetTumb($tumbInputImgSize, $imgTumb);
//             imagegif($newImgTumb, $savePathTumb);
//             imagedestroy($newImgTumb);
//         }
//     }
// // }
// ?>
//     <img src="http://omoro.top/wp-content/uploads/<?php echo $filename;?>">
//     <img src="http://omoro.top/wp-content/uploads/<?php echo "tumb150_".$filename;?>">
