<?php
//Cron is in used, runs at 2 PM
if(php_sapi_name() != 'cli') {
    echo 'Script cannot be exeuted from GUI';
    exit;
}
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/../zoho_crm/functions.php';
ignore_user_abort();
addNewAdvertiser();

function addNewAdvertiser(){
	$db = getConnection();
	$sql="SELECT adv_id, company_name, display_name, alt_adv_names, notes,need_help  FROM `advertiser` WHERE `zoho_account_id` IS NULL AND `zoho_synced` = '0' AND new_adv = '1' ORDER BY adv_id ASC";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$new_adv_list = $stmt->fetchAll(PDO::FETCH_ASSOC );
	// $new_adv_list = array(
	// 	array(
	// 		'company_name' 	=> "test cron",
	// 		'display_name' 	=> "test display name",
	// 		'alt_adv_names' => "test aliase",
	// 		'adv_id'   		=> 9000,
	// 		'need_help' => 1

	// 	)
	// 	);
	$adv_array = array();
	$i = 1;
	
	if(!empty($new_adv_list)){
		foreach($new_adv_list as $key => $value){
			$need_help = false;
			if($value['need_help'] == '1'){
				$need_help = true;
			}
			$account = [];
			$insertFields = array(
				"Account_Name"      	=> ($value['company_name']),
				"ADS_Display_Name"  	=> $value['display_name'],
				"Aliases_2"      		=> $value['alt_adv_names'],
				"Adsphere_Acct_ID"      => $value['adv_id'],
				"New_Advertiser"    	=> true,
				"Need_Help" 			=> $need_help,
			);
			$createAccountResponse  = createRecordInZoho('Accounts/upsert', $insertFields);
			
			if(isset($createAccountResponse->data[0]) &&  $createAccountResponse->data[0]->code == 'SUCCESS'){          
				$zoho_id = $createAccountResponse->data[0]->details->id ;
				$account['zoho_account_id'] = $zoho_id;
				$sql = "UPDATE advertiser SET zoho_account_id ='".$account['zoho_account_id']."', new_adv = '0', zoho_synced = '1' WHERE adv_id = '".$value['adv_id']."' ";
			    $stmt = $db->prepare($sql);
			    $stmt->execute();
			} else{
				// exception log
				$filename               = basename($_SERVER['PHP_SELF']);
				api_exception_log($filename, 'Cron - New advertiser ', serialize($createAccountResponse));
			}

			if (!empty($value['notes']) && !is_null($value['notes'])) {
                $notes_insertFields = array(
                    "Note_Title" => $value['company_name'],
                    "Note_Content" => $value['notes'],
                );

                $createNotesResponse = createNotesForModule('Accounts/' . $account['zoho_account_id'] . '/Notes', $notes_insertFields);
                if (isset($createNotesResponse->data[0]) && $createNotesResponse->data[0]->code == 'SUCCESS') {
                    // do nothings
                } else {
                    // exception log
                    $filename = basename($_SERVER['PHP_SELF']);
                    api_exception_log($filename, 'Cron - Notes in advertiser ' . $value['company_name'], serialize($createNotesResponse));
                }
            }
	        if($i == 5){
            	sleep(5);
            	$i = 1;
          	}
          	$i++;	
    	}
	}
	closeConnection();
	echo "done";
}


?>