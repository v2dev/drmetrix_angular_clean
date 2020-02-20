<?php
if(php_sapi_name() != 'cli') {
  echo 'Script cannot be exeuted from GUI';
  exit;
}
//Cron is in used, runs at 6 PM for frequnecy daily and on monday 6 PM for frequency weeklt
require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../queries.php';
require_once dirname(__FILE__) . '/../functions.php';
require_once dirname(__FILE__) . '/./email_template.php';
require_once dirname(__FILE__) . '/../PHPMailer/class.phpmailer.php';

ignore_user_abort();
set_time_limit(0);

$users      = get_all_tracking_alert_subscribers();

if(php_sapi_name() == 'cli') {
  if (!isset($argv[1])) {
    echo 'frequency not set correctly';
    exit;
  }
  
  parse_str($argv[1], $params);
  $frequency  = $params['frequency'];
} else {
  echo 'Script cannot be exeuted from GUI';
  exit;
  $frequency  = $_GET['frequency'];
}
//for testing purpose
// $email_content  = alert_email('ashwini.rewatkar@v2solutions.com', '669', $frequency);
// echo $email_content;
// exit;
foreach ($users as $user) {
  $user_name      = $user['first_name']. ' '. $user['last_name'];
  $user_id        = $user['user_id'];
  $user_email     = $user['email'];

  $email_content  = alert_email($user_name, $user_id, $frequency);
  
  if ($email_content == '') {
    continue;
  }
  
  $subject    = 'Tracking alert email ';
  $from_name  = FROM_NAME;
  $from_email = FROM_EMAIL;
  
  $to_name    = $user_name;
  $to_email   = $user_email;
  
  $body     = $email_content;
  $body     = wordwrap($body, 50);

  $mail     = new PHPMailer(); //New instance, with exceptions enabled
  $mail->IsSendmail();  // tell the class to use Sendmail            
  $mail->SetFrom($from_email, $from_name);
  //$mail->addCustomHeader('MIME-Version: 1.0');
  //$mail->addCustomHeader('Content-Type: text/html; charset=ISO-8859-1');
  $mail->AddAddress($to_email, $to_name);
  $mail->Subject  = $subject;
  $mail->MsgHTML($body);
  $mail->MsgHTML(wordwrap($body, 50));
  $mail->IsHTML(true); // send as HTML
  
  $tracking_email_recipients_array = explode(",", TRACKING_EMAIL_RECIPIENTS);

  foreach ($tracking_email_recipients_array as $email_address) {
      $email_address = trim($email_address);
      $mail->addBCC($email_address, $email_address);
  }

  //echo $body;
  if ($mail->Send()) {
    echo 'email sent';
  } else {
    echo 'error while sending an email';
  }
  sleep(5);
}