<?php
$array = [1=>[],2=>[],3=>[],4=>[2],5=>[],6=>[],7=>[6],8=>[],9=>[],10=>[6],11=>[],12=>[8],13=>[10],14=>[],15=>[10],16=>[13]];

function sortResByAnkaer($array){
    $countOfArray = count($array);
    $reArray = [];
    for ($i=1; $i < $countOfArray + 1; $i++) {
        if($array[$i]){
            $lengthOfReArray = count($reArray);
            $reArrayIndex    = array_search($array[$i][0], $reArray);
            //配列を挿入位置で分離
            $upper           = array_slice($reArray, 0, $reArrayIndex + 1) ; 
            $bottom          = array_slice($reArray, $reArrayIndex + 1, $lengthOfReArray - $reArrayIndex); 
            //アッパー配列のカウント　->元の配列の要素を探す用
            $lengthOfUpper   = count($upper);
            //もしもとの配列の要素が配下を持っているなら…
            if(!$array[$upper[$lengthOfUpper - 1]]){
                // さらに挿入しようとしている要素と、一つ前にある要素は同じ親を持っているかどうか
                if( $array[$upper[$lengthOfUpper - 1]] < $array[$i]){
                    //もし同じ親を持っていたら、小さいレス番の方が前に来るように並び替えをする
                    //小さいレス番が最後になるよう配列を再分割
                    $upper   = array_slice($reArray, 0, $reArrayIndex + 2) ; 
                    $bottom  = array_slice($reArray, $reArrayIndex + 2, $lengthOfReArray - $reArrayIndex); 
                    //突っ込む
                    array_push($upper, $i);
                }
            }else{
                array_push($upper, $i);
            }
            $reArray         = array_merge($upper,$bottom);
        }else{
            if(!array_search($i, $reArray)){
                array_push($reArray, $i);
            }
        }
        return $reArray;
    }
}
