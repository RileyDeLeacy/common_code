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
    if($text==null||$text==""){
        return "";
    }
    if($delimiter==null||$delimiter==""){
        return $text;
    }
    $splitstring = explode($delimiter,$text);
    $tempArray=Array();
    for($i=0;$i<$nth;$i++){
        if(!isset($splitstring[$i])){
        break;
        }
        array_push($tempArray,$splitstring[$i]);
    }
    return implode($delimiter,$tempArray);
}

/**
 * prints an associatve array of unique values from $data which their mapped value
 * as the number of occurances in $data
 * @param Array $data input array
 **/
function pullColumnOccurances($data){
    $occurances = Array();
    for($i=0;$i<count($data);$i++){
        if(isset($data[$i])){
            if(!isset($occurances[trim($data[$i])])){
                $occurances[trim($data[$i])]=1;
            }else{
                $occurances[trim($data[$i])]++;
            }
        }
    }
    print_r($occurances);
}

/**
 * null safe check to determine if $input2 is within $margin% of $input1
 * @param Double/Int $input1 number to be compared against
 * @param Double/Int $input2 number to compare
 * @param int $margin % accectable error
 * @return Boolean returns true if $input2 is within $margin% of $input1 or true if both inputs are null
 **/
function withinMargin($input1,$input2,$margin) {
    return $input1==null?$input2==null:$input1<=($input2*(1+$margin/100))&&$input1>=($input2*(1-$margin/100));
}

/**
 * sanitizes and formats an input to safely use in database queries
 * @param String value to sanitize
 * @param mysqli a valid mysqli connection
 * @return String sanitized input value with leading and following double quotations
 **/
function formatValue($value,$mysqli_connection){
    return ($value==""||$value==null||!isset($value))?"NULL":"\"".$mysqli_connection->real_escape_string($value)."\"";
}
?>