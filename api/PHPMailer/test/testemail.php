<?php
/**
* Simple example script using PHPMailer with exceptions enabled
* @package phpmailer
* @version $Id$
*/

require '../class.phpmailer.php';

//file_put_contents("/tmp/testcron.txt",'testing');

try {
	$mail = new PHPMailer(true); //New instance, with exceptions enabled

	$body             = file_get_contents('contents.html');
	$body             = preg_replace('/\\\\/','', $body); //Strip backslashes

	$mail->IsSMTP();                           // tell the class to use SMTP
    $mail->Mailer = "smtp";
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->Port       = 587;                    // set the SMTP server port
	//$mail->Port       = 465;                    // set the SMTP server port
        $mail->SMTPSecure = 'tls';
	$mail->Host       = "tcp://smtp.gmail.com"; // SMTP server
	$mail->Username   = "pravingalaxys3@gmail.com";     // SMTP server username
	$mail->Password   = "pravin125";            // SMTP server password

	//$mail->IsSendmail();  // tell the class to use Sendmail

	$mail->AddReplyTo("pravingalaxys3@gmail.com","First Last");

	$mail->From       = "pravingalaxys3@gmail.com";
	$mail->FromName   = "Prasad Chaugule";

	$to = "pravin.sapkal@v2solutions.com";
	// $to = "smartparsu@gmail.com";

	$mail->AddAddress($to);

	$mail->Subject  = "Second PHPMailer Message";

	$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
	$mail->WordWrap   = 80; // set word wrap

	$mail->MsgHTML($body);

	$mail->IsHTML(true); // send as HTML

	$mail->Send();
	echo 'Message has been sent.';
} catch (phpmailerException $e) {
	echo $e->errorMessage();
}
?>
