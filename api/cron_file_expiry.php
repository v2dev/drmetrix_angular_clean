<?php
//Cron is in used ,run always at 1 o'clock.
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) .'/../zoho_crm/functions.php';


ignore_user_abort();

cronDeleteExpiredFiles();

function cronDeleteExpiredFiles() {
       updateExcel30DaysDownloadCount();
       deleteExpiredFilesRow();
       deleteExpiredFilesFolders();
}

function updateExcel30DaysDownloadCount() {
    $current_date   = date('Y-m-d H:i:s');
    $db             = getConnection();
    $sql            = "SELECT count(e.id) as excel_downloads_counts ,e.user_id, u.zoho_contact_id   FROM excel_exports e INNER JOIN user u ON e.user_id = u.user_id WHERE  DATEDIFF(CURRENT_DATE, requested_on) <= ".EXCEL_FILE_EXPIRY_DAYS .' GROUP BY e.user_id';

     try{
        $userRows   = getResult($sql);
     
        $updateSql = array();
        foreach($userRows as $key => $value) {
            $users[$key]['zoho_contact_id']             =  $value['zoho_contact_id'];
            $users[$key]['excel_downloads_counts']      =   $value['excel_downloads_counts'];
            $updateSql[]  = "UPDATE user SET excel_30days_count = ".$value['excel_downloads_counts'].", excel_lifetime_count = ".$value['excel_downloads_counts']." WHERE user_id = ".$value['user_id'] ."";
        }
        $updateStatement = implode(";", $updateSql);
        $db->exec($updateStatement);
     
        foreach($users as $zohoKeys => $zohoValues) {
            $user_details[$zohoKeys]['excel_downloads_counts']     = $zohoValues['excel_downloads_counts'];
            $user_details[$zohoKeys]['zoho_contact_id']            = $zohoValues['zoho_contact_id'];
        }

        $i = 1;
        if(!empty($user_details)) {
            $updateFields = array();
            foreach($user_details as $key => $value){
                $user_array = array (
                    "id"           => $value['zoho_contact_id'],
                    "Day_Download" => $value['excel_downloads_counts'],
                    // "Lifetime_Download" => $value['excel_downloads_counts']
                );
                array_push($updateFields,$user_array);

                if ($i == 75) {
                    $updateResponse = updateBulkRecordsInZoho('Contacts', $updateFields);
                    $updateFields = array();
                    if(!empty($updateResponse)){
                        foreach($updateResponse->data as $key =>$value) {
                            if (isset($value->code) && ($value->code != 'SUCCESS')) {
                                $filename = basename($_SERVER['PHP_SELF']);
                                api_exception_log($filename, 'APIManageZOHOContact - excelBulkUpdate'. $i, serialize($value));
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
                            api_exception_log($filename, 'excelBulkUpdate - '. $i, serialize($value));
                        }
                    }
                }
            }
        }
    }
    catch (PDOException $e) {
        echo '{"status"=>0,"error":{"text":'. $e->getMessage() .'}}';
    }
}

function deleteExpiredFilesRow() {
    $current_date = date('Y-m-d H:i:s');
    $db = getConnection();
    $deleteRow = "Delete FROM excel_exports WHERE  DATEDIFF(CURRENT_DATE, requested_on) > ".EXCEL_FILE_EXPIRY_DAYS;
     try{
        $db = getConnection();
        $stmt = $db->prepare($deleteRow);
        $stmt->execute();
    }
    catch (PDOException $e) {
        echo '{"status"=>0,"error":{"text":'. $e->getMessage() .'}}';
    }
}

function deleteExpiredFilesFolders(){
    $current_date = date('Y-m-d');
    $folders = glob(dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.'*');
    if(!empty($folders)){
        foreach($folders as $folder){
            shell_exec('chown -fR '.OWNERSHIP .' '. dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.'/');
            $folder_name = str_replace(dirname(__FILE__). '/../'.LARGE_EXCEL_DOWNLOAD_PATH, "", $folder);
            $start = strtotime($folder_name);
            $end = strtotime($current_date);
            $days_between = ceil(abs($end - $start) / 86400);
          
            if($days_between >=  EXCEL_FILE_EXPIRY_DAYS && $folder_name != ''){
                shell_exec('chmod -fR 0777 ' . dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.'/');
                shell_exec('rm -fR ' .dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.$folder_name.'/');
            }
        }
    }
}

closeConnection();