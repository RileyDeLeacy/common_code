<?php
class azureStorageManager{
    private $key;
    private $accountname;
    private $container;
    
    public function __construct($key,$accountname,$container){
        $this->key = $key;
        $this->accountname = $accountname;
        $this->container = $container;
    }
    public function getKey(){
        return $this->key;
    }
    public function setKey($newKey){
        $this->key = $newKey;
    }
    public function getAccountName(){
        return $this->accountname;
    }
    public function setAccountName($newAccountName){
        $this->accountname = $newAccountName;
    }
    public function getContainer(){
        return $this->container;
    }
    public function setContainer($newContainer){
        $this->container = $newContainer;
    }

    /**
     * Uploads a file to an azure bucket
     * @param String $filetoUpload path to file
     * @param String $blobName Blobs new name prefixes with the file path e.g. images/test_image.png
     * @return Boolean true if the upload was successful, false otherise
     */
    public function uploadLocalBlob($filetoUpload, $blobName, $local) {
        $destinationURL = "https://$this->accountname.blob.core.windows.net/$this->container/$blobName";
        $currentDate = gmdate("D, d M Y H:i:s T", time());
        if($local){
            $handle = fopen($filetoUpload, "r");
            $fileLen = filesize($filetoUpload);
            $contentType = mime_content_type($filetoUpload);
        }else{
            $fileContents = file_get_contents($filetoUpload);
            $handle = tmpfile();
            fwrite($handle,$fileContents);
            $fileLen = fstat($handle)['size'];
            $contentType = mime_content_type($handle);
        }
        
        $headerResource = "x-ms-blob-cache-control:max-age=3600\nx-ms-blob-type:BlockBlob\nx-ms-date:$currentDate\nx-ms-version:2019-12-12";
        $urlResource = "/$this->accountname/$this->container/$blobName";

        //need all of these headers even if they're null as specified in the documentation here:
        //https://docs.microsoft.com/en-us/rest/api/storageservices/authorize-with-shared-key

        $arraysign = array();
        $arraysign[] = 'PUT';               /*HTTP Verb*/
        $arraysign[] = '';                  /*Content-Encoding*/
        $arraysign[] = '';                  /*Content-Language*/
        $arraysign[] = $fileLen;            /*Content-Length (include value when zero)*/
        $arraysign[] = '';                  /*Content-MD5*/
        $arraysign[] = $contentType;         /*Content-Type*/
        $arraysign[] = '';                  /*Date*/
        $arraysign[] = '';                  /*If-Modified-Since */
        $arraysign[] = '';                  /*If-Match*/
        $arraysign[] = '';                  /*If-None-Match*/
        $arraysign[] = '';                  /*If-Unmodified-Since*/
        $arraysign[] = '';                  /*Range*/
        $arraysign[] = $headerResource;     /*CanonicalizedHeaders*/
        $arraysign[] = $urlResource;        /*CanonicalizedResource*/

        //we still need the new line character even if the header option is null
        $str2sign = implode("\n", $arraysign);

        //Hash-based Message Authentication Code (HMAC) constructed from the request and computed by using the
        //SHA256 algorithm, and then encoded by using Base64 encoding
        $sig = base64_encode(hash_hmac('sha256', urldecode(utf8_encode($str2sign)), base64_decode($this->key), true));
        $authHeader = "SharedKey $this->accountname:$sig";

        $headers = [
            'Authorization: ' . $authHeader,
            'x-ms-blob-cache-control: max-age=3600',
            'x-ms-blob-type: BlockBlob',
            'x-ms-date: ' . $currentDate,
            'x-ms-version: 2019-12-12',
            'Content-Type: ' . $contentType,
            'Content-Length: ' . $fileLen
        ];

        $ch = curl_init($destinationURL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_INFILE, $handle);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileLen);
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        $response = curl_exec($ch);
        if($response === false){
            echo 'Curl error: ' . curl_error($ch) . " On File: " . $blobName . "\n";
            return false;
        }else{
            // echo 'Operation completed without any errors';
        }
        // echo $response;
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        if (isset($error_msg)) {
            echo 'Curl error: ' . $error_msg . " On File: " . $blobName . "\n";
            return false;
        }
        curl_close($ch);
        return true;
    }
    /**
     * returns an array of arrays containing the name of files and their properties. This method will return over the
     * 5000 limit imposed by azure
     * @return Array[Array] nested arrays of format Array('Name'=>,'Properties'=>Array(),'OrMetadata'=>Array())
     */
    public function listBlobs(){
        $destinationURL = "https://$this->accountname.blob.core.windows.net/$this->container?comp=list&restype=container";
        return $this->privateListBlobs($this->accountname,$destinationURL,$this->key,$this->container,null,Array());
    }
    /**
     * This is recursive method designed to sidestep the 5000 files limit imposed by azure. The way it works is the 
     * method runs, if there are more than 5000 files it will return a marker. If a marker is present the method will
     * call itself passing the return from the first iteration and the marker into itself. listBlobs is a helper method 
     * that allows users to call this method without knowledge of the recursive markers.
     * @param String $storageAccount account name
     * @param String $destinationURL target destingation of format https://<accountname>.blob.core.windows.net/<container>?comp=list&restype=container
     * @param String $accesskey storage accounts access key
     * @param String $containerName name of target container
     * @param String $nextMarker marker provided by azure to indicate the next recursive call. Leave null on the firs call.
     * @param Array $responseArray current list items. Leave as empty array on first call
     */
    private function privateListBlobs($storageAccount, $destinationURL, $accesskey, $containerName, $nextMarker=null, $responseArray = Array()) {
        $currentDate = gmdate("D, d M Y H:i:s T", time());    
        $version = "2019-12-12";
        // $version = "2009-09-19";
        $headerResource = "x-ms-date:$currentDate\nx-ms-version:$version";
        $urlResource = isset($nextMarker)?"/$storageAccount/$containerName\ncomp:list\nmarker:$nextMarker\nrestype:container":
        "/$storageAccount/$containerName\ncomp:list\nrestype:container";
        // $urlResource = "/$storageAccount/$containerName\ncomp:list\nrestype:container";

        //need all of these headers even if they're null as specified in the documentation here:
        //https://docs.microsoft.com/en-us/rest/api/storageservices/authorize-with-shared-key

        $arraysign = array();
        $arraysign[] = 'GET';               /*HTTP Verb*/
        $arraysign[] = '';                  /*Content-Encoding*/
        $arraysign[] = '';                  /*Content-Language*/
        $arraysign[] = '';            /*Content-Length (include value when zero)*/
        $arraysign[] = '';                  /*Content-MD5*/
        $arraysign[] = '';         /*Content-Type*/
        $arraysign[] = '';                  /*Date*/
        $arraysign[] = '';                  /*If-Modified-Since */
        $arraysign[] = '';                  /*If-Match*/
        $arraysign[] = '';                  /*If-None-Match*/
        $arraysign[] = '';                  /*If-Unmodified-Since*/
        $arraysign[] = '';                  /*Range*/
        $arraysign[] = $headerResource;     /*CanonicalizedHeaders*/
        $arraysign[] = $urlResource;        /*CanonicalizedResource*/

        //we still need the new line character even if the header option is null
        $str2sign = implode("\n", $arraysign);

        //Hash-based Message Authentication Code (HMAC) constructed from the request and computed by using the
        //SHA256 algorithm, and then encoded by using Base64 encoding
        $sig = base64_encode(hash_hmac('sha256', urldecode(utf8_encode($str2sign)), base64_decode($accesskey), true));
        $authHeader = "SharedKey $storageAccount:$sig";

        $headers = [
            'Authorization: ' . $authHeader,
            'x-ms-date: ' . $currentDate,
            'x-ms-version: ' . $version,
            'Content-Length: 0'
            // 'marker: 4999'
        ];

        $ch = curl_init($destinationURL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        // curl_setopt($ch, CURLOPT_HEADER, true);
        $content = curl_exec($ch);
        if($content === false){
            echo 'Curl error: ' . curl_error($ch)."<br>";
        }else{
            // echo 'Operation completed without any errors<br>';
        }
        // $response = curl_getinfo($ch);
        // echo $content;
        $xml = simplexml_load_string($content);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        // print_r($array);
        curl_close($ch);
        if(isset($array['NextMarker'])&&$array['NextMarker']!=""&&!is_array($array['NextMarker'])){
            return $this->privateListBlobs($storageAccount, $destinationURL."&marker=".$array['NextMarker'], $accesskey, $containerName, $array['NextMarker'], array_merge($responseArray,$array['Blobs']['Blob']));
            // echo "Next Marker is Set!";
        }
        return array_merge($responseArray,isset($array['Blobs']['Blob'])?$array['Blobs']['Blob']:Array());
        // return $responseArray;
    }

}


?>