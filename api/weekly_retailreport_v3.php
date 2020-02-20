<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/inc/MailChimp.php';
require_once dirname(__FILE__) . '/PHPMailer/class.phpmailer.php';

ignore_user_abort();
sendWeeklyReport();

function getListId($list_name){
	//get list form Mailchimp
	$api_obj = new MailChimp(API_KEY);
	$getList = $api_obj->get("lists/");
	foreach ($getList['lists'] as $key => $value) {
		if($value['name'] == $list_name){ //'LeisureTime Report' or 'DRMetrix Report'
			return $value['id'];
		}
	}
}

function sendWeeklyReport(){	
	/*if(trim($_REQUEST['api_key']) != API_KEY){
		echo json_encode(array('status'=> 0,'error'=>'API key not matched'));
		exit;
	}*/

	$MailChimp = new MailChimp(API_KEY);
	$last_week =  getLastMediaWeekDetail();
	$types = array('user'=>true);
	// get all templates
	$result = $MailChimp->get("templates",['type' => 'user']);
	$response = $result;
	$i=0;
	foreach($response['templates'] as $tmpl){
	    if($tmpl['name'] == 'DRMetrix-Branded-Retailers'){
	    	$for_campaign[$i]['type'] = 'drmetrix';
	    	$for_campaign[$i]['template_id'] = $tmpl['id'];
	    	$for_campaign[$i]['user_type'] = 'retailer';
	    	$for_campaign[$i]['list_id'] = getListId(LIST_DRM);
	    	$for_campaign[$i]['for_subject'] = "DRM";
	    }else if($tmpl['name'] == 'DRMetrix-Branded-Marketers'){
	    	$for_campaign[$i]['type'] = 'drmetrix';
	    	$for_campaign[$i]['template_id'] = $tmpl['id'];
	    	$for_campaign[$i]['user_type'] = 'marketer';
	    	$for_campaign[$i]['list_id'] = getListId(LIST_DRM);
	    	$for_campaign[$i]['for_subject'] = "DRM";
	    }else if($tmpl['name'] == 'LeisureTime-Branded-Retailers'){
	    	$for_campaign[$i]['type'] = 'leisuretime';
	    	$for_campaign[$i]['template_id'] = $tmpl['id'];
	    	$for_campaign[$i]['user_type'] = 'retailer';
	    	$for_campaign[$i]['list_id'] = getListId(LIST_LT);
	    	$for_campaign[$i]['for_subject'] = "LT";
	    }else if($tmpl['name'] == 'LeisureTime-Branded-Marketers'){
	    	$for_campaign[$i]['type'] = 'leisuretime';
	    	$for_campaign[$i]['template_id'] = $tmpl['id'];
	    	$for_campaign[$i]['user_type'] = 'marketer';
	    	$for_campaign[$i]['list_id'] = getListId(LIST_LT);
	    	$for_campaign[$i]['for_subject'] = "LT";
	    }else if($tmpl['name'] == 'TeamDirect-Retailers'){
	    	$for_campaign[$i]['type'] = 'teamdirect';
	    	$for_campaign[$i]['template_id'] = $tmpl['id'];
	    	$for_campaign[$i]['user_type'] = 'retailer';
	    	$for_campaign[$i]['list_id'] = getListId(LIST_TD);
	    	$for_campaign[$i]['for_subject'] = "TD";
	    }else if($tmpl['name'] == 'TeamDirect-Marketers'){
	    	$for_campaign[$i]['type'] = 'teamdirect';
	    	$for_campaign[$i]['template_id'] = $tmpl['id'];
	    	$for_campaign[$i]['user_type'] = 'marketer';
	    	$for_campaign[$i]['list_id'] = getListId(LIST_TD);
	    	$for_campaign[$i]['for_subject'] = "TD";
	    }
	    $i++;
	}

	if($last_week['calendar_id'] < 10){
		$last_week['calendar_id'] = '0'.$last_week['calendar_id'];
	}
	$type = 'regular'; 	
	$from_email = FROM_EMAIL; 
	$from_name = FROM_NAME;
	echo "<pre>"; print_r($for_campaign); exit;	
	/*foreach ($for_campaign as $key => $value) {
		$list_id = $value['list_id'];
		$template_id = 	$value['template_id'];
		$subject = $value['for_subject'].' Weekly '.ucfirst($value['user_type']).' - Week '.$last_week['calendar_id'].' - Report';
		$result = $MailChimp->post("campaigns", [
		    'type' => $type,
		    'recipients' => ['list_id' => $list_id,
		    				"segment_opts"=> ["match"=> "all",
										        "conditions"=> array( array("condition_type"=> "TextMerge", 
										        						 	"op"=> "contains", 
								        									"field"=> "REPORTTYPE",
													        				"value"=> ucfirst($value['user_type'])
															        				
										        				))
										    ]
							],
		    'settings' => ['subject_line' => $subject,
		           'reply_to' => $from_email,
		           'from_name' => $from_name
		          ]
		    
		]);
		$response = $MailChimp->getLastResponse();
		$responseObj = json_decode($response['body']);
		$media_week_date = "<span>".$last_week['calendar_id'] ." - ".$last_week['start_date'].' thru '.$last_week['end_date']." </span>"; 
		$report_links = '<a href="http://'.HOST.'/retailreport/api/index.php/retail_report_short_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$value['user_type'].$last_week['calendar_id'].'_*|DATE:Y|*&siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*" target="_blank"> <img alt="DRMetrix&nbsp;Short&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-01.png?siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*"></a></td><td mc:edit="long_link"><a href="http://'.HOST.'/retailreport/api/index.php/retail_report_long_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$value['user_type'].$last_week['calendar_id'].'_*|DATE:Y|*&siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*" target="_blank"><img alt="DRMetrix&nbsp;Long&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-02.png?siq_name=*|FNAME|* *|LNAME|*&siq_email=*|EMAIL|*"></a>';
		
		$conentResult = $MailChimp->put('campaigns/' . $responseObj->id . '/content', [
	      'template' => ['id' => $template_id, 
	        'sections' => ['REPORTLINKS' => $report_links, 'MEDIAWEEKDATE'=>$media_week_date]
	        ]
      	]);	

		$sent = $MailChimp->post('campaigns/' . $responseObj->id . '/actions/send');
	}*/
	//$cmd  = "curl http://."HOST"./drmetrix/api/retail_report/weekly_email_retailreport.php​";
	//exec($cmd);
	echo "done";
}

?>