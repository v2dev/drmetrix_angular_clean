<?php
require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../functions.php';
require_once dirname(__FILE__) . '/../PHPMailer/class.phpmailer.php';
ignore_user_abort();

weeklyEmailUser();
closeConnection();

function weeklyEmailUser(){
	if(RETAILREPORT_ONLINE == 1){
		$db = getConnection();
		//$current_monday = date('Y-m-d',strtotime('next monday -7 days'));	
		$monday = date( 'Y-m-d', strtotime( 'monday this week' ) );	
		$last_week =  getLastMediaWeekDetail();
		$sql = "SELECT * FROM weekly_email_user WHERE subscription_status = '1'  AND 	last_mail_sent != '".$monday."' ";
		$rows = getResult($sql);
		$i=1;
	    if (!empty($rows)) {    	
	    	foreach ($rows as $key =>$val){
	    		$val['calendar_id'] = $last_week['calendar_id'];
				$val['start_date'] = $last_week['start_date'];
				$val['end_date'] = $last_week['end_date'];
				$val['user_type'] = $val['report_type'];
	    		sendRetailEmail ($val);
	    		if($i == 2){
	    			closeConnection();
	    			sleep(60);
	    			$i=0;
	    		}
	    		$i++;
	    	}
	    }
	    echo "done";
	}
}


function sendRetailEmail ($user_info) {		
	$subject 	= 'LT Weekly Retailer - Week '.$user_info['calendar_id'].' - Report';
	$from_name 	= FROM_NAME;
	$from_email = FROM_EMAIL;
	
	$to_name 	= $user_info['first_name'].' '.$user_info['last_name'];
	$to_email 	= $user_info['email'];
	
	$bcc[] 		= array('pravin.sapkal@v2solutions.com' => "Pravin Sapkal");
	$bcc[] 		= array('gaurav.sharma@v2solutions.com' => "Gaurav Sharma");

	$body 		= file_get_contents('retail_email_template.html');

	//replace body with links str_replace
	$body 		= replace_content ($body, $user_info);
	
	$mail 		= new PHPMailer(); //New instance, with exceptions enabled
	$mail->IsSendmail();  // tell the class to use Sendmail            
	$mail->SetFrom($from_email, $from_name);
	$mail->AddAddress($to_email, $to_name);
	$mail->Subject  = $subject;
	$mail->MsgHTML($body);
	$mail->IsHTML(true); // send as HTML

	foreach ($bcc as $email) {
		foreach ($email as $email_address => $name) {
			$mail->addBCC($email_address, $name); 		
		}
	}

	$email_status = $mail->Send();

	if(!$email_status) {
		$email_status = $mail->ErrorInfo;    	
	} 

	$db = getConnection();
	$sql 	= 'UPDATE weekly_email_user SET last_mail_sent = "'.gmdate('Y-m-d').'" WHERE email = "'.$user_info['email'].'"';
	$stmt 	= $db->prepare($sql);
	$stmt->execute();

	$sql_log = "INSERT INTO weekly_email_log SET email='".addslashes($user_info['email'])."', for_date='".gmdate('Y-m-d')."', email_send='".gmdate('Y-m-d H:i:s')."', response='".$email_status."' ";
	$stmt 	= $db->prepare($sql_log);
	$stmt->execute();
}


function replace_mailchimp_edit_blocks ($content, $info) {
	extract($info);

	$to_be_replaced[] = '<div mc:edit="reportlinks"></div>';
	$to_be_replaced[] = '<span mc:edit="mediaweekdate">*|DATE:W|* *|DATE:Y|*;</span>';
	$to_be_replaced[] 	= 'UNSUBSCRIBE_LINK';

	$replaced_with[] = '<a href="http://'.HOST.'/retailreport/api/index.php/retail_report_short_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$user_type.$calendar_id.'_*|DATE:Y|*&siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*" target="_blank"> <img alt="DRMetrix&nbsp;Short&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-01.png?siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*"></a></td><td mc:edit="long_link"><a href="http://'.HOST.'/retailreport/api/index.php/retail_report_long_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$user_type.$calendar_id.'_*|DATE:Y|*&siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*" target="_blank"><img alt="DRMetrix&nbsp;Long&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-02.png?siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*"></a>';

	$replaced_with[] = "<span>".$calendar_id." - ".$start_date.' thru '.$end_date." </span>";

	$replaced_with[] = "http://".HOST."/drmetrix/api/retail_report/unsubscribe.php?param=".base64_encode($email); 

	return str_replace($to_be_replaced, $replaced_with, $content);
}


function replace_placeholders ($content, $info) {
	extract($info);

	$to_be_replaced[] 	= '*|DATE:W|*';
	$to_be_replaced[] 	= '*|DATE:Y|*';
	$to_be_replaced[] 	= '*|FNAME|*';
	$to_be_replaced[] 	= '*|LNAME|*';
	$to_be_replaced[] 	= '*|EMAIL|*';
	$to_be_replaced[] 	= '*|UTMCODE|*';
	$to_be_replaced[] 	= '<USEREMAIL>';


	$replaced_with[]	= $calendar_id;
	$replaced_with[]	= gmdate('Y');
	$replaced_with[]	= $first_name;
	$replaced_with[]	= $last_name;
	$replaced_with[]	= $email;
	$replaced_with[]	= $utm_code;
	$replaced_with[]	= $email;
	
	return str_replace($to_be_replaced, $replaced_with, $content);
}

function replace_content ($body, $info) {
	$body = replace_mailchimp_edit_blocks($body, $info);
	$body = replace_placeholders($body, $info);

	return $body;
}
?>
