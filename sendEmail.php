<?php
//SETUP
// ==== COMPOSER ====
// require 'vendor/autoload.php';

// ==== MANUAL PATH ====
require_once("references/sendgrid/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases

require_once("references/credentials.php");
//I added my apikey under references/credentials.php so I could add that to my .gitignore
$email = new \SendGrid\Mail\Mail(); 
$email->setFrom("noreply@company.com", "NoReply Company");
$email->setSubject("Test API");
$email->addTo("riley.deleacy@company.com");
$email->addTo("riley.deleacy@companyone.com.au","Riley De Leacy");
$email->addBcc("test@email.com");
$email->addCc("test1@email.com");
$email->addAttachment(file_get_contents("tests/test_image.jpg"),mime_content_type("tests/test_image.jpg"),"test_image.jpg","attachment");
//You can only add one set of content as it will overwrite the previous but this demos adding text and html
// $email->addContent("text/plain", "This is a test email");
$email->addContent("text/html", "<p style='color:green'>This is some html</p>");
$sendgrid = new \SendGrid($apiKey);
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '. $e->getMessage() ."\n";
}
?>