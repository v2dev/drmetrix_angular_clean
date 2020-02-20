<?php
	set_time_limit(0);
	require_once dirname(__FILE__) . '/config.php';
	require_once dirname(__FILE__) . '/constants.php';
	require_once dirname(__FILE__) . '/functions.php';
	require_once dirname(__FILE__) . '/inc/MCAPI.class.php';
	require_once dirname(__FILE__) . '/PHPMailer/class.phpmailer.php';

	$zoho_key = ZOHO_APIKEY;
	$api_key = API_KEY;
	$crm_type = "Leads";
	$user_info = $_REQUEST;
	$record_id = $user_info['lead_id'];

	function curl_param($user_info,$zoho_key,$crm_type){
		$param = array();
		$param['url'] = "https://crm.zoho.com/crm/private/json/".$crm_type."/getRecordById";
		$param['query'] = 'authtoken='.$zoho_key.'&scope=crmapi&id='.$user_info['lead_id'].'&selectColumns='.$crm_type.'('.rtrim($crm_type, "s").' Owner,First Name,Last Name,Email,Company,List Template,UTM Code,Report Type,Created Time,Account Name)';
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

	function subscribedUserMailchimp($rsp_crul,$api_key,$crm_type,$user_info,$record_id,$zoho_key){
		$company = '';
		
		foreach($rsp_crul['response']['result'][$crm_type]['row']['FL'] as $key => $value){
			if($value['val'] == 'Lead Owner' || $value['val'] == 'Contact Owner'){
				$lead_owner =  $value['content'];
			}else if($value['val'] == 'First Name'){
				$fname = $value['content'];
			}else if($value['val'] == 'Last Name'){
				$lname = $value['content'];
			}else if($value['val'] == 'Email'){
				$email = $value['content'];
			}else if($value['val'] == 'List Template'){
				$list_name = $value['content'];
			}else if($value['val'] == 'Report Type'){
				$report_type = $value['content'];
			}else if($value['val'] == 'Company' || $value['val'] == 'Account Name'){
				$company = $value['content'];
			}else if($value['val'] == 'UTM Code'){
				$utm_code	= $value['content'];
			}
		}		

		$merge_vars = array(
			'FNAME'		=> $fname, 
			'LNAME'		=> $lname,
			'COMPANY'	=> $company,
			'REPORTTYPE'=> $report_type,	
			'CRMTYPE'	=> $crm_type,	
			'ZOHOID'	=> $user_info['lead_id'],	
			'CREATEDBY'	=> $lead_owner
		);

		$user_email = $email;
		$api = new MCAPI($api_key);	
		$getList = $api->lists();
		$list_id = "";
		$i=1;
		foreach($getList['data'] as $key => $val){
			if($val['name'] == $list_name){			
				$list_id = $val['id'];
				break;		
			}
			$i++;
		}	
		
		$merge_vars['UTMCODE']  = $utm_code;
		
		$api->listSubscribe( $list_id, $user_email, $merge_vars, 'html', false, true );
		$flag = "add";
		
		if ($api->errorCode){
			echo "error";
		} else {
			$status = 'subscribed';
			$fl  = '<FL val="Receive Weekly Report">true</FL>';			

			$xml = '<?xml version="1.0" encoding="UTF-8"?>
					<Leads>
					<row no="1">
					<FL val="UTM Code">'.$utm_code.'</FL>';
			$xml .= $fl;
			$xml .= '<FL val="Subscription Status">'.$status.'</FL>
					</row>
					</Leads>';
			$url ="https://crm.zoho.com/crm/private/xml/".$crm_type."/updateRecords";
			$query="authtoken=".$zoho_key."&scope=crmapi&id=".$record_id."&xmlData=".$xml;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			//Execute cUrl session
			$response = curl_exec($ch);
			curl_close($ch);
			$xml_rsp = new SimpleXMLElement($response);
			echo "success";
		}
	}

	$param_arr = curl_param($user_info, $zoho_key,'Contacts');
	$rsp_crul = process_curl($param_arr['url'], $param_arr['query']);

	if(isset($rsp_crul['response']['nodata'])){	
		$param_arr = curl_param($user_info, $zoho_key,'Leads');
		$rsp_crul = process_curl($param_arr['url'], $param_arr['query']);
		subscribedUserMailchimp($rsp_crul,$api_key,'Leads',$user_info,$record_id,$zoho_key);
	}else{
		subscribedUserMailchimp($rsp_crul,$api_key,'Contacts',$user_info,$record_id,$zoho_key);

	}
?>