<?php
function customZeroFill($value,$length){
    $stringBuilder = $value;
    for($i=0+strlen($value);$i<$length;$i++){
        $stringBuilder="0".$stringBuilder;
    }
    return $stringBuilder;
}
function splitn($text,$delimiter,$nth){
    $splitstring = explode($delimiter,$text);
    $tempArray=Array();
    for($i=0;$i<$nth;$i++){
        array_push($tempArray,$splitstring[$i]);
    }
    return implode("-",$tempArray);
}

?>