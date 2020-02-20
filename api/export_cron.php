<?php
// Cron is in used, runs every second.
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';

if(php_sapi_name() == 'cli') {
    if (!isset($argv[1])) {
      echo 'environment not set correctly';
      exit;
    }
    parse_str($argv[1], $params);
    $execute_queries_on_production  = $params['environment'];
} else {
    $execute_queries_on_production  = $_GET['environment'];
    echo 'Script cannot be exeuted from GUI';
    exit;
}

ignore_user_abort();

cronDownloadExcelFiles();

function cronDownloadExcelFiles()
{
    global $execute_queries_on_production;
    resetStatus();
    $sql = "SELECT count(*) count FROM `excel_exports` where status = 'inprogress' and environment_id = '".$execute_queries_on_production."';";
    $rows = getResult($sql);
    if ($rows[0]['count'] >= NO_OF_FILE_DOWNLOAD_TASKS) {
        echo "cannot start more than " . NO_OF_FILE_DOWNLOAD_TASKS . " tasks";
        return;
    }

    $sql = "SELECT * FROM `excel_exports` where query IS NOT NULL AND status = 'queued' and progress = 0 and report_type != 'network_list' and environment_id = '".$execute_queries_on_production."' order by requested_on asc LIMIT 1;";
    // $sql = "SELECT * FROM `excel_exports` where id = 48859";
    $rows = getResult($sql);
    if (!empty($rows[0])) {
        ini_set('memory_limit', '8192M');
        $db = getConnection();
        $rows = $rows[0];
        extract($rows);
        $file_name = getFileNameFromExcelReportTables($rows);
        $function_name = getFunctionNameFromExcelReportTables($rows);
        $updateRow = 'UPDATE excel_exports SET status = "inprogress", task_initiated_on="' . date("Y-m-d H:i:s") . '" WHERE id = ' . $id;
        $stmt = $db->prepare($updateRow);
        $stmt->execute();

        closeConnection();
        $filename_to_be_replaced = array('*', '/');
        $file_name = str_replace($filename_to_be_replaced, '-', $file_name);
        $excel_values = array('day_type' => $day_type);
        $downloaded_file_name = downloadExcel($function_name, $query, $day_type, $file_name, $user_id, $header_text, $media_date_range, $excel_values, $id);
        $db = getConnection();

        $check_email = "SELECT email_alert FROM excel_exports WHERE id = " . $id;
        $reuslt = getResult($check_email);

        if (!empty($reuslt[0])) {
            $reuslt = $reuslt[0];
            extract($reuslt);
        }
        if ($email_alert == 1) {
            $file_downloadable_path = getExcelFiledownloadablePath($downloaded_file_name);
            
            sendMail($user_id, $file_downloadable_path, $id, $email);
            $email_condition = " , mail_sent_on = '" . date("Y-m-d H:i:s") . "', mail_sent = 'Y' ";
        } else {
            $email_condition = "";
        }

        $start_pos = strpos($file_path, '{');
        $end_pos = strpos($file_path, '}');

        if ($start_pos > -1 && $end_pos > -1) {
            $dirName = dirname(addslashes($downloaded_file_name));
            $find = array("{", "}");
            $replace = array("");

            $file_path = str_replace($find, $replace, $file_path);

            $info = pathinfo($downloaded_file_name);
            $name = $info['filename'];
            $ext = $info['extension'];

            $newFilePath = $dirName . '/' . $file_path . '.' . $ext;

            if ($_SERVER['HTTP_HOST'] == "localhost") {
                rename("'.$downloaded_file_name.'", "'.$newFilePath.'");
            } else {
                shell_exec('mv "' . $downloaded_file_name . '" "' . $newFilePath . '"');
            }
            $downloaded_file_name = $newFilePath;
        }

        $filesize = calculateFileSize($downloaded_file_name);
        $updateRow = 'UPDATE excel_exports SET file_path = "' . addslashes($downloaded_file_name) . '", filesize ="' . $filesize . '", status = "completed", task_completed_on="' . date("Y-m-d H:i:s") . '" ' . $email_condition . 'WHERE id = ' . $id;

        $stmt = $db->prepare($updateRow);
        $stmt->execute();
    }
}

function resetStatus()
{
    global $execute_queries_on_production;
    if (date('I', time()))
    {
        $dayLightSaving = '-05:00';
    }
    else
    {
        $dayLightSaving = '-04:00';
    }
    $sql = "SELECT *, CONVERT_TZ(last_updated,'+00:00','$dayLightSaving') as last_updated_utc FROM `excel_exports` where status = 'inprogress' and environment_id = '".$execute_queries_on_production."';";
    $rows = getResult($sql);

    if (count($rows) > 0) {
        foreach ($rows as $record) {
            $from_date = $record['last_updated_utc'];
            $to_date = date("Y-m-d H:i:s");
            $time_taken = getTimeDiffInMin($from_date, $to_date);

            if ($time_taken >= 20) {
                $sql = "UPDATE excel_exports SET status = 'queued', progress = 0 and report_type != 'network_list' WHERE id = " . $record['id'];
                //api_exception_log('export_cron.php', 'Export cron  ', serialize($sql));
                $db = getConnection();
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
        }
    }
}
