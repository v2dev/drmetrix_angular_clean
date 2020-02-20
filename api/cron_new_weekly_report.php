<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/PHPMailer/class.phpmailer.php';

ignore_user_abort();
updateWeeklyRetailReportInAdspher();


function curl_param(){
  $param = array();
  $param['url'] = "https://crm.zoho.com/crm/private/json/Contacts/searchRecords";
  $param['query'] = "authtoken=".ZOHO_APIKEY."&scope=crmapi&criteria=(New Weekly Report:Yes)&selectColumns=Contacts(Email,First Name,Last Name,New Weekly Report,Client,Account Name)&fromIndex=1&toIndex=".ZOHOMAXINDEX;
  return $param; 
}

function curl_param_account(){
  $param = array();
  $param['url'] = "https://crm.zoho.com/crm/private/json/Accounts/searchRecords";
  $param['query'] = "authtoken=".ZOHO_APIKEY."&scope=crmapi&criteria=(Client:true)&selectColumns=Contacts(Account Name,Client)&fromIndex=1&toIndex=".ZOHOMAXINDEX;
  return $param; 
}

function process_curl($url, $query){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $response_info = curl_getinfo($ch);
  $response = curl_exec($ch);
  curl_close($ch);
  $result = json_decode($response, true);
  return $result;
}

function updateWeeklyRetailReportInAdspher(){
  //if(check_weekly_mail_date() && ($_SERVER['SHELL'] == '/bin/sh' ||$_SERVER['SHELL'] == '/bin/bash') ){
  $params = curl_param_account();
  $result = process_curl($params['url'], $params['query']);    
  if(isset($result['response']['result']['Accounts']['row']) || !empty($result['response']['result']['Accounts']['row'])){
    $params = curl_param();
    $contacts = process_curl($params['url'], $params['query']);
    $comp = array();
    if(isset($contacts['response']['result']['Contacts']['row']) || !empty($contacts['response']['result']['Contacts']['row'])){
      foreach ($result['response']['result']['Accounts']['row'] as $key => $value) {
        foreach ($value['FL'] as $ky => $val) {
          if($val['val'] == 'Account Name'){
            $comp[] = $val['content'];
          }
        }
      }
      $user_data = '';
      foreach ($contacts['response']['result']['Contacts']['row'] as $keyC => $valueC) {
        //$contact_id = $fname = $lname = $email = $new_weekly_report = $client = $comp_name =  '';
        foreach ($valueC['FL'] as $ky => $valc) {    
              if($valc['val'] == 'CONTACTID' ){
                $contact_id =  $valc['content'];
              }else if($valc['val'] == 'First Name'){
                $fname = $valc['content'];
              }else if($valc['val'] == 'Last Name'){
                $lname = $valc['content'];
              }else if($valc['val'] == 'Email'){
                $email = $valc['content'];
              }else if($valc['val'] == 'New Weekly Report'){
                $new_weekly_report = $valc['content'];
              }else if( $valc['val'] == 'Client'){
                $client = ($valc['content'] == 'true') ? 'Yes' : 'No';
              }else if( $valc['val'] == 'Account Name'){
                $comp_name = $valc['content'];
              }
        }
        if(in_array($comp_name, $comp)){
          $user_data .= "('".$contact_id."','".$email."','".$fname.' '.$lname."','".$new_weekly_report."','".$client."','".standardDateTimeFormat('Y-m-d H:i:s')."')".',';
        }            
      }
      $user_data = rtrim($user_data,',');
      $db = getConnection();
      // TRUNCATE "new_weekly_report_log" table
      $sql_tbl_empty = "TRUNCATE new_weekly_report_log";
      $stmt = $db->prepare($sql_tbl_empty);
      $stmt->execute();

      $sql = "INSERT INTO `new_weekly_report_log`(`zoho_contact_id`, `email`, `name`, `new_weekly_report`, `client`, `created_date`) VALUES ".$user_data;
      $stmt = $db->prepare($sql);
      $stmt->execute();

      $subject = 'New Weekly Report Log';
      $message = "New Weekly Report Log updated in Adsphere DB";

      $headers = 'From: info@drmetrix.com' . "\r\n" .
                  'MIME-Version: 1.0' . "\r\n" .
                  'Content-type: text/html; charset=iso-8859-1' . "\r\n";

      try {
          $mail = new PHPMailer(); //New instance, with exceptions enabled

        $body = $message;
        $mail->IsSendmail();     
        $to = "siddhesh.s@v2solutions.com";
        $mail->AddAddress($to);
        $mail->SetFrom('info@drmetrix.com', 'DRMetrix');
        $mail->Subject  = $subject;
        $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->WordWrap   = 80; // set word wrap
        $mail->MsgHTML($body);
        $mail->IsHTML(true); // send as HTML
        $mail->Send();
      } catch (phpmailerException $e) {  }
    }
  }    
}
?>