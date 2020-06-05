<?php
/**
 * appends zeros to the start of input $value resulting in a string of length $length
 * @param String $value input value to be zero filled.
 * @param Int $length desired length
 * @return String returns $value zero filled to length $length
 **/
function customZeroFill($value,$length){
    $stringBuilder = $value;
    for($i=0+strlen($value);$i<$length;$i++){
        $stringBuilder="0".$stringBuilder;
    }
    return $stringBuilder;
}
/**
 * splits a string by the nth delimiter
 * @param String $text text to be split 
 * @param String $delimiter delimiter to be split by
 * @param Int $nth number of delimiters to include
 * @return String returns the input string up intil the $nth occurance of $delimiter
 **/
function splitn($text,$delimiter,$nth){
    $splitstring = explode($delimiter,$text);
    $tempArray=Array();
    for($i=0;$i<$nth;$i++){
        array_push($tempArray,$splitstring[$i]);
    }
    return implode("-",$tempArray);
}

?>