<?php
//Cron is in used, runs at 1 PM
if(php_sapi_name() != 'cli') {
    echo 'Script cannot be exeuted from GUI';
    exit;
}
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/../zoho_crm/functions.php';
ignore_user_abort();
updateTrackingAndAlertFlag();

function updateTrackingAndAlertFlag()
{
    $db = getConnection();
    $_sql = "SELECT ta.user_id, u.zoho_contact_id, ta.status FROM `tracking_and_alerts` as ta INNER JOIN user u ON u.user_id = ta.user_id where ta.status = 'active' AND  u.tracking_alert_subscribed = '1' group by ta.user_id";

    // $_sql = "SELECT ta.user_id, u.zoho_contact_id, ta.status, MIN(l.created_at) first_login_date FROM `tracking_and_alerts` as ta INNER JOIN user u ON u.user_id = ta.user_id  INNER JOIN user_logs l ON u.user_id = l.user_id where ta.status = 'active' AND  u.tracking_alert_subscribed = '1' group by ta.user_id";
    $stmt = $db->prepare($_sql);
    $stmt->execute();
    $active_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tracked_user_id = array();
    foreach ($active_users as $key => $value) {
        $tracked_user_id[] = $value['user_id'];
    }

    $_where = '';
    if (!empty($tracked_user_id)) {
        $_where = "AND u.user_id NOT IN (" . implode(',', $tracked_user_id) . ")";
    }

    $sql = "SELECT user_id,zoho_contact_id, 'inactive' as status FROM user u WHERE zoho_contact_id IS NOT NULL " . $_where . " ORDER BY user_id ASC";
    // $sql = "SELECT u.user_id,zoho_contact_id, 'inactive' as status,MIN(l.created_at) first_login_date FROM user u LEFT JOIN user_logs l ON u.user_id = l.user_id WHERE zoho_contact_id IS NOT NULL ".$_where."  group by u.user_id  
    // ORDER BY `first_login_date`  ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $inactive_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $final_list = array_merge($inactive_users, $active_users);
    $i = 1;
    $y = 1;

    if (!empty($final_list)) {
		$updateFields = array();
        foreach ($final_list as $key => $value) {
			$status = ($value['status'] == 'active') ? true : false;
			$userDetails = array(
				'id'			  => $value['zoho_contact_id'],
                'Tracking_Alerts' => $status,
                // 'First_Login_Date' => isset($value['first_login_date']) ? isoDateFormatForZoho($value['first_login_date']) : ''
			);
            $updateFields[] = $userDetails;
            if ($i == 75) {
                $updateResponse = updateBulkRecordsInZoho('Contacts', $updateFields);
                $updateFields = array();
                if(!empty($updateResponse)){
                    foreach($updateResponse->data as $key =>$value) {
                        if (isset($value->code) && ($value->code != 'SUCCESS')) {
                            $filename = basename($_SERVER['PHP_SELF']);
                            api_exception_log($filename, 'Cron sync tracking flag - '. $i, serialize($value));
                        }
                    }
                }
                $i = 0;
            }
            $i++;
        }

        if($i > 1){
            $updateResponse = updateBulkRecordsInZoho('Contacts', $updateFields);
            $data = array();
            if(!empty($updateResponse)){
                foreach($updateResponse->data as $key =>$value) {
                    if (isset($value->code) && ($value->code != 'SUCCESS')) {
                        $filename = basename($_SERVER['PHP_SELF']);
                        api_exception_log($filename, 'Cron sync tracking flag - '. $i, serialize($value));
                    }
                }
                $i = 0;
            }
            $i++;
        }

        if($i > 1){
            $updateResponse = updateBulkRecordsInZoho('Contacts', $updateFields);
            $data = array();
            if (isset($updateResponse->code) && $updateResponse->code != 'SUCCESS') {
                $filename = basename($_SERVER['PHP_SELF']);
                api_exception_log($filename, 'Cron - sync tracking flag ' . $i, serialize($updateResponse));
            }
        }
	}
    closeConnection();
    echo "done";
}
