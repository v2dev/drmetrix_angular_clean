<?php
//referece : https://isabelcastillo.com/create-send-mailchimp-campaign-api-3
//Cron runs at 1 PM on monday
if(php_sapi_name() != 'cli') {
    echo 'Script cannot be exeuted from GUI';
    exit;
}
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/../zoho_mailchip_api/functions_mailchimp.php';
require_once dirname(__FILE__) . '/inc/MailChimp.php';
require_once dirname(__FILE__) . '/PHPMailer/class.phpmailer.php';
ignore_user_abort();
sendWeeklyReport();
function getListId($list_name){
	//get list form Mailchimp
	// $api_obj = new MailChimp(API_KEY);
    // $getList = $api_obj->get("lists/");
    $getList  =  getList();
    foreach ($getList->lists as $key => $value) {
        if ($value->name ==  $list_name) {
            return $value->id;
        }
	}
}
function sendWeeklyReport(){
	if( check_weekly_mail_date() && ($_SERVER['SHELL'] == '/bin/sh'  || $_SERVER['SHELL'] == '/bin/bash')){
		$MailChimp = new MailChimp(MAILCHIMP_API_KEY);
		$last_week =  getLastMediaWeekDetail();
		$year = explode('/', $last_week['end_date']);
        // get all templates
        $result              = getTemplates('user');
		$response = $result;
        $i=0;
		$for_campaign = array();
		
		foreach($response->templates as $tmpl){
		    if($tmpl->name == 'DRMetrix-Branded-Retailers'){
		    	$for_campaign[$i]['type'] = 'drmetrix';
		    	$for_campaign[$i]['template_id'] = $tmpl->id;
		    	$for_campaign[$i]['user_type'] = 'retailer';
		    	$for_campaign[$i]['list_id'] = getListId(LIST_DRM);
		    	$for_campaign[$i]['for_subject'] = "DRM";
            } 
            else if($tmpl->name == 'DRMetrix-Branded-Marketers'){
		    	$for_campaign[$i]['type'] = 'drmetrix';
		    	$for_campaign[$i]['template_id'] = $tmpl->id;
		    	$for_campaign[$i]['user_type'] = 'marketer';
		    	$for_campaign[$i]['list_id'] = getListId(LIST_DRM);
		    	$for_campaign[$i]['for_subject'] = "DRM";
		    }else if($tmpl->name == 'LeisureTime-Branded-Retailers'){
		    	$for_campaign[$i]['type'] = 'leisuretime';
		    	$for_campaign[$i]['template_id'] = $tmpl->id;
		    	$for_campaign[$i]['user_type'] = 'retailer';
		    	$for_campaign[$i]['list_id'] = getListId(LIST_LT);
		    	$for_campaign[$i]['for_subject'] = "LT";
		    }else if($tmpl->name == 'LeisureTime-Branded-Marketers'){
		    	$for_campaign[$i]['type'] = 'leisuretime';
		    	$for_campaign[$i]['template_id'] = $tmpl->id;
		    	$for_campaign[$i]['user_type'] = 'marketer';
		    	$for_campaign[$i]['list_id'] = getListId(LIST_LT);
		    	$for_campaign[$i]['for_subject'] = "LT";
		    }else if($tmpl->name == 'inDemand-Retailers'){
		    	$for_campaign[$i]['type'] = 'indemand';
		    	$for_campaign[$i]['template_id'] = $tmpl->id;
		    	$for_campaign[$i]['user_type'] = 'retailer';
		    	$for_campaign[$i]['list_id'] = getListId(LIST_TD);
		    	$for_campaign[$i]['for_subject'] = "IDM";
		    }else if($tmpl->name == 'inDemand-Marketers'){
		    	$for_campaign[$i]['type'] = 'indemand';
		    	$for_campaign[$i]['template_id'] = $tmpl->id;
		    	$for_campaign[$i]['user_type'] = 'marketer';
		    	$for_campaign[$i]['list_id'] = getListId(LIST_TD);
		    	$for_campaign[$i]['for_subject'] = "IDM";
		    }
		    $i++;
		}
		//Marketer or Retailer DRMerix / LeisureTime Report--------------------------------------------------------------------
		if($last_week['calendar_id'] < 10){
			$last_week['calendar_id'] = '0'.$last_week['calendar_id'];
		}
		$type = 'regular'; 	
		$from_email = FROM_EMAIL; 
        $from_name = FROM_NAME;
	  
		foreach ($for_campaign as $key => $value) {
			$list_id        = $value['list_id'];
			$template_id    = $value['template_id'];
			$subject        = $value['for_subject'].' Weekly '.ucfirst($value['user_type']).' - Week '.$last_week['calendar_id'].' - Report';
			
			$segment_opts = array(
				'match' => 'all', // or 'all' or 'none'
				'conditions' => array (
					array(
						'condition_type' => 'TextMerge', 
						'field' => 'REPORTTYPE', 								  
						'op' => 'contains', 
						'value' => ucfirst($value['user_type'])
					)
				
				  )
				);

				
				$postData       = 
				array(
					'recipients' =>
						array(
							'list_id' => $list_id,
							"segment_opts"=> $segment_opts
						),
						'type' => $type,
						'settings' =>
							array(
							'subject_line' => $subject,
							'title'  => $subject,
							'reply_to' => $from_email,
							'from_name' =>$from_name
						)
					 );
          
				$module = 'campaigns/';
				$result = createRecord($module , $postData);
				$campaign_id = $result->id;
				if($campaign_id){
					$media_week_date = "<span>".$last_week['calendar_id'] ." - ".$last_week['start_date'].' thru '.$last_week['end_date']." </span>"; 
					$report_links = '<a href="http://'.HOST.'/retailreport/api/index.php/retail_report_short_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$value['user_type'].$last_week['calendar_id'].'_'.$year[2].'&siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*" target="_blank"> <img alt="DRMetrix&nbsp;Short&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-01.png?siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*"></a></td><td mc:edit="long_link"><a href="http://'.HOST.'/retailreport/api/index.php/retail_report_long_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$value['user_type'].$last_week['calendar_id'].'_'.$year[2].'&siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*" target="_blank"><img alt="DRMetrix&nbsp;Long&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-02.png?siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*"></a>';
					$template_content =  array(
						'template' => array(
							'id' => $template_id , 
							'sections'  => array(                     
								'reportlinks' => $report_links, 'mediaweekdate'=>$media_week_date
							)
						)
						);
		
					$set_campaign_content = setCampaignContent( $campaign_id, $template_content );
					$module = 'campaigns/' . $campaign_id . '/actions/send';
					$send_campaign = createRecord($module);
						if ( empty( $send_campaign ) ) {
						} elseif( isset( $send_campaign->detail ) ) {
							$error_detail = $send_campaign->detail;
							api_exception_log($filename, 'send campiagn', serialize($error_detail));
						}
				}
		}

		if(count($for_campaign) == 0) {
		    $zoho_adsphere_discrepancy_email_recipients_array = explode(",", ZOHO_ADSPHERE_DISCREPANCY_EMAIL_RECIPIENTS);

		    foreach ($zoho_adsphere_discrepancy_email_recipients_array as $email_address) {
		        $email_address = trim($email_address);
		        custom_email($email_address, 'ATTENTION - ACTION REQUIRED!!!', "Weekly Retail Report did not go out. Action Required.<br/><br/>" . json_encode($response));
		    }
		}

		echo json_encode(array('status'=> 1));
		exit;
		
	}
}
?>