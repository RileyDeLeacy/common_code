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
/**
 * determines if a phrase occurs in a string, the search is case sensative
 * @param String phrase to search for
 * @param String string to be searched
 * @return Boolean returns true if the input phrase occurs within the input string
 **/
function instr($phrase,$string){
    return strpos($string,$phrase) !== false;
}
/**
 * if json_last_error returns 4 for syntax error there maybe some hidden characters ruining your JSON format
 * I copied this from kris's post here https://stackoverflow.com/questions/17219916/json-decode-returns-json-error-syntax-but-online-formatter-says-the-json-is-ok
 * @param String json string
 * @param String sanitised JSON string
 **/
function sanitiseJSON($data){
    // This will remove unwanted characters.
    // Check http://www.php.net/chr for details
    for ($i = 0; $i <= 31; ++$i) { 
        $data = str_replace(chr($i), "", $data); 
    }
    $data = str_replace(chr(127), "", $data);
    
    // This is the most common part
    // Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
    // here we detect it and we remove it, basically it's the first 3 characters 
    if (0 === strpos(bin2hex($data), 'efbbbf')) {
       $data = substr($data, 3);
    }
    return $data;
}
?>