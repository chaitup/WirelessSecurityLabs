<?php
function mymail($email, $subject, $content){

require_once('class.phpmailer.php');
require_once('PHPMailerAutoload.php');
// Send mail
$mail = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
// SMTP Configuration
$mail->SMTPAuth = true;                  // enable SMTP authentication
$mail->Host       = "smtp.gmail.com";      
$mail->Port       = 465;                   
$mail->SMTPSecure = "ssl";       
$mail->Username = "wirelessecurelabs@gmail.com";
$mail->Password = "mynewpass";            
//$mail->Port = 465; // optional if you don't want to use the default 

$mail->From = "wirelessecurelabs@gmail.com";
$mail->FromName = "wireless security labs";
$mail->Subject = $subject;
$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
$mail->MsgHTML("<!DOCTYPE html><html lang='en'><head><meta charset='utf-8'><style>*{margin:0px;padding:0px;box-sizing:border-box;}</style></head><body><div style='width:100%;color:white;background:#2C3E50;padding:10px;text-align: center;'><h1>Docs Share</h1></div><div style='width:100%;color:black;background:#F4F4F4;padding:22px;font-size:18px'>".$content."</div><div style='padding:5px;width:100%;color:white;background:#2C3E50;text-align:center;'>Copyright © Wireless Security Labs 2016</div></div></div></body></html>" );

// Add as many as you want
$mail->AddAddress($email, 'tcs mailid');

// If you want to attach a file, relative path to it
//$mail->AddAttachment("images/phpmailer.gif");             // attachment
$mail->SMTPDebug = 1;
$response= NULL;
if(!$mail->Send()) {
    $response = "Mailer Error: " . $mail->ErrorInfo;
} else {
    $response = "Message sent!";
}
header('content-type: application/json; charset=utf-8');
echo 'success';
header('Location: '.'admin-addusers.html?page=1');
}
?>