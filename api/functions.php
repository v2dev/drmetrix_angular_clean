<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/queries.php';

global $skip_urls;
$skip_urls = array('user_login', 'user_logout', 'number_of_weeks', 'last_media_week', 'last_media_month', 'last_media_quarter', 'current_week', 'current_media_month', 'current_media_quarter', 'verify_authy', 'get_cats_list', 'forgot_password', 'update_password', 'get_video', 'excel_video', 'daily_creative_status_update', 'get_user', 'get_my_reports_data', 'downloadClientFiles', 'current_week', 'ranking_report', 'display_airings_brands_with_networks', 'fetch_creative_short_duration', 'unsubscribe_user', 'get_all_media_data', 'network_list_export_from_grid', 'creative_videos', 'export_refine_log', 'create_network_pdf', '/drmetrix/api/export_to_excel', '/drmetrix/api/refine_excel_export', '/drmetrix/api/filter_results', '/drmetrix/api/network_excel_export', '/drmetrix/api/display_airings_brands_with_networks', '/drmetrix/api/apply_refine_filters','networks_ranking_export','export_to_excel','show_users', 'get_user_edit', 'delete_user_from_company','forgot_password', 'deactivate_user', 'contact_us', 'filter_results');

function downloadExcel($function_name, $sql, $day_type, $file_name, $user_id, $name, $date_range_str, $excel_values ,$id = '')
{
    require_once dirname(__FILE__) . '/excel_download.php';
    return $function_name($sql, $day_type, $file_name, $user_id, $name, $date_range_str, $excel_values, $id);
}
//echo standardDateTimeFormat('Y-m-d h:i:s', strtoTime('2016-08-01 11:00:00'));
/*
 * @param string $format Dateformat
 * @param date $date_to_be_converted Date to convert in std format
 * @return date
 */

function standardDateTimeFormat($format = 'Y-m-d H:i:s', $date_to_be_converted = '')
{
    if (empty($date_to_be_converted)) {
        return date($format);
    } else {
        return date($format, $date_to_be_converted);
    }

}

/*
 * @param string $file Filepath
 * @param int $digits Digits to display
 * @return string|bool Size (KB, MB, GB, TB) or boolean
 */

function calculateFileSize($filePath, $digits = 2)
{
    //  if(file_exists($filePath)){
    $db = getConnection();
    $filepath = addslashes($filePath);
    $fileSize = filesize($filePath);

    $sizes = array("TB", "GB", "MB", "KB", "B");
    $total = count($sizes);
    while ($total-- && $fileSize > 1024) {
        $fileSize /= 1024;
    }
    $roundSize = round($fileSize, $digits) . " " . $sizes[$total];

    return round($fileSize, $digits) . " " . $sizes[$total];
}

function getLifetimeDetails(){
   $last_week =  Slim_App_Lib_Common::getLastMediaWeek();
    $sql = 'SELECT MIN(media_week_start) as min_sd ,MIN(media_year) as year , "'.customDate('Y-m-d').'" as max_ed from media_calendar';
    $mediaCalendarRows = getResult($sql);

    $this_lifetime['start_date'] = date("m/d/Y", strtotime($mediaCalendarRows[0]['min_sd']));
    $this_lifetime['end_date'] = date("m/d/Y", strtotime($mediaCalendarRows[0]['max_ed']));
    $this_lifetime['start_date_db'] = $mediaCalendarRows[0]['min_sd'];
    $this_lifetime['end_date_db'] = $mediaCalendarRows[0]['max_ed'];
    $this_lifetime['year'] = $mediaCalendarRows[0]['year'];

    return $this_lifetime;
}

function getCurrentWeekDetails()
{
    $db = getConnection();

    $year = customDate('Y');
    $date = customDate('Y-m-d');
    
    $media_type_count   = "SELECT media_week as media_type, media_year FROM `media_calendar` WHERE  `media_week_start` <= '$date' AND `media_week_end` >= '$date'";
    
    $media_count_result = execute_query_get_result($media_type_count, 'FETCH_OBJ');            
    /*
    $stmt = $db->prepare($media_type_count);
    $stmt->execute();
    $media_count_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    */
    $result = $media_count_result[0]->media_type;
    $year   = $media_count_result[0]->media_year;
    //CURDATE() as max_date
    $sql = "SELECT MIN(`media_week_start`) as min_date,  MIN(`media_week_end`) as max_date, media_week as calendar_id from media_calendar where  `media_year` = $year AND media_week  =  $result";

    $date_range = execute_query_get_result($sql, 'FETCH_ASSOC');
    /*
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $date_range = $stmt->fetchAll(PDO::FETCH_ASSOC);
     */

    $current_week['sd'] = $date_range[0]['min_date'];
    $current_week['ed'] = $date_range[0]['max_date'];
    $current_week['calendar_id'] = $date_range[0]['calendar_id'];

    $current_week['start_date'] = date("m/d/Y", strtotime($date_range[0]['min_date']));
    $current_week['end_date'] = date("m/d/Y", strtotime($date_range[0]['max_date']));
    return $current_week;
}

function getLifeTimeWeeks()
{
    $lifetimeWeek = "";
    for ($i = 1; $i <= 52; $i++) {
        $lifetimeWeek = $lifetimeWeek . "," . $i;
    }
    $lifetimeWeek = ltrim(rtrim($lifetimeWeek, ","), ",");
    return $lifetimeWeek;
}

function fullTextSearch($colName, $search, $mode = null)
{
    $return_arr = array();
    $return_arr['search_col'] = '(MATCH (' . $colName . ')
    AGAINST ("' . $search . '" IN ' . MODE . ' LANGUAGE MODE)';

    $return_arr['str'] = ' AND ' . $return_arr['search_col'];
    //if(strlen($search) <= 4){
    $return_arr['str'] = $return_arr['str'] . ' OR ' . $colName . ' LIKE "%' . $search . '%"  OR ' . $colName . ' LIKE "%' . str_replace(" ", "", $search) . '%")';
    //}
    return $return_arr;
}

function getMediaYears($date)
{
    // $sql = "SELECT media_year,media_year_end from media_calendar where media_year <= YEAR('".$date."') GROUP by media_year ORDER BY media_year DESC";
    $sql = "SELECT media_year,media_year_start,media_year_end from media_calendar where media_year <= YEAR('" . $date . "') GROUP by media_year ORDER BY media_year DESC";
    $result = getResult($sql);
    return $result;
}

function getOthersDateDetails($date = '')
{
    $date = empty($date) ? customDate('Y-m-d') : $date;
    $mediaCalendarRows = getMediaCaledarRows($date);
    $weeksDetails = getWeeksDetails($mediaCalendarRows);
    $quarterDetails = getQuarterDetails($mediaCalendarRows);
    $monthDetails = getMonthDetails($mediaCalendarRows);
    $mediaYears = getMediaYears($date);

    /*
    if (date('Y', strtotime($date)) == date('Y')) {
    array_pop($weeksDetails);
    array_pop($quarterDetails);
    array_pop($monthDetails);
    }
     */

    $ytdDetails = getYtdDetails($mediaCalendarRows);
    arsort($weeksDetails);
    $array['week'] = $weeksDetails;
    arsort($monthDetails);
    $array['month'] = $monthDetails;
    arsort($quarterDetails);
    $array['quarter'] = $quarterDetails;
    $array['year'] = $ytdDetails;
    $array['media_years'] = $mediaYears;
    $array['current_media_year'] = date("Y");

    return $array;
}

if (!function_exists('show')) {
    function show($var, $exit = 0)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';

        if ($exit == 1) {
            exit;
        }
    }
}

function implode_call($arr, $quote = false)
{
    if ($quote) {
        $string = implode("','", $arr);
        $string = "'" . $string . "'";
    } else {
        $string = implode(",", $arr);
    }
    $string = rtrim($string, ",");
    return $string;
}

function getMediaCaledarRows($date = '')
{
    // $result = getPreviousDates('week', 1, '', 'current');
    $result = getPreviousDates('week', 1, '', '');
    $calendar_id = $result['calendar_id'];

    if (date('Y') == date('Y', strtotime($date))) {
        $sql = "SELECT * from media_calendar where media_week <= (" . $calendar_id . ") AND media_year = YEAR('" . $date . "') ORDER BY media_week desc";
        // $date  = $result['max_date'];
        // $sql = "SELECT * from media_calendar where media_week <= (".$calendar_id. ") AND media_week_end > '$date' AND media_year = YEAR('".$date."') ORDER BY media_week desc";
    } else {
        $sql = "SELECT * from media_calendar where media_year = YEAR('" . $date . "') ORDER BY media_week desc";
    }
    $mediaCalendarRows = getResult($sql);

    return $mediaCalendarRows;
}

function getMediaCaledarRowsPrevYear($year)
{
    echo $sql = "SELECT * from media_calendar where media_year = '$year'";
    $mediaCalendarRows = getResult($sql);

    return $mediaCalendarRows;
}

function getWeeksDetails($mediaCalendarRows)
{
    $weekArray = array();

    if (!empty($mediaCalendarRows)) {
        foreach ($mediaCalendarRows as $key => $row) {
            $week = $row['media_week'];
            $array['media_week_start'] = $row['media_week_start'];
            $array['media_week_end'] = $row['media_week_end'];
            $weekArray[$week] = $array;
        }
    }
    return $weekArray;
}

function getMonthDetails($mediaCalendarRows)
{
    $mediaCalendarRows = array_reverse($mediaCalendarRows);
    $monthArray = array();

    if (!empty($mediaCalendarRows)) {
        foreach ($mediaCalendarRows as $key => $row) {
            $month = $row['media_month'];
            $array['media_month_start'] = $row['media_month_start'];
            $array['media_month_end'] = $row['media_month_end'];
            $monthArray[$month] = $array;
        }
    }

    return $monthArray;
}

function getYtdDetails($mediaCalendarRows)
{
    $mediaCalendarRows = array_reverse($mediaCalendarRows);
    $ytdArray = array();
    if (!empty($mediaCalendarRows)) {
        foreach ($mediaCalendarRows as $key => $row) {
            $ytd = $row['media_year'];
            $array['media_year_start'] = $row['media_year_start'];
            $array['media_week_end'] = $row['media_week_end'];

            if (strtotime($row['media_year_end']) > strtotime(time()) && $key > 0) {
                $endDate = $mediaCalendarRows[$key - 1]['media_week_end'];
            } else {
                $endDate = $row['media_year_end'];
            }
            $array['media_year_end'] = $endDate;
            $ytdArray[$ytd] = $array;
        }
    }
    return $ytdArray;
}

function getQuarterDetails($mediaCalendarRows)
{
    $mediaCalendarRows = array_reverse($mediaCalendarRows);
    $quarterArray = array();

    if (!empty($mediaCalendarRows)) {
        foreach ($mediaCalendarRows as $key => $row) {
            $quarter = $row['media_qtr'];
            $array['media_qtr_start'] = $row['media_qtr_start'];
            $array['media_qtr_end'] = $row['media_qtr_end'];
            $quarterArray[$quarter] = $array;
        }
    }

    return $quarterArray;
}

function execQuery($sql)
{
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
}

function getResult($sql, $type = 'FETCH_ASSOC')
{
    ini_set('memory_limit', '8192M');
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();

    if ($type == 'FETCH_ASSOC') {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $result;
}

function customDate($format = 'Y-m-d')
{
    $day = date('Y-m-d H:i:s');
    //$newDate    = date($format, strtotime($day . OFFSET));
    $newDate = date($format, strtotime(OFFSET, strtotime($day)));
    return $newDate;
}

function getRosDay($key = null)
{
    $array = array(
        "-------" => "-",
        "------S" => "Sunday",
        "-----S-" => "Saturday",
        "-----SS" => "Sat-Sun",
        "---T---" => "Thursday",
        "---T-SS" => "Thu&Sat-Sun",
        "---TF--" => "Thu-Fri",
        "--WT---" => "Wed-Thu",
        "-T-----" => "Tue",
        "-TW----" => "Tue-Wed",
        "-TWT---" => "Tue-Thu",
        "-TWTF--" => "Tue-Fri",
        "-TWTFS-" => "Tue-Sat",
        "M------" => "Monday",
        "M-----S" => "Sun-Mon",
        "M---FSS" => "Fri-Mon",
        "MTW----" => "Mon-Wed",
        "MTW-F--" => "Mon-Wed & Fri",
        "MTWTF--" => "Mon-Fri",
        "MTWTF-S" => "Sun-Fri",
        "MTWTFS-" => "Mon-Sat",
        "MTWTFSS" => "Mon-Sun",
    );

    if (!isset($key)) {
        return $array;
    } else {
        return $array[$key];
    }
}

function rosDayFull()
{
    $array = array(
        "-" => "-------",
        "Sunday" => "------S",
        "Saturday" => "-----S-",
        "Sat-Sun" => "-----SS",
        "Thursday" => "---T---",
        "Thu & Sat-Sun" => "---T-SS",
        "Thu-Fri" => "---TF--",
        "Wed-Thu" => "--WT---",
        "Tue" => "-T-----",
        "Tue-Wed" => "-TW----",
        "Tue-Fri" => "-TWTF--",
        "Tue-Sat" => "-TWTFS-",
        "Monday" => "M------",
        "Sun-Mon" => "M-----S",
        "Fri-Mon" => "M---FSS",
        "Mon-Wed" => "MTW----",
        "Mon-Wed & Fri" => "MTW-F--",
        "Mon-Fri" => "MTWTF--",
        "Sun-Fri" => "MTWTF-S",
        "Mon-Sat" => "MTWTFS-",
        "Mon-Sun" => "MTWTFSS",
    );

    return $array;
}

function checkSession()
{
    session_save_path(SESSION_DIR);
    if ((isset($_SESSION['username']) && $_SESSION['username'] == 'demo.user@drmetrix.com')) {
        return;
    }
    session_start();

/*
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT_TIME)) {
session_unset();
session_destroy();
}
 */
    $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
}

function clean($string, $separator = '-')
{
    $string = str_replace(' ', $separator, $string); // Replaces all spaces with hyphens.

    return preg_replace('/[^A-Za-z0-9]/', '', $string); // Removes special chars.
}

function getFunctionNameFromExcelReportTables($rows)
{
    extract($rows);
    return '_download' . ucfirst($report_length) . 'Form' . ucfirst($report_type) . 'Excel';
}

function getNetworkNamebyCode($netCode)
{
    if (!empty($netCode)) {
        $db = getConnection();
        $recordSql = "SELECT network_alias from network WHERE network_code IN ('" . $netCode . "') ";
        $networkRes = getResult($recordSql);
        if (isset($networkRes[0]['network_alias'])) {
            return $networkName = $networkRes[0]['network_alias'];
        }
    }
}

function getFileNameFromExcelReportTables($rows)
{
    extract($rows);
    $cols = '';
    //    Copy of DRM LibertyMutual TheWeatherChannel Airing Detail 09-26-16 till 10-02-16_time_10-04-16_173323.xlsx
    if ($excel_for == 'creative') {
        $cols = ", length";
    }

    $nameSql = "SELECT {$excel_for}_name " . $cols . " from {$excel_for} WHERE {$excel_for}_id = {$excel_for_id}";
    $rows = getResult($nameSql);
    $ext = '.xlsx';
    if ($no_of_records > MAX_NO_OF_RECORDS_FOR_EXCEL) {
        $ext = '.csv';
    }

    $fromDateArr = strtotime($from_date);
    $fromDateArr = date('m-d-y', $fromDateArr);

    $toDateArr = strtotime($to_date);
    $toDateArr = date('m-d-y', $toDateArr);

    $networkName = getNetworkNamebyCode($network_ids);

    if ($report_type == 'summary') {
        $report_type = 'Summary';
    } else if ($report_type == 'airing_detail') {
        $report_type = 'Airing Detail';
    }

    $display_length = isset($rows[0]["length"]) ? ' ' . $rows[0]["length"] . 'sec - ' : ' ';

    if ($screen == 'three') {
        $file_name = 'DRM ' . clean($rows[0]["{$excel_for}_name"], '_') . $display_length . $networkName . ' ' . $report_type . ' ' . $rosDay . ' ' . $rosTime . ' ' . $fromDateArr . ' till ' . $toDateArr . '_time_' . date('m-d-y_His') . $ext;
    } else if ($screen == 'two') {
        $file_name = 'DRM ' . clean($rows[0]["{$excel_for}_name"], '_') . $display_length . $networkName . ' ' . $report_type . ' ' . $fromDateArr . ' till ' . $toDateArr . '_time_' . date('m-d-y_His') . $ext;
    } else {
        $file_name = 'DRM ' . clean($rows[0]["{$excel_for}_name"], '_') . $display_length . $report_type . ' ' . $fromDateArr . ' till ' . $toDateArr . '_time_' . date('m-d-y_His') . $ext;
    }

    $file_name = str_replace("'", "", $file_name);
    $file_name = preg_replace('!\s+!', ' ', $file_name);

    return $file_name;
}

function getEstimatedDownloadTime($id)
{
    $sql = "SELECT SUM(no_of_records) no_of_records FROM `excel_exports` WHERE id < $id AND status IN ('queued', 'inprogress') group by status ";
    $rows = getResult($sql);

    if (count($rows) == 1) {
        return gmdate("H:i:s", $rows[0]['no_of_records'] * TIME_REQUIRED_FOR_ONE_RECORD);
    } else {
        return '---------------';
    }
}

function update_excel_progress($id, $current_row)
{
    if (!empty($id)) {
        $db = getConnection();
        $recordSql = "SELECT no_of_records from excel_exports WHERE id = '" . $id . "' ";
        $no_of_records = getResult($recordSql);
        $no_of_records = $no_of_records[0]['no_of_records'];

        $no_of_records = $no_of_records == 0 ? 1 : $no_of_records;

        $progress_cal = ($current_row / $no_of_records) * 100;

        if ($progress_cal > 95 || $current_row == 0) {
            $progress_cal = 95;
        }

        $sql = "UPDATE excel_exports SET progress = '" . $progress_cal . "' WHERE id = '" . $id . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
}

function createDir($user_id)
{
    shell_exec('chown -fR www-data:www-data ' . str_replace('/staging', '', dirname(__FILE__)) . '/../' . LARGE_EXCEL_DOWNLOAD_PATH);
    checkDirAndCreate(str_replace('/staging', '', dirname(__FILE__)) . '/../' . LARGE_EXCEL_DOWNLOAD_PATH . date('Y-m-d'));

    shell_exec('chown -fR www-data:www-data ' . str_replace('/staging', '', dirname(__FILE__)) . '/../' . LARGE_EXCEL_DOWNLOAD_PATH . date('Y-m-d') . '/');
    checkDirAndCreate(str_replace('/staging', '', dirname(__FILE__)) . '/../' . LARGE_EXCEL_DOWNLOAD_PATH . date('Y-m-d') . '/' . date('H_i_s'));

    shell_exec('chown -fR www-data:www-data ' . str_replace('/staging', '', dirname(__FILE__)) . '/../' . LARGE_EXCEL_DOWNLOAD_PATH . date('Y-m-d') . '/' . '/' . date('H_i_s'));
    checkDirAndCreate(str_replace('/staging', '', dirname(__FILE__)) . '/../' . LARGE_EXCEL_DOWNLOAD_PATH . date('Y-m-d') . '/' . date('H_i_s') . '/' . $user_id);

    $dir_name = str_replace('/staging', '', dirname(__FILE__)) . '/../' . LARGE_EXCEL_DOWNLOAD_PATH . date('Y-m-d') . '/' . date('H_i_s') . '/' . $user_id . '/';
    return $dir_name;
}

function checkDirAndCreate($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        shell_exec('chown -fR www-data:www-data ' . $path);
    }
}

function transformDate($date)
{
    $date = date("n-j-Y", strtotime($date));
    return $date;
    // $dateArr = explode('-', $date);
    // return $dateArr[1] . '-' . $dateArr[2] . '-' . $dateArr[0];
}

function getDateDifferenceInHours()
{
    $t1 = StrToTime('2016-04-14 11:30:00');
    $t2 = StrToTime('2016-04-12 12:30:00');
    $diff = $t1 - $t2;
    $hours = $diff / (60 * 60);
}

function getFilename($file_name)
{
    $arr = explode('/', $file_name);
    return $arr[count($arr) - 1];
}

function formatType($name)
{
    return ucwords(str_replace('_', ' ', $name));
}

function getExcelFiledownloadablePath($file_path)
{
    $file_name = explode('../excels/', $file_path);
    return 'http://' . HOST . '/drmetrix/excels/' . $file_name[1];
}

function sendMail($userId, $file_path, $id, $email)
{
    $userSql = "SELECT * FROM user where user_id = $userId";
    $user = getResult($userSql);
    $user = $user[0];

    extract($user);
    $subject = 'DRMetrix - File Download';

    $download_file_path = 'http://' . HOST . '/drmetrix/api/index.php/downloadClientFiles?code=' . base64_encode("email=$email&id=$id");

    $message = $first_name . ',<br/><br/>';
    $message .= 'The following requested file is now ready for you to download: <br/><a href="' . $download_file_path . '">' . $file_path . '</a><br/><br/>
    Should you ever need assistance, please email <a href="mailto:support@drmetrix.com">support@drmetrix.com</a><br><br><br>
    Thank you,<br>
    DRMetrix';

    $headers = 'From: info@drmetrix.com' . "\r\n" .
        'MIME-Version: 1.0' . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    require_once 'PHPMailer/class.phpmailer.php';
    try {
        $mail = new PHPMailer(); //New instance, with exceptions enabled
        $mail->IsSendmail(); // tell the class to use Sendmail
        $body = $message;
        $to = $username;
        $mail->SetFrom('info@drmetrix.com', 'DRMetrix');
        $mail->AddAddress($to);
        $mail->Subject = $subject;
        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->WordWrap = 80; // set word wrap
        $mail->MsgHTML($body);
        $mail->IsHTML(true); // send as HTML
        $mail->Send();
        echo json_encode(array('status' => 1));
    } catch (phpmailerException $e) {
        echo '{"status"=>0,"error":{"text":' . $e->getMessage() . '}}';
    }
}

function getLastMediaWeekDetail()
{
    $date_range = getPreviousDates('week', 1, '', 'current');
    $this_week['sd'] = $date_range['min_date'];
    $this_week['ed'] = $date_range['max_date'];
    $this_week['calendar_id'] = $date_range['calendar_id'];

    $this_week['start_date'] = date("m/d/Y", strtotime($date_range['min_date']));
    $this_week['end_date'] = date("m/d/Y", strtotime($date_range['max_date']));
    return $this_week;
}

function getPreviousDates($type, $num, $date = '', $flag = '')
{
    $db = getConnection();
    
    $year = empty($date) ?  customDate('Y') : date('Y',  strtotime($date));
    $date = empty($date) ?  customDate('Y-m-d') : $date;
    
    $media_type_count   = "SELECT media_{$type} as media_type, media_year FROM `media_calendar` WHERE  `media_{$type}_start` <= '$date' AND `media_{$type}_end` >= '$date'";
    $media_count_result = execute_query_get_result($media_type_count, 'FETCH_OBJ');
    /*
    $stmt = $db->prepare($media_type_count);
    $stmt->execute();
    $media_count_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    */
// show($media_count_result, 1);
    $year = $media_count_result[0]->media_year;
    if($flag == 'current') {
        // show($media_count_result);
        $result = $media_count_result[0]->media_type - 1;
    } else {
        $result = ($media_count_result[0]->media_type);
    }
    // show($result);
    if($result == 0){
        $year = $year - 1;
        $max_media_type = "SELECT MAX(media_{$type}) as media_type FROM `media_calendar` WHERE media_year = $year";

        $media_count_result = execute_query_get_result($max_media_type, 'FETCH_OBJ');
        /*
        $stmt = $db->prepare($max_media_type);
        $stmt->execute();
        $media_count_result = $stmt->fetchAll(PDO::FETCH_OBJ);
         */
        $result = $media_count_result[0]->media_type;
    }

    $num = $num - 1;
  
    $sql = "SELECT MIN(`media_{$type}_start`) as min_date, MAX(`media_{$type}_end`) as max_date, media_{$type} as calendar_id, media_year from media_calendar where  `media_year` = $year AND media_{$type}  BETWEEN  $result - $num AND $result GROUP BY calendar_id, media_year" ;
    
    $date_range = execute_query_get_result($sql, 'FETCH_ASSOC');  
    /*
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $date_range = $stmt->fetchAll(PDO::FETCH_ASSOC);
     */
    $date_range = array_reverse($date_range);
    return $date_range[0];
}

function getTimeDiffInMin($from_date, $to_date)
{
    $to_time = strtotime($to_date);
    $from_time = strtotime($from_date);
    return round(abs($to_time - $from_time) / 60, 2);
}

function getDatesForTrendGraph($requestEndDate)
{
    $db = getConnection();
    $min_date_before = date("Y-m-d", strtotime($requestEndDate . "-12 week"));

    $current_media_week = "SELECT media_week, media_week_start,media_week_end FROM media_calendar WHERE media_week_start <= '" . $min_date_before . "' AND  media_week_end >= '" . $min_date_before . "'";

    $stmt = $db->prepare($current_media_week);
    $stmt->execute();
    $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($dates[0])) {
        $dates[0]['media_week_start'] = LIFETIME_START_DATE;
    }
    $endDateBeforeWeek['min_date'] = $dates[0]['media_week_start'];
    $endDateBeforeWeek['max_date'] = $requestEndDate;

    return $endDateBeforeWeek;

}

function trackResults($string)
{
    if ($string != '') {
        $parsedString = parse_str($string, $getResArr);
    }

    if ($getResArr['page'] == 1 && $getResArr['sidx'] == 'spend_index') {
        switch ($getResArr['c']) {
            case 1:
                $classification = 'All Short Form';
                break;
            case 2:
                $classification = 'Short Form Products';
                break;
            case 3:
                $classification = 'Lead Generation';
                break;
            case 4:
                $classification = 'Brand/DR';
                break;
            case 5:
                $classification = 'AsOnTV Retail Rankings';
                break;
            case 6:
                $classification = '28.5m Creative';
                break;
            case 7:
                $classification = 'AsOnTV Retail Rankings (28.5m)';
                break;
        }

        switch ($getResArr['type']) {
            case 1:
                $type = 'Brand';
                break;
            case 0:
                $type = 'Advertiser';
                break;
        }

        $dateRange = $getResArr['val'];
        $dateRange = explode('_', $dateRange);
        $dateRange = $dateRange[0];

        switch ($dateRange) {
            case 1:
                $dateRange = 'Media Week';
                break;
            case 2:
                $dateRange = 'Media Month';
                break;
            case 3:
                $dateRange = 'Quarter';
                break;
            case 4:
                $dateRange = 'YTD/All';
                break;
            case 5:
                $dateRange = 'Lifetime';
                break;
        }

        $categories = $getResArr['cat'];
        if ($categories == '') {
            $categories = 'all';
        }

        switch ($getResArr['spanish']) {
            case 0:
                $language = 'English';
                break;
            case 1:
                $language = 'Spanish';
                break;
        }

        $userEmail = $_SESSION['username'];
        $time = gmdate('Y-m-d H:i:s');
        $db = getConnection();
        $insertSql = "INSERT INTO result_log (email, start_date, end_date, date_range, classification, product_type, categories, language, response_type, time) VALUES ('" . $userEmail . "','" . $getResArr['sd'] . "', '" . $getResArr['ed'] . "','" . $dateRange . "','" . $classification . "','" . $type . "','" . $categories . "','" . $language . "','" . $getResArr['responseType'] . "', '$time')";
        $stmt = $db->prepare($insertSql);
        $stmt->execute();
    }
}

function dateFormateForGridDate($data)
{
    $raw_date = date_create($data);
    $f_date = date_format($raw_date, 'm/d/y h:i A');
    return $f_date;
}

function reorderResultForRanking($result)
{
    $new_result = $result;
    $return_result = array();
    foreach ($result as $resultK => $resultV) {
        $result[$resultK]->airings = $resultV->airings / $resultV->category_count;

        //changing national and local airings
        if (!empty($result[$resultK]->nat)) {
            $result[$resultK]->nat = $resultV->nat / $resultV->category_count;
        }

        if (!empty($result[$resultK]->loc)) {
            $result[$resultK]->loc = $resultV->loc / $resultV->category_count;
        }

        if (!empty($resultV->spend_index)) {
            $result[$resultK]->spend_index = floatval($resultV->spend_index / $resultV->category_count);
            $new_result[$resultK] = $result[$resultK]->spend_index;
        } else {
            $new_result[$resultK] = $result[$resultK]->airings;
        }

    }

    arsort($new_result);

    foreach ($new_result as $key => $value) {
        $return_result[] = $result[$key];
    }

    return $return_result;
}

function reorderResultForRankingArray($result)
{
    foreach ($result as $resultK => $resultV) {
        foreach ($resultV as $key => $value) {
            $result[$resultK][$key]['airings'] = $result[$resultK][$key]['airings'] / $result[$resultK][$key]['category_count'];
            $result[$resultK][$key]['projected_score'] = $result[$resultK][$key]['projected_score'] / $result[$resultK][$key]['category_count'];

            if (!empty($result[$resultK][$key]['Airings'])) {
                $result[$resultK][$key]['Airings'] = $result[$resultK][$key]['Airings'] / $result[$resultK][$key]['category_count'];
            }
        }
    }

    return $result;
}

function reorderResultForRankingArrayForExcel($result)
{
    foreach ($result as $key => $value) {
        $result[$key]['count'] = $result[$key]['count'] / $result[$key]['category_count'];
        $result[$key]['national'] = $result[$key]['national'] / $result[$key]['category_count'];
        $result[$key]['local'] = $result[$key]['local'] / $result[$key]['category_count'];
    }

    return $result;
}

function getArrayForNetworkTab(&$requestData, $query_function)
{
    $params = array();
    $where_flag = ' ';
    $advOrBrandId = '';
    $advOrBrandName = '';
    $brand_array = array();
    $new_brand_array = array();
    $final_array = array();
    $json_data = array();
    $data = array();
    $request = Slim::getInstance()->request();
    $query_string = $request->getBody();
    if (strpos($query_string, 'startDate') !== false) {
        $_SESSION['filter_data'] = $query_string;
        $_SESSION['filter_type'] = 'network';
    }
    $raw_data = array();
    $set_one = explode('&', $query_string);
    if( !isset($set_one) || count($set_one) == 0 ) {
        $set_one = $raw_data = $requestData  = $_REQUEST;
    } else {
        foreach ($set_one as $k => $v) {
            $raw_data = explode('=', $v);
            $requestData[$raw_data[0]] = $raw_data[1];
        }
    }
    $sd = $requestData['sd'];
    $ed = $requestData['ed'];
    $val = $requestData['val'];
    $c = urldecode($requestData['c']);

    if (isset($requestData['export'])) {
        $tab = 1;
    } else {
        $tab = $requestData['type'];
    }

    if (isset($requestData['creative_ids'])) {
        $creative_ids = urldecode($requestData['creative_ids']);
    }
    if (isset($requestData['brands_ids'])) {
        $brands_ids = urldecode($requestData['brands_ids']);
    }
    $cat = rtrim(urldecode($requestData['cat']), ",");
    $cat = rtrim($cat, "all,");
    $catIn = '(' . $cat . ')';
    $uncheckedCatIn = '(' . rtrim(urldecode($requestData['unchecked_category']), ",") . ')';

    switch ($tab) {
        case 1:
            $advOrBrandId = 'b.brand_id';
            $advOrBrandName = 'b.brand_name';
            $brandOrCreative = 'c.creative_id';
            break;

        case 0:
            $advOrBrandId = 'adv.adv_id';
            $advOrBrandName = 'adv.display_name';
            $brandOrCreative = 'b.brand_id';
            break;

        case 3:
            $advOrBrandId = 'c.creative_id';
            $advOrBrandName = 'c.creative_name';
            $brandOrCreative = 'c.creative_id';
            break;

        default:
            # code...
            break;
    }

    $network_code = isset($requestData['network_code']) ? urldecode($requestData['network_code']) : '';
    $network_id = isset($requestData['network_id']) ? urldecode($requestData['network_id']) : '';

    $network_array      = explode(",", $network_code);
    $network_id_array   = explode(",", $network_id);
    sort($network_array);

    $requestData['network_array']       = $network_array;
    $requestData['network_id_array']    = $network_id_array;
    $_resp_type = urldecode($requestData['responseType']);
    $responseType = "(" . $_resp_type . ")";
    $spanish = urldecode($requestData['spanish']);
    $lang_type = $spanish == 0 ? '' : 'sp-';
    $active_flag = isset($requestData['flag']) ? $requestData['flag'] : '2';
    $count_unchecked_cat = $requestData['length_unchecked'];
    $creative_durations = urldecode($requestData['creative_duration']);
    $brand_classification = '';
    if ($count_unchecked_cat <= CATEGORY_UNCHECKED_LIMIT && $count_unchecked_cat != 0) {
        $categories = ' AND (b.main_sub_category_id NOT IN ' . $uncheckedCatIn . ' OR b.alt_sub_category_id NOT IN ' . $uncheckedCatIn . ') ';
    } else {
        $categories = !$cat || $cat == 'all' ? '' : ' AND (b.main_sub_category_id IN ' . $catIn . ' OR b.alt_sub_category_id IN ' . $catIn . ')';
    }

    if ($c > 5) {
        $active_col = 'long_active';
    } else {
        $active_col = 'short_active';
    }

    if ($active_flag == 1) {
        $where_flag = ' AND b.' . $active_col . ' = 1 ';
        if ($tab == 0) {
            $where_flag = ' AND adv.' . $active_col . ' = 1 ';
        }
    } else if ($active_flag == 0) {
        $where_flag = ' AND b.' . $active_col . ' = 0 ';
        if ($tab == 0) {
            $where_flag = ' AND adv.' . $active_col . '= 0 ';
        }
    }

    $brand_classification = getBrandClassification($c, $creative_durations);

    $params['spanish'] = $spanish;
    $params['sd'] = $sd;
    $params['ed'] = $ed;
    $params['where_flag'] = $where_flag;
    $params['brand_classification'] = $brand_classification['brand_classification'];
    $params['airings_length'] =       $brand_classification['length_condition'];
    $params['categories'] = $categories;
    $params['responseType'] = $responseType;
    $params['network'] = implode('", "', $network_array);
    $params['network_id'] = implode(', ', $network_id_array);
    $params['advOrBrandId'] = $advOrBrandId;
    $params['advOrBrandName'] = $advOrBrandName;
    $params['brandOrCreative'] = $brandOrCreative;
    $params['tab'] = $tab ;
    if (isset($requestData['subgird']) && $requestData['subgird'] == 'adv_brand') {
        $params['subgird'] = $requestData['subgird'];
        $params['adv_id'] = $requestData['adv_id'];
    }
    $params['creative_ids'] = isset($creative_ids) ? $creative_ids : "";
    $params['brands_ids'] = isset($brands_ids) ? $brands_ids : "";

    $requestData = array_merge($requestData, $params);
    $params['breaktype'] = isset($requestData['breaktype']) ? $requestData['breaktype'] : null;
    $result = get_query_result($query_function, $params, 'FETCH_ASSOC');
    foreach ($result as $key => $value) {
        foreach ($network_array as $n1 => $n2) {
            if ($n2 == $value['network_code']) {
                $brand_array[$value['id']][$n2] = $value;
                $new_brand_array[$value['id']] = $value;
            }
        }
    }
    foreach ($brand_array as $id => $nw_array) {
        $category_count = getCatNumByBrandId($new_brand_array[$id]['_brand_id'], $cat);
        //$category_count                                     = 1;

        foreach ($network_array as $n1 => $n2) {
            $brand_array[$id][$n2]['category_count'] = $category_count;
            //$brand_array[$id][$n2]['category_count']        = 1;

            if (!array_key_exists($n2, $nw_array)) {
                $brand_array[$id][$n2]['network_code'] = $n2;
                $brand_array[$id][$n2]['network_id'] =$new_brand_array[$id]['network_id'];
                $brand_array[$id][$n2]['id'] = $new_brand_array[$id]['id'];
                $brand_array[$id][$n2]['dpi'] = $new_brand_array[$id]['dpi'];
                $brand_array[$id][$n2]['projected_score'] = 0;
                $brand_array[$id][$n2]['adv_id'] = $new_brand_array[$id]['adv_id'];
                $brand_array[$id][$n2]['name'] = $new_brand_array[$id]['name'];
                $brand_array[$id][$n2]['airings'] = 0;
                $brand_array[$id][$n2]['category'] = 'temp';
                $brand_array[$id][$n2]['creative_count'] = $new_brand_array[$id]['creative_count'];
                $brand_array[$id][$n2]['creative_name'] = $new_brand_array[$id]['creative_name'];
                $brand_array[$id][$n2]['spanish'] = $new_brand_array[$id]['spanish'];
                $brand_array[$id][$n2]['no_of_brands'] = $new_brand_array[$id]['no_of_brands'];
                $brand_array[$id][$n2]['thumbnail'] = $new_brand_array[$id]['thumbnail'];
                $brand_array[$id][$n2]['_network_code'] = $new_brand_array[$id]['_network_code'];
                $brand_array[$id][$n2]['brand_id'] = $new_brand_array[$id]['_brand_id'];
                $brand_array[$id][$n2]['advertiser_id'] = $new_brand_array[$id]['_adv_id'];
                $brand_array[$id][$n2]['brand_name'] = $new_brand_array[$id]['_brand_name'];
                $brand_array[$id][$n2]['advertiser_name'] = $new_brand_array[$id]['_advertiser_name'];
                $brand_array[$id][$n2]['_brand_id'] = $new_brand_array[$id]['_brand_id'];
                $brand_array[$id][$n2]['_adv_id'] = $new_brand_array[$id]['_adv_id'];
                $brand_array[$id][$n2]['_brand_name'] = $new_brand_array[$id]['_brand_name'];
                $brand_array[$id][$n2]['_advertiser_name'] = $new_brand_array[$id]['_advertiser_name'];
                $brand_array[$id][$n2]['need_help'] = $new_brand_array[$id]['need_help'];
//                    $brand_array['creatives']     = $new_brand_array[$id]['creatives'];
            }
        }
    }
    $brand_array = reorderResultForRankingArray($brand_array);

    if ($tab == 0) {
        // $brand_array = processAdvForCategory($brand_array, $requestData, $params);
    }
    foreach ($brand_array as $brand_name => $brand) {
        $creative_ids = '';
        $brand_projected_score = 0;
        $brand_row_airings = 0;

        foreach ($brand as $key => $network) {
            if (empty($network['creatives'])) {
                $network['creatives'] = '';
            }

            if (empty($network['thumbnail'])) {
                $network['thumbnail'] = '';
            }

            $creative_ids = $creative_ids . ',' . $network['creatives'];
            $id = $network['id'];
            $brand_projected_score = $brand_projected_score + $network['projected_score'];
            $brand_row_airings = $brand_row_airings + $network['airings'];
        }

        $creatives = trim($creative_ids, ',');
        $creatives = preg_replace("/,+/", ",", $creatives);
        $creatives = explode(',', $creatives);
        $creatives = array_unique($creatives);
        $creative_count = count(array_unique($creatives));
        $brand_array[$brand_name]['_creatives_']['creative_ids'] = trim(implode(',', $creatives), ',');

        //temp values in _creatives_ to avoid network column errors
        $brand_array[$brand_name]['_creatives_']['id'] = $id;
        $brand_array[$brand_name]['_creatives_']['network_id'] = 'temp';
        $brand_array[$brand_name]['_creatives_']['network_code'] = 'temp';
        $brand_array[$brand_name]['_creatives_']['dpi'] = 0;
        $brand_array[$brand_name]['_creatives_']['creatives'] = 'temp';
        $brand_array[$brand_name]['_creatives_']['projected_score'] = 0;
        $brand_array[$brand_name]['_creatives_']['adv_id'] = 'temp'; //$network['advertiser_id'];
        $brand_array[$brand_name]['_creatives_']['name'] = $network['name'];
        $brand_array[$brand_name]['_creatives_']['airings'] = 1;
        $brand_array[$brand_name]['_creatives_']['category'] = 'temp';
        $brand_array[$brand_name]['_creatives_']['no_of_brands'] = $creative_count;
        $brand_array[$brand_name]['_creatives_']['creative_count'] = $creative_count;
        $brand_array[$brand_name]['_creatives_']['brand_count'] = $creative_count;
        $brand_array[$brand_name]['_creatives_']['creative_name'] = $network['name'];
        $brand_array[$brand_name]['_creatives_']['spanish'] = $network['spanish'];
        $brand_array[$brand_name]['_creatives_']['thumbnail'] = $network['thumbnail'];
        $brand_array[$brand_name]['_creatives_']['_network_code'] = 'temp';
        $brand_array[$brand_name]['_creatives_']['Spend Index'] = $brand_projected_score;
        $brand_array[$brand_name]['_creatives_']['Airings'] = $brand_row_airings;
        $brand_array[$brand_name]['_creatives_']['brand_id'] = 'temp';
        $brand_array[$brand_name]['_creatives_']['_brand_name'] = $network['_brand_name'];
        $brand_array[$brand_name]['_creatives_']['_advertiser_name'] = $network['_advertiser_name'];
        $brand_array[$brand_name]['_creatives_']['_brand_id'] = $network['_brand_id'];
        $brand_array[$brand_name]['_creatives_']['_adv_id'] = $network['_adv_id'];
        $brand_array[$brand_name]['_creatives_']['category_count'] = 1;
        $brand_array[$brand_name]['_creatives_']['need_help'] = $network['need_help'];
        $brand_array[$brand_name]['_creatives_']['classification'] = $c;

        if ($tab == 3) {
            $params['condition'] = !empty($requestData['brand_id']) ? $requestData['brand_id'] : '';
        }
    }

    $requestData['networkTab'] = empty($requestData['networkTab']) ? 'Spend Index' : $requestData['networkTab'];

    if (isset($requestData['brand_id']) || isset($requestData['adv_id'])) {
        $total_spend = $_SESSION['projected_score'];
    } else {
        // $total_spend = getProjectedSpend($params);
        $total_spend = 1;
        $_SESSION['projected_score'] = $total_spend;
    }

    $requestData['total_spend'] = $total_spend;
    $brand_array = sortArray($brand_array, '_creatives_', $requestData['networkTab'], $total_spend, 1);
    return $brand_array;
}

function processAdvArray($params)
{
    extract($params);

    // $result                     = get_query_result('__query_get_airings_for_advertisers_networks', $params, 'FETCH_ASSOC');
    $result = get_query_result('__query_get_airings_brands_networks', $params, 'FETCH_ASSOC');
    return $result;
}

function getProjectedSpend($params)
{

    if (defined('PROJECTED_SPEND')) {
        return PROJECTED_SPEND;
    }

    extract($params);

    $result = get_query_result('__query_get_projected_spend_for_networks', $params, 'FETCH_ASSOC');

    if (!empty($result)) {
        foreach ($result as $key => $value) {
            $return[] = $result[$key]['rate'] / $result[$key]['category_count'];
        }

        $return = array_sum($return);
    } else {
        $return = 1;
    }

    if (!defined('PROJECTED_SPEND')) {
        define('PROJECTED_SPEND', $return);
    }

    return $return;
}

function sortArray($array, $first_level_key, $second_level_key, $total_spend, $recalculate_spend_index = 1)
{
    $return_array = $array;
    if (count($array) != 0) {
        switch ($second_level_key) {
            case 'spend_index':
                $second_level_key = 'Spend Index';
                break;

            case 'airings':
                $second_level_key = 'Airings';
                break;
        }

        foreach ($array as $array_key => $value) {
            $new_array[$array_key] = $value[$first_level_key][$second_level_key];
        }

        arsort($new_array);

        $return_array = array();

        foreach ($new_array as $key => $value) {

            $return_array[$key] = $array[$key];

            if ($recalculate_spend_index == 1) {

                /* Uncomment if topmost Spend index should be 100
                if (empty($total_spend)) {
                $total_spend = $value;
                }
                 */
                //////

                /*
                $request                            = Slim::getInstance()->request();
                $query_string                       = $request->getBody();
                $set_one                            = explode('&', $query_string);
                $raw_data = array();
                foreach($set_one as $k =>$v){
                $raw_data                       = explode('=',$v);
                $requestData[$raw_data[0]]      = $raw_data[1];
                }

                if (empty($requestData['brand_id']) && empty($requestData['adv_id']) && empty($total_spend)) {
                $total_spend = $_SESSION['total_spend'] = array_sum($new_array);
                } else {
                $total_spend = $_SESSION['top_spend'];
                }
                 */
                /////

                $spendIndexCalculate['projected_score'] = $value;
                $spendIndexCalculate['classification'] = $return_array[$key][$first_level_key]['classification'];
                $spendIndexCalculate['sum'] = $total_spend;
                $return_array[$key][$first_level_key]['Spend Index']        = $spendIndexCalculate['projected_score'];
                $return_array[$key][$first_level_key]['number_Spend Index'] = $value;
                // $value = $value / $total_spend * 100;
                // $return_array[$key][$first_level_key]['Spend Index'] = $value;
                // $return_array[$key][$first_level_key]['Spend Index'] = custom_round($return_array[$key][$first_level_key]['Spend Index']);
            }

        }
    }

    return $return_array;
}

function getUserCompany($user_id)
{
    $db = getConnection();
    $sql = "SELECT company_id, role FROM user WHERE user_id = '" . $user_id . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    if ($get_result[0]->role == 'admin') {
        return $get_result[0]->company_id;
    } else if ($get_result[0]->role == 'user') {
        $sql = "SELECT admin_id FROM admin_user WHERE user_id = '" . $user_id . "'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $get_user_result = $stmt->fetchAll(PDO::FETCH_OBJ);
        return getUserCompany($get_user_result[0]->admin_id);
    }
}

function getCompanyInfoById($company_id)
{
    $db = getConnection();
    $sql = "SELECT company_type,network_tab,staging_access,zoho_account_id,eula_flag,company_name,company_size,revenue FROM company WHERE id = '" . $company_id . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $get_result;
}

function getCreativeDataByBrandId($brand_id)
{
    $db = getConnection();
    $sql = "SELECT creative_id FROM creative WHERE brand_id = '" . $brand_id . "' AND class != 'BRAND';";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $get_result;
}

function processAdvForCategory($brand_array, $requestData, $params)
{
    $advOrBrandId = 'b.brand_id';
    $advOrBrandName = 'b.brand_name';
    $brandOrCreative = 'c.creative_id';
    $params['advOrBrandId'] = $advOrBrandId;
    $params['advOrBrandName'] = $advOrBrandName;
    $params['brandOrCreative'] = $brandOrCreative;

    $requestData = array_merge($requestData, $params);
    $brand_array_result = get_query_result('__query_get_airings_brands_networks', $params, 'FETCH_ASSOC');

    foreach ($brand_array_result as $brand) {
        $_network_code = $brand['network_code'];
        $adv_id = $brand['_adv_id'];
        $brand['category_count'] = getCatNumByBrandId($brand['_brand_id'], $requestData['cat']);
        //$brand['category_count']                                        = 1;

        $processed_adv[$adv_id][$_network_code]['airings'] = empty($processed_adv[$adv_id][$_network_code]['airings']) ? 0 : $processed_adv[$adv_id][$_network_code]['airings'];
        $processed_adv[$adv_id][$_network_code]['projected_score'] = empty($processed_adv[$adv_id][$_network_code]['projected_score']) ? 0 : $processed_adv[$adv_id][$_network_code]['projected_score'];

        $processed_adv[$adv_id][$_network_code]['airings'] = $processed_adv[$adv_id][$_network_code]['airings'] + ($brand['airings'] / $brand['category_count']);
        $processed_adv[$adv_id][$_network_code]['projected_score'] = $processed_adv[$adv_id][$_network_code]['projected_score'] + ($brand['projected_score'] / $brand['category_count']);
    }

    foreach ($brand_array as $adv_id => $adv) {
        foreach ($adv as $network) {
            $__network_code = $network['network_code'];
            $processed_adv[$adv_id][$__network_code]['airings'] = empty($processed_adv[$adv_id][$__network_code]['airings']) ? 0 : $processed_adv[$adv_id][$__network_code]['airings'];
            $processed_adv[$adv_id][$__network_code]['projected_score'] = empty($processed_adv[$adv_id][$__network_code]['projected_score']) ? 0 : $processed_adv[$adv_id][$__network_code]['projected_score'];

            $brand_array[$adv_id][$__network_code]['airings'] = $processed_adv[$adv_id][$__network_code]['airings'];
            $brand_array[$adv_id][$__network_code]['projected_score'] = $processed_adv[$adv_id][$__network_code]['projected_score'];
        }
    }

    return $brand_array;
}

function processAdvRankingForCategory($adv_array, $brand_array_result)
{

    $brand_array_result = getBrandAiringsArray($brand_array_result, 'brand_id');

    foreach ($adv_array as $key => $adv) {
        $adv_id = $adv->adv_id;
        $airing_details = getAiringsDetailsFromArray($adv_id, $brand_array_result);
        $adv_array[$key]->airings = $airing_details['airings'];
        $adv_array[$key]->spend_index = $airing_details['spend_index'];
        $adv_array[$key]->category_count = 1;
    }

    return $adv_array;
}

function getAiringsDetailsFromArray($adv_id, $brand_array_result)
{
    $airing_detail['airings'] = $brand_array_result[$adv_id]['airings'];
    $airing_detail['spend_index'] = $brand_array_result[$adv_id]['spend_index'];

    return $airing_detail;
}

function getBrandAiringsArray($array, $key)
{

    foreach ($array as $_key => $_value) {
        $_value = get_object_vars($_value);
        $adv_id = $_value['adv_id'];

        $new_array[$adv_id]['airings'] = empty($new_array[$adv_id]['airings']) ? 0 : $new_array[$adv_id]['airings'];
        $new_array[$adv_id]['spend_index'] = empty($new_array[$adv_id]['spend_index']) ? 0 : $new_array[$adv_id]['spend_index'];

        $new_array[$adv_id]['airings'] = $new_array[$adv_id]['airings'] + ($_value['airings'] / $_value['category_count']);
        $new_array[$adv_id]['spend_index'] = $new_array[$adv_id]['spend_index'] + ($_value['spend_index'] / $_value['category_count']);
    }

    if (!isset($new_array)) {
        return $array;
    }

    return $new_array;
}

function createCatNetArray()
{
    $net = array();
    $cat = array();
    $netcarArr = array();

    $get_result = get_query_result('__query_network_alias', '', 'FETCH_ASSOC');
    foreach ($get_result as $result) {
        $net[$result['network_code']] = $result['network_alias'];
    }

    $get_res = get_query_result('__query_get_subcategory_detail', '', 'FETCH_ASSOC');
    foreach ($get_res as $res) {
        $cat[$res['sub_category_id']]['cat_name'] = $res['category'];
        $cat[$res['sub_category_id']]['sub_cat_name'] = $res['sub_category'];
    }

    $netcarArr['networks'] = $net;
    $netcarArr['categories'] = $cat;
    return $netcarArr;
}

function getNetworkName($codes, $netCatArray)
{
    $networks = $netCatArray['networks'];
    $net_names = array();
    $code_list = explode(',', $codes);
    foreach ($code_list as $code) {
        $net_names[$code] = $networks[$code];
    }
    return $net_names;
}

function getCategoryData($subcat_ids, $netCatArray)
{
    $categories = $netCatArray['categories'];
    $cat_names = array();
    $cat_list = explode(',', $subcat_ids);
    foreach ($cat_list as $cat) {
        $cat_names[$cat] = $categories[$cat];
    }
    return $cat_names;
}

function getNetCatNames()
{
    $netCatArray = createCatNetArray();
    $net_codes = 'MTV2,FNC,CNNH,LMN';
    $subcat_ids = '1009,1015,1025,1032,1053';
    $net_name = getNetworkName($net_codes, $netCatArray);
    print_r($net_name);
    $cat_values = getCategoryData($subcat_ids, $netCatArray);
    print_r($cat_values);
}
function custom_round($value)
{
    $round_multiply = 10 * 10;
    for ($i = 1; $i < NO_OF_DIGITS_FOR_NETWORK_MARKET_SHARE - 1; $i++) {
        $round_multiply = $round_multiply * 10;
    }

    $round_divide = 9 / 10;
    for ($i = 1; $i < NO_OF_DIGITS_FOR_NETWORK_MARKET_SHARE - 1; $i++) {
        $round_divide = $round_divide / 10;
    }

    if ($value <= $round_divide || $value == 0) {
        return round($value * $round_multiply) / $round_multiply;
    } else {
        return round($value * 100) / 100;
    }
}

// function getCreativeVideoId($creative_id, $network_code, $tab)
function getCreativeVideoId($creative_id, $network_id, $tab, $parameters = array())
{
    $network_filter = '';
    $get_result = array();
    if (!empty($network_id) && $network_id != "") {
        $network_filter = " AND a.`network_id` IN ($network_id) ";
    }
    $params['creative_id']      = $creative_id;
    $params['network_filter']   = $network_filter;
    $params['tab']              = $tab;
    $params['where']            = !empty($parameters) ?  $parameters['where']  : '';
    $get_result = get_query_result('__query_get_video_id', $params, 'FETCH_OBJ');
    if (empty($get_result)) {
        $params['network_filter'] = '';
        $get_result = get_query_result('__query_get_video_id', $params, 'FETCH_OBJ');
    }
    if (empty($get_result)) {
        $get_result = getAiringDetailByCreativeId($params);
    }

    return $get_result;
}

//last Last month
function getLastMediaMonth()
{

    $date_range = getPreviousDates('month', 1, '', 'current');  
    if($date_range['calendar_id'] == 12){
        
        $params['prev_year']   = $date_range['media_year'];
        $params['calendar_id'] = $date_range['calendar_id'];

        $date_range = get_query_result('__SQL_GET_LAST_MEDIA_MONTH', $params, 'FETCH_ASSOC');
        $date_range = $date_range[0];
    }

    $this_month['sd'] = $date_range['min_date'];
    $this_month['ed'] = $date_range['max_date'];
    $this_month['calendar_id'] = date("M", strtotime($date_range['max_date']));

    $this_month['start_date'] = date("m/d/Y", strtotime($date_range['min_date']));
    $this_month['end_date'] = date("m/d/Y", strtotime($date_range['max_date']));
    return $this_month;
}

//last current month
function getCurrentMediaMonth()
{

    $date_range = getPreviousDates('month', 1, '', '');
    $this_month['sd'] = $date_range['min_date'];
    $this_month['ed'] = $date_range['max_date'];
    $this_month['calendar_id'] = date("M", strtotime($date_range['max_date']));
    $this_month['media_month_id'] = date("m", strtotime($date_range['max_date']));

    $this_month['start_date'] = date("m/d/Y", strtotime($date_range['min_date']));
    $this_month['end_date'] = date("m/d/Y", strtotime($date_range['max_date']));
    return $this_month;
}

function getLastMediaWeek()
{
    $date_range = getPreviousDates('week', 1, '', 'current');
    $this_week['sd'] = $date_range['min_date'];
    $this_week['ed'] = $date_range['max_date'];
    $this_week['calendar_id'] = $date_range['calendar_id'];

    $this_week['start_date'] = date("m/d/Y", strtotime($date_range['min_date']));
    $this_week['end_date'] = date("m/d/Y", strtotime($date_range['max_date']));

    return $this_week;
}

function getCurrentMediaWeek()
{
    $date_range = getPreviousDates('week', 1, '', '');
    $this_week['sd'] = $date_range['min_date'];
    $this_week['ed'] = $date_range['max_date'];

    return $this_week;
}

function getLastQuarter()
{
    $date_range = getPreviousDates('qtr', 1, '', 'current');
    $this_week['sd'] = $date_range['min_date'];
    $this_week['ed'] = $date_range['max_date'];

    return $this_week;
}

function getCurrentMediaQuarter()
{
    $date_range = getPreviousDates('qtr', 1, '', '');
    $this_week['sd'] = $date_range['min_date'];
    $this_week['ed'] = $date_range['max_date'];

    return $this_week;
}

function getCurrentMediaYear()
{
    $date_range = getPreviousDates('year', 1, '');
    $this_week['sd'] = $date_range['min_date'];
    $this_week['ed'] = customDate();

    return $this_week;
}

function passphreasForUser($user_id)
{
    $db = getConnection();
    $sql = "SELECT passphrase FROM user WHERE user_id = '" . $user_id . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $get_result;
}

function getNetworkCodeById($network_id)
{
    $db = getConnection();
    $sql = "SELECT network_code, network_alias FROM network WHERE network_id = '" . $network_id . "'";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $get_result;
}

function getCreativeInfoByAiring($airing_id)
{
    $db = getConnection();
    $sql = "SELECT c.brand_id, network_alias, brand.brand_name, a.start, a.creative_id, c.creative_name, c.length
            FROM `airings` as a INNER JOIN creative c on a.creative_id = c.creative_id INNER JOIN network on network.network_code = a.network_code INNER JOIN brand on a.brand_id = brand.brand_id
            WHERE a.airing_id = {$airing_id} AND c.class != 'BRAND';";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $get_result;
}

function getAiringInfoById($airing_id, $refine_params)
{
    $db = getConnection();
    $final_array = array();
    $where = '';
    $sql = "SELECT a.airing_id,
    a.start_date,
    a.broadcast_start,
    n.network_alias,
    a.start,
    a.creative_id,
    b.brand_name,
    c.creative_name,
    c.length
FROM   `airings` AS a
    INNER JOIN brand b
            ON b.brand_id = a.brand_id
    INNER JOIN creative c
            ON c.creative_id = a.creative_id
    INNER JOIN network n
            ON n.network_id = a.network_id
WHERE  airing_id = '" . $airing_id . "'
    AND a.start_date BETWEEN Date_sub(Curdate(), INTERVAL 10 day) AND Curdate() AND c.class != 'BRAND';";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
    $final_array['process'] = 1;

    if (empty($get_result)) {
        $sql = "SELECT a.creative_id, a.network_code , a.network_id  FROM `airings` as a INNER JOIN network n on n.network_id = a.network_id WHERE airing_id = '" . $airing_id . "'  $where";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $get_result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $network_filter = " AND a.`network_id` IN ( ". $get_result[0]->network_id." ) ";
        $params['creative_id'] = $get_result[0]->creative_id;
        $params['network_filter'] = $network_filter;
        $params['where']          = $where;
        if(!empty($refine_params)) {
            $params['refine_by'] = $refine_params['refine_by'][1];
            $params['search_by_tfn'] = $refine_params['search_by_tfn'][1];
            $params['refine_where'] = $refine_params['where'];
        }
        $get_result = get_query_result('__query_get_video_id', $params, 'FETCH_OBJ');
        $final_array['process'] = 2;

        if (empty($get_result)) {
            $params['network_filter'] = '';
            $get_result = get_query_result('__query_get_video_id', $params, 'FETCH_OBJ');
            $final_array['process'] = 3;
        }
    }
    if (!empty($get_result)) {
        $final_array['result'] = $get_result;
    } else {
        $final_array['process'] = 4;
        $final_array['result'] = getAiringDetailByCreativeId($params);
    }

    return $final_array;
}

//for refine pop up, logic to get NA text.
function noVideoIfNoAirings($params) {
    extract($params);
    $refine_params['where'] = $where;
    $airing_info = getAiringInfoById($airing_id, $refine_params);
    return $airing_info;
}

function getNetworkCodeByAlias($network_alias) #(network_code)
{
    $db = getConnection();
    $sql = "SELECT network_code FROM network WHERE network_alias = '" . addslashes($network_alias) . "' ";
   #$sql = "SELECT network_code FROM network WHERE network_id =". $network_id ;

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $result[0]->network_code;
}

function getNetworkAliasByCode($network_code, $tab = null)
{
    $db = getConnection();

    /*if( $network_code == "'All'")
    $network_code = 'All';

    $param = "'".$network_code."'";
    if($tab == 'network'){
    $param = $network_code;
    }*/

    $sql = "SELECT network_alias FROM network WHERE network_code IN (" . $network_code . ") ";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (isset($result[0]->network_alias)) {
        if ($tab == 'network') {
            $network_alias = array();
            foreach ($result as $key => $value) {
                $network_alias[] = $value->network_alias;
            }
            return $network_alias;
        } else {
            return $result[0]->network_alias;
        }
    } else {
        return false;
    }
}

function getAiringDetailByCreativeId($parameters)
{
    extract($parameters);
    $params['creative_id']  = $parameters['creative_id'];
    $params['where']        = $parameters['where'];
    $get_result             = get_query_result('__query_get_video_id_by_creative', $parameters, 'FETCH_OBJ');
    return $get_result;
}

function get_filter_text($query_string)
{

    $output = get_filter_criteria_array($query_string);
    if (empty($output['categories']['display_criteria'])) {
        $output['categories']['display_criteria'] = 'All';
    }

    $sequence_for_display = array(
        'date',
        'classification',
        'categories',
        'language',
        'breaktype',
        'response_type',
        'network',
        'creative_duration',
        'new_filter',
        'refine_filter_opt',
        'refine_filter_opt_text',
        'sd',
        'ed',
        'search_text',
        'active_inactive_flag',
        'primary_tab',
        'list_ids_name',
    );

    $sequence_to_save = array(
        'date',
        'classification',
        'categories',
        'language',
        'breaktype',
        'response_type',
        'network',
        'creative_duration',
        'new_filter',
        'refine_filter_opt',
        'refine_filter_opt_text',
        'search_text',
        'active_inactive_flag',
        'primary_tab',
        'list_ids_name',
    );
    $query_string = process_dates_for_filter($query_string);
    parse_str($query_string, $date_details);
    if ($date_details['sd'] == 'calender') {
        $date_range_string = '';
    } else {
        $sd = formatDate($date_details['sd']);
        $ed = formatDate($date_details['ed']);
        $date_range_string = " - $sd thru $ed";
    }
    $creative_durations = '({{creative_duration}})';
    $output_list_condition = '';
    if(isset($output['list_ids_name'])) {
        $primary_tab_ucfirst = ucfirst($output['primary_tab']['display_criteria']);
        $output_list_condition = '| Selected '.$primary_tab_ucfirst.'s: {{list_ids_name}}';
   }
    if($output['page_call']['display_criteria'] == 'ranking') {
        $display_text = "Date Range - {{date}} $date_range_string | Search - {{search_text}} | Brand Classification - {{classification}} $creative_durations | Creative - {{language}} | Response Type - {{response_type}} | Categories : {{categories}} | Active/Inactive : {{active_inactive_flag}} ".$output_list_condition;
    } else {
        $display_text = "Date Range - {{date}} $date_range_string | Search - {{search_text}} | Brand Classification - {{classification}} $creative_durations | Creative - {{language}} | Response Type - {{response_type}} | Categories : {{categories}} | Active/Inactive : {{active_inactive_flag}} | Breaktype - {{breaktype}}";
    }

    // $display_text = "Date Range - {{date}} $date_range_string | Search - {{search_text}} | Brand Classification - {{classification}} $creative_durations | Creative - {{language}} | Response Type - {{response_type}} | Categories : {{categories}} | Active/Inactive : {{active_inactive_flag}} | Breaktype - {{breaktype}}";
    $return_display_text = '';
    foreach ($sequence_for_display as $key => $value) {
        if (isset($output[$value])) {
            $text_to_be_replaced = '{{' . $value . '}}';
            $replace_with = trim_text($output[$value]['display_criteria']);
            if($value ==  'classification' && $output[$value]['post_values'] > 5 ) {
                $creative_durations = '';
                $display_text = str_replace('({{creative_duration}})', $creative_durations, $display_text);
            }

            $display_text = str_replace($text_to_be_replaced, $replace_with, $display_text);
        }
    }

    $return_save_text = '';
    foreach ($sequence_to_save as $key => $value) {
        if (isset($output[$value])) {
            $return_save_text .= ' | ' . $output[$value]['post_values'];
        }
    }

    $array['display_text'] = $display_text;
    $array['save_text'] = ltrim($return_save_text, ' | ');

    return $array;
}

function get_filter_criteria_array($query_string)
{
    // show($query_string, 1);
    $parsed_data = parse_query_string($query_string); //parsed array with with search key and search value
    if(isset($parsed_data['applied_ids']) && $parsed_data['applied_ids']!= '' ) {
        if($parsed_data['primary_tab'] == 'advertiser') {
            $sql = 'SELECT GROUP_CONCAT(display_name) as name FROM advertiser  WHERE adv_id IN ('.$parsed_data['applied_ids'].')';
        } else {
            $sql = 'SELECT GROUP_CONCAT(brand_name) as name FROM brand as name WHERE brand_id IN ('.$parsed_data['applied_ids'].')';
        }
        $result = execute_query_get_result($sql, 'FETCH_OBJ');
        $parsed_data['list_ids_name'] =  $result[0]->name;
    }
    $fields = get_criteria_keys($parsed_data);
    foreach ($fields as $key => $value) {
        if (!isset($value['display_criteria'])) {
            $converted_data = get_converted_data($key, $value);
            $fields[$key]['display_criteria'] = search_from_array($converted_data, $value['post_values']);
        }
    }

    return $fields;
}

function get_converted_data($key, $value)
{
    $function_name = "get_$key";
    $data_arr = $function_name();
    $converted_data = convert_array($data_arr, $value['key'], $value['value']);

    return $converted_data;
}

function __query_get_categories()
{
    $sql = 'SELECT * from categories';
    return $sql;
}

function __query_get_network()
{
    $sql = 'SELECT * from network';
    return $sql;
}

function __query_get_brands()
{
    $sql = 'SELECT * from brand';
    return $sql;
}

function get_categories()
{

    if (!defined('CATEGORIES_FOR_FILTER')) {
        $categories = get_query_result('__query_get_categories', '', 'FETCH_ASSOC');
        define('CATEGORIES_FOR_FILTER', serialize($categories));
    }

    return unserialize(CATEGORIES_FOR_FILTER);
}

function get_network()
{

    if (!defined('NETWORKS_FOR_FILTER')) {
        $network = get_query_result('__query_get_network', '', 'FETCH_ASSOC');
        define('NETWORKS_FOR_FILTER', serialize($network));
    }

    return unserialize(NETWORKS_FOR_FILTER);
}

function get_brands()
{
    $brands = get_query_result('__query_get_brands', '', 'FETCH_ASSOC');
    return $brands;
}

function get_language()
{
    $language = array(
        array('key' => 'All', 'value' => 'All'),
        array('key' => 'English', 'value' => 'English'),
        array('key' => 'Spanish', 'value' => 'Spanish'),
    );
    return $language;
}

function convert_array($array, $arr_key, $arr_value)
{
    $converted_array = array();

    foreach ($array as $main_array => $value) {
        $converted_array[$value[$arr_key]] = $value[$arr_value];
    }

    return $converted_array;
}

function parse_query_string($string)
{
    $parse_arr = parse_str($string, $output_arr);
    return $output_arr;
}

function search_from_array($super_set, $search_string, $search_id = null)
{
    $resp_arr['ids'] = array();
    $resp_arr['values'] = array();
    $return_string = '';

    $search_array = explode(',', $search_string);

    foreach ($search_array as $key => $value) {
        if (!empty($super_set[$value])) {
            $return_string .= $super_set[$value] . ', ';
        }
    }

    return rtrim($return_string, ', ');
}

function get_criteria_keys($super_set)
{
    $criteria_array = [];

    foreach ($super_set as $key => $value) {
        switch ($key) {
            case 'page_call':
                $criteria_array['page_call']['post_values'] = $value;
                $criteria_array['page_call']['display_criteria'] = $value;
                $criteria_array['page_call']['key'] = 'NA';
                $criteria_array['page_call']['value'] = 'NA';
                break;
            case 'cat':
                $criteria_array['categories']['post_values'] = $value;
                $criteria_array['categories']['key'] = 'sub_category_id';
                $criteria_array['categories']['value'] = 'sub_category';
                break;
            case 'network_alias':
                $criteria_array['network']['post_values'] = $value;
                $criteria_array['network']['key'] = 'network_alias';
                $criteria_array['network']['value'] = 'network_alias';
                break;
            case 'network_code':
                $criteria_array['network']['post_values'] = $value;
                $criteria_array['network']['key'] = 'network_alias';
                $criteria_array['network']['value'] = 'network_alias';
                break;
            case 'c':
                $classification = array(
                    1 => 'All Short Form',
                    2 => 'Short Form Products',
                    3 => 'Lead Generation',
                    4 => 'Brand/DR',
                    5 => 'AsOnTV Retail Rankings',
                    6 => '28.5m Creative',
                    7 => 'AsOnTV Retail Rankings (28.5m)',
                );
                $class_display = '';
                if ($value > 5) {
                    $class_display = $classification[$value];
                } else {
                    $c_arr = explode(',', $value);
                    foreach ($c_arr as $k => $val) {
                        $class_display .= $classification[$val] . ',';
                    }
                    $class_display = rtrim($class_display, ",");
                }
                $criteria_array['classification']['post_values'] = $value;
                $criteria_array['classification']['key'] = 'key';
                $criteria_array['classification']['value'] = 'value';
                $criteria_array['classification']['display_criteria'] = $class_display;
                break;
            case 'creative_duration':
                $criteria_array['creative_duration']['post_values'] = str_replace('%2C', ",", $value);
                $criteria_array['creative_duration']['display_criteria'] = str_replace('%2C', ",", $value);
                if($value == 'all_short_duration') {
                    $criteria_array['creative_duration']['display_criteria'] = 'All Short Duration';
                }
                $criteria_array['creative_duration']['key'] = 'NA';
                $criteria_array['creative_duration']['value'] = 'NA';
                break;
            case 'refine_filter_opt':
                $criteria_array['refine_filter_opt']['post_values'] = $value;
                $criteria_array['refine_filter_opt']['display_criteria'] = ucfirst($value);
                $criteria_array['refine_filter_opt']['key'] = 'NA';
                $criteria_array['refine_filter_opt']['value'] = 'NA';
                break;
            case 'refine_filter_opt_text':
                $criteria_array['refine_filter_opt_text']['post_values'] = $value;
                $criteria_array['refine_filter_opt_text']['display_criteria'] = ucfirst($value);
                $criteria_array['refine_filter_opt_text']['key'] = 'NA';
                $criteria_array['refine_filter_opt_text']['value'] = 'NA';
                break;
            case 'new_filter_opt':
                $criteria_array['new_filter']['post_values'] = $value;
                $criteria_array['new_filter']['display_criteria'] = ucfirst($value);
                $criteria_array['new_filter']['key'] = 'NA';
                $criteria_array['new_filter']['value'] = 'NA';
                break;
            case 'startDate':
                $criteria_array['date']['post_values'] = processDate($value, array('sd' => $super_set['sd'], 'ed' => $super_set['ed']));
                $criteria_array['date']['display_criteria'] = processDate($value, array('sd' => $super_set['sd'], 'ed' => $super_set['ed']));
                $criteria_array['date']['key'] = 'NA';
                $criteria_array['date']['value'] = 'NA';
                break;
            case 'responseType':
                $criteria_array['response_type']['post_values'] = trim($value);
                $criteria_array['response_type']['display_criteria'] = strtoupper(str_ireplace('response_', ' ', trim($value)));
                $criteria_array['response_type']['key'] = 'NA';
                $criteria_array['response_type']['value'] = 'NA';
                break;
            case 'sd':
                $criteria_array['sd']['post_values'] = $value;
                $criteria_array['sd']['display_criteria'] = formatDate($value);
                $criteria_array['sd']['key'] = 'NA';
                $criteria_array['sd']['value'] = 'NA';
                break;
            case 'ed':
                $criteria_array['ed']['post_values'] = $value;
                $criteria_array['ed']['display_criteria'] = formatDate($value);
                $criteria_array['ed']['key'] = 'NA';
                $criteria_array['ed']['value'] = 'NA';
                break;
            case 'search_text':
                $criteria_array['search_text']['post_values'] = $value;
                $criteria_array['search_text']['display_criteria'] = $value;
                $criteria_array['search_text']['key'] = 'NA';
                $criteria_array['search_text']['value'] = 'NA';
                break;
            case 'spanish':
                if ($value == '0,1') {
                    $value = 'All';
                } else if ($value == 0){
                    $value = 'English';
                } else {
                    $value = 'Spanish';
                }

                $criteria_array['language']['post_values'] = $value;
                $criteria_array['language']['key'] = 'key';
                $criteria_array['language']['value'] = 'value';
                break;
            case 'active_inactive_flag':
                if ($value == '') {
                    $value = 'All';
                } else if ($value == 0) {
                    $value = 'Inactive';
                } else if ($value == 1) {
                    $value = 'Active';
                } else {
                    $value = 'All';
                }
                $criteria_array['active_inactive_flag']['post_values'] = $value;
                $criteria_array['active_inactive_flag']['display_criteria'] = $value;
                $criteria_array['active_inactive_flag']['key'] = 'NA';
                $criteria_array['active_inactive_flag']['value'] = 'NA';
                break;

            case 'breaktype':
                if ($value == '' || $value == 'A') {
                    $value = 'All';
                } else if ($value == 'L') {
                    $value = 'Local';
                } else if ($value == 'N') {
                    $value = 'National';
                } else {
                    $value = 'All';
                }
                $criteria_array['breaktype']['post_values'] = $value;
                $criteria_array['breaktype']['display_criteria'] = $value;
                $criteria_array['breaktype']['key'] = 'NA';
                $criteria_array['breaktype']['value'] = 'NA';
                break;
            case 'list_ids_name':
                $criteria_array['list_ids_name']['post_values'] = $value;
                $criteria_array['list_ids_name']['display_criteria'] = $value;
                $criteria_array['list_ids_name']['key'] = 'NA';
                $criteria_array['list_ids_name']['value'] = 'NA';
                break;
            case 'primary_tab':
                $criteria_array['primary_tab']['post_values'] = $value;
                $criteria_array['primary_tab']['display_criteria'] = $value;
                $criteria_array['primary_tab']['key'] = 'NA';
                $criteria_array['primary_tab']['value'] = 'NA';
                break;
            default:
                # code...
                break;
        }
    }

    return $criteria_array;
}

//@$date in YYYY-MM-DD format
function formatDate($date, $format = 'm/d/yyyy')
{
    if ($format == 'm/d/yyyy') {
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);
        $date = substr($date, 8, 2);

        return "$month/$date/$year";
    }
}

function processDate($date_string, $dates)
{
    $patterns = array(
        '9' => 'Current week',
        '2' => 'Current week',
        '7' => 'Last Month',
        '8' => 'Last quarter',
        '10' => 'Current Month',
        '11' => 'Current quarter',
        '1' => 'Last week',
        '6' => 'Last week',
        '5' => 'Lifetime',
        'calender' => 'Custom Date',
    );

    if ($date_string == 'calender') { //if calendar date
        $return_string = 'Custom Date -' . formatDate($dates['sd']) . ' - ' . formatDate($dates['ed']);
    } else if (array_key_exists($date_string, $patterns)) { // if relative date
        $return_string = $patterns[$date_string];
    } else { // if absolute date
        if (strpos($date_string, 'year') !== false) {
            $year = substr($date_string, -10, 4);
            if ($year == customDate('Y')) {
                $db_date_string = 'Current Year - YTD';
            } else {
                $db_date_string = "$year - All";
            }
            $return_string = $db_date_string;
        } else {
            $year = substr($date_string, -10, 4);
            $date_details_array = explode('_', $date_string);
            $range_type = ucfirst(substr($date_details_array[0], 0, -2));
            $number = $date_details_array[1];
            $return_string = "$year - $range_type $number";
        }
    }

    return $return_string;
}

function process_dates_for_filter($query_string, $scheduled_email=0)
{
    parse_str($query_string, $date_details);
    $date_string = intval($date_details['startDate']);
    $patterns = array(
        1 => 'Last week',
        2 => 'Current week',
        5 => 'Lifetime',
        7 => 'Last month',
        8 => 'Last quarter',
        9 => 'Current week',
        10 => 'Current month',
        11 => 'Current quarter',
        'calender' => 'Custom Date',
    );
    if (array_key_exists($date_string, $patterns)) { // if relative date
        switch ($date_string) {
            case 1:
                $return = getLastMediaWeek();
                $query_string .= '&sd=' . $return['sd'] . '&ed=' . $return['ed'];
                break;
            case 2:
                $return = getCurrentMediaWeek();
                $query_string .= '&sd=' . $return['sd'] . '&ed=' . ($scheduled_email ? customDate('Y-m-d') : $return['ed']);
                break;
            case 5:
                $query_string .= '&sd=' . LIFETIME_START_DATE . '&ed=' . customDate('Y-m-d');
                break;
            case 7:
                $return = getLastMediaMonth();
                $query_string .= '&sd=' . $return['sd'] . '&ed=' . $return['ed'];
                break;
            case 8:
                $return = getLastQuarter();
                $query_string .= '&sd=' . $return['sd'] . '&ed=' . $return['ed'];
                break;
            case 9:
                $return = getCurrentMediaWeek();
                $query_string .= '&sd=' . $return['sd'] . '&ed=' . ($scheduled_email ? customDate('Y-m-d') : $return['ed']);
                break;
            case 10:
                $return = getCurrentMediaMonth();
                $query_string .= '&sd=' . $return['sd'] . '&ed=' . ($scheduled_email ? customDate('Y-m-d') : $return['ed']);
                break;
            case 11:
                $return = getCurrentMediaQuarter();
                $query_string .= '&sd=' . $return['sd'] . '&ed=' . ($scheduled_email ? customDate('Y-m-d') : $return['ed']);
                break;
            case 'calender':
                $query_string .= '&sd=' . $date_details['sd'] . '&ed=' . $date_details['ed'];
                break;

            default:
                # code...
                break;
        }
    } else if ($date_string == 0 && strpos($date_details['startDate'], 'year') !== false) {
        $year = substr($date_details['startDate'], -10, 4);
        if ($year == customDate('Y')) {
            $return = getCurrentMediaYear();
            $query_string .= '&sd=' . $return['sd'] . '&ed=' . $return['ed'];
            if( strpos($date_details['startDate'], '_') !== false ) {
               $dateArray = explode('_', $date_details['startDate']);
               $query_string .= '&startDate='.$dateArray[0].'_'.$dateArray[1].'_'.$return['sd'].'_'.$return['ed'];
            }
        }
    }

    return $query_string;
}

/**
 * trims text to a space then adds ellipses if desired
 * @param string $input text to trim
 * @param int $length in characters to trim to
 * @param bool $ellipses if ellipses (...) are to be added
 * @param bool $strip_html if html tags are to be stripped
 * @return string
 */
function trim_text($input, $length = 100, $ellipses = true, $strip_html = true)
{
    //strip tags, if desired
    if ($strip_html) {
        $input = strip_tags($input);
    }

    //no need to trim, already shorter than trim length
    if (strlen($input) <= $length) {
        return $input;
    }

    //find last space within length
    $last_space = strrpos(substr($input, 0, $length), ' ');
    $trimmed_text = substr($input, 0, $last_space);

    //add ellipses (...)
    if ($ellipses) {
        $trimmed_text .= '...';
    }

    return $trimmed_text;
}

function check_weekly_mail_date()
{
    $day = 'Monday';
    $monday_date = date('Y-m-d', strtotime($day . ' this week'));
  

    if ($monday_date == date('Y-m-d')) { // if today is Monday
        return $monday_date;
    } else {
        //echo 'wrong time to execute';
        return 0;
    }
}

function getActulAiringInfoById($airing_id)
{
    $final_array = array();
    $params['airing_id'] = $airing_id;
    $final_array['result'] = get_query_result('__query_get_info_org_airing', $params, 'FETCH_OBJ');
    return $final_array;
}

function query_string_to_json($result)
{
    $query_string = process_dates_for_filter($result['query_string'], $result['criteria']);
    $set_one = explode('&', $query_string);
    $requestData = $raw_data = array();
    $requestData['criteria'] = $result['criteria'];
    foreach ($set_one as $k => $v) {
        $raw_data = explode('=', $v);
        if ($raw_data[0] == 'c') {
            $raw_data[1] = str_replace('+', " ", $raw_data[1]);
        } else if ($raw_data[0] == 'cat') {
            $raw_data[1] = str_replace('%2C', ",", $raw_data[1]);
        }

        if ($raw_data[0] == 'unchecked_category') {
            $raw_data[1] = str_replace('%2C', ",", $raw_data[1]);
        }

        if ($raw_data[0] == 'responseType') {
            $requestData[$raw_data[0]] = urldecode($raw_data[1]);
        } else if ($raw_data[0] == 'search_text') {
            $requestData[$raw_data[0]] = str_replace('XxX', '&', $raw_data[1]);
        } else {
            if ($raw_data[0] == 'network_alias') {
                $requestData[$raw_data[0]] = urldecode(stripslashes($raw_data[1]));
            } else {
                $requestData[$raw_data[0]] = isset($raw_data[1]) ? stripslashes($raw_data[1]) : stripslashes($raw_data[0]);
            }
        }
    }
    $pageData['page'] = $result['page'];
    $pageData['primary_tab'] = $result['primary_tab'];
    $pageData['secondary_tab'] = $result['secondary_tab'];

    return json_encode(array('status' => 1, 'result' => $requestData, 'pageData' => $pageData));
}

function getCatNumByBrandId($brand_id, $cat_str)
{
    $cat_str = urldecode($cat_str);
    global $BRAND_CATEGORY_IDS;
    /*
    if (!defined('BRAND_CATEGORY_IDS')) {
    $result  = getAllCategoryIdByBrand();
    define('BRAND_CATEGORY_IDS', serialize($result));
    } else {
    $result  = unserialize(BRAND_CATEGORY_IDS);
    }
     */
    if (empty($BRAND_CATEGORY_IDS)) {
        $result = getAllCategoryIdByBrand();
        $BRAND_CATEGORY_IDS = $result;
    } else {
        $result = $BRAND_CATEGORY_IDS;
    }

    $cat_ids = $result[$brand_id];
    $ids = explode(',', $cat_ids);

    if (empty($cat_str) || $cat_str == 'all') {
        $number = count($ids);
    } else {
        $number = 0;
        foreach ($ids as $value) {
            if (strpos($cat_str, $value) !== false) {
                $number += 1;
            }
        }
    }

    return $number;
}

function getAllCategoryIdByBrand()
{
    $result = get_query_result('__get_brand_id_with_category');
    $brand_arr = array();
    foreach ($result as $category_id) {
        $brand_arr[$category_id['brand_id']] = rtrim($category_id['cat_ids'], ',');
    }
    return $brand_arr;
}

function getAdvertiserName($search_adv_id)
{
    $advertisers = get_query_result('__query_get_advertisers');

    foreach ($advertisers as $advertiser) {
        $adv_id = $advertiser['adv_id'];
        $constant_advertiser_name = 'ADVERTISER_ID_' . $adv_id;
        if (!defined($constant_advertiser_name)) {
            define($constant_advertiser_name, $advertiser['display_name']);
        }
    }

    return constant("ADVERTISER_ID_{$search_adv_id}");
}

function checkDomain($domain_flag, $admin_email, $user_email)
{

    $user_domain = explode('@', $user_email);
    $admin_domain = explode('@', $admin_email);
    $flag = true;
    if (!$domain_flag && ($admin_domain[1] != $user_domain[1])) {
        $flag = false;
    }
    if (!$flag) {
        echo json_encode(array('status' => 4, 'domain_msg' => 'You are only licensed to add users that have an email addressed associated with your assigned company domain <b>' . $admin_domain[1] . '</b>. If you require assistance, please contact DRMetrix'));
        exit;
    } else {
        return true;
    }

}

function getNetworkIdByCode($network_code)
{
    $params['network_code'] = htmlspecialchars_decode($network_code);

    $get_result = get_query_result('__query_get_networkid_by_code', $params, 'FETCH_OBJ');
    return $get_result[0]->network_id;
}

function getNetworkIdByAlias($network_alias)
{
    $params['network_alias'] = htmlspecialchars_decode($network_alias);

    $get_result = get_query_result('__query_get_networkid_by_alias', $params, 'FETCH_OBJ');
    return $get_result[0]->network_id;
}

function getNetworkIdByName($network_name)
{
    $params['network_name'] = htmlspecialchars_decode($network_name);

    $get_result = get_query_result('__query_get_networkid_by_name', $params, 'FETCH_OBJ');
    return $get_result[0]->network_id;
}

function getTrackingDataForUser($user_id, $frequency)
{
    $params['user_id'] = $user_id;
    $params['frequency'] = $frequency;
    $response['query'] = '';
    $response['id'] = array();
    $result = get_query_result('__query_get_tracking_data_for_user', $params, 'FETCH_OBJ');
    $tracking_alert_subscribed = 0;

    if (count($result) > 0) {

        foreach ($result as $key => $value) {
            $tracking_alert_subscribed = $value->tracking_alert_subscribed;

            if ($value->alert_type == 'advertiser') {
                $cond[] = " advertiser.adv_id IN (" . $value->type_ids . ") ";
                $ids['advertiser'] = explode(',', $value->type_ids);
            } else if ($value->alert_type == 'brand') {
                $cond[] = " brand.brand_id IN (" . $value->type_ids . ") ";
                $ids['brand'] = explode(',', $value->type_ids);
            } else if ($value->alert_type == 'network') {
                $networks = get_network_codes($value->type_ids);
                $cond[] = " airings.network_code IN (" . $networks . ") ";
                $ids['network'] = explode(',', $value->type_ids);
            } else if ($value->alert_type == 'category') {
                $cond[] = " ( brand.alt_sub_category_id IN (" . $value->type_ids . ") OR brand.main_sub_category_id IN (" . $value->type_ids . ") )";
                $ids['category'] = explode(',', $value->type_ids);
            }
        }

        if (count($cond) > 1) {
            $query = " AND (" . implode(" OR ", $cond) . ")";
        } elseif (count($cond) == 1) {
            $query = " AND " . $cond[0];
        } else {
            $query = "";
        }

        $response['query'] = $query;
        $response['id'] = $ids;
    }

    if ($tracking_alert_subscribed == 0) {
        $response['query'] = '';
        $response['id'] = array();
    }

    return $response;
}

function checkStatus($type, $id, $user_tracking_details, $applicable_alert_types = array())
{
    $ids = $user_tracking_details['id'];
    $present = false;

    if (!empty($applicable_alert_types)) {
        if ($type == 'advertiser' && in_array($type, $applicable_alert_types)) {
            if (array_key_exists("advertiser", $ids)) {
                $ids_list = $ids['advertiser'];

                if (in_array($id, $ids_list)) {
                    $present = true;
                }
            }
        } elseif ($type == 'brand' && in_array($type, $applicable_alert_types)) {
            if (array_key_exists("brand", $ids)) {
                $ids_list = $ids['brand'];
                if (in_array($id, $ids_list)) {
                    $present = true;
                }
            }
        } elseif ($type == 'network' && in_array($type, $applicable_alert_types)) {
            if (array_key_exists("network", $ids)) {
                $ids_list = $ids['network'];
                $lst = explode(',', $id);
                foreach ($lst as $value) {
                    if (in_array($value, $ids_list)) {
                        $present = true;
                    }
                }
            }
        } elseif ($type == 'category' && in_array($type, $applicable_alert_types)) {
            if (array_key_exists("category", $ids)) {
                $ids_list = $ids['category'];
                if (in_array($id, $ids_list)) {
                    $present = true;
                }
            }
        }
    } else {
        if ($type == 'advertiser') {
            if (array_key_exists("advertiser", $ids)) {
                $ids_list = $ids['advertiser'];

                if (in_array($id, $ids_list)) {
                    $present = true;
                }
            }
        } elseif ($type == 'brand') {
            if (array_key_exists("brand", $ids)) {
                $ids_list = $ids['brand'];
                if (in_array($id, $ids_list)) {
                    $present = true;
                }
            }
        } elseif ($type == 'network') {
            if (array_key_exists("network", $ids)) {
                $ids_list = $ids['network'];
                $lst = explode(',', $id);
                foreach ($lst as $value) {
                    if (in_array($value, $ids_list)) {
                        $present = true;
                    }
                }
            }
        } elseif ($type == 'category') {
            if (array_key_exists("category", $ids)) {
                $ids_list = $ids['category'];
                if (in_array($id, $ids_list)) {
                    $present = true;
                }
            }
        }
    }

    if ($present) {
        $images_url = "http://" . HOST . "/drmetrix/api/email-template/images/";
        return "<img src='" . $images_url . "email-check-icon.png'/>";
    } else {
        return "";
    }
}

function getTrackingEditLink($array, $user_tracking_details)
{

    $return_text = '';
    foreach ($array as $type => $id) {

        $ids = $user_tracking_details['id'];

        if ($type == 'advertiser') {
            if (array_key_exists("advertiser", $ids)) {
                $ids_list = $ids['advertiser'];

                if (in_array($id, $ids_list)) {
                    $return_text .= '&advertiser=' . $id;
                }
            }
        } elseif ($type == 'brand') {
            if (array_key_exists("brand", $ids)) {
                $ids_list = $ids['brand'];
                if (in_array($id, $ids_list)) {
                    $return_text .= '&brand=' . $id;
                }
            }
        } elseif ($type == 'network') {
            if (array_key_exists("network", $ids)) {
                $ids_list = $ids['network'];
                $net_list = '';
                $id_lst = explode(',', $id);

                foreach ($ids_list as $network_id) {
                    if (in_array(get_network_details($network_id, 'network_code'), $id_lst)) {
                        $net_list .= $network_id . ',';
                    }
                }
                $tmp_list = trim($net_list);
                if (!empty($tmp_list)) {
                    $return_text .= '&network=' . rtrim($net_list, ',');
                }
            }
        } elseif ($type == 'category') {
            if (array_key_exists("category", $ids)) {
                $ids_list = $ids['category'];
                if (in_array($id, $ids_list)) {
                    $return_text .= '&category=' . $id;
                }
            }
        }
    }

    return ltrim($return_text, '&');
}

function get_users_list()
{
    $get_result = get_query_result('__query_get_users_list', '', 'FETCH_OBJ');

    return $get_result;
}

function get_network_codes($network_id)
{
    $param['network_id'] = $network_id;
    $get_result = get_query_result('__query_get_network_codes', $param, 'FETCH_ASSOC');

    return $get_result[0]['network_codes'];
}

function get_all_networks()
{
    if (!defined('__NETWORKS')) {
        $get_result = get_query_result('__query_get_all_networks', '', 'FETCH_ASSOC');

        foreach ($get_result as $key => $network) {
            $network_id = $network['network_id'];
            $network_code = $network['network_code'];
            $network_name = $network['network_name'];
            $network_alias = $network['network_alias'];

            $networks[$network_id]['network_id'] = $network_id;
            $networks[$network_id]['network_code'] = $network_code;
            $networks[$network_id]['network_name'] = $network_name;
            $networks[$network_id]['network_alias'] = $network_alias;
        }

        define('__NETWORKS', serialize($networks));

    }

    return unserialize(__NETWORKS);
}

function get_network_details($network_id, $detail = 'network_alias')
{
    $networks = get_all_networks();

    return $networks[$network_id][$detail];
}

function get_all_networks_by_code()
{
    if (!defined('__NETWORKS_WITH_CODE')) {
        $get_result = get_query_result('__query_get_all_networks', '', 'FETCH_ASSOC');

        foreach ($get_result as $key => $network) {
            $network_id = $network['network_id'];
            $network_code = $network['network_code'];
            $network_name = $network['network_name'];
            $network_alias = $network['network_alias'];

            $networks[$network_code]['network_id'] = $network_id;
            $networks[$network_code]['network_code'] = $network_code;
            $networks[$network_code]['network_name'] = $network_name;
            $networks[$network_code]['network_alias'] = $network_alias;
        }

        define('__NETWORKS_WITH_CODE', serialize($networks));

    }

    return unserialize(__NETWORKS_WITH_CODE);
}

function get_network_details_with_codes($network_code, $detail = 'network_alias')
{
    $networks = get_all_networks_by_code();

    return $networks[$network_code][$detail];
}

function get_all_networks_array_from_code()
{
    if (!defined('__NETWORKS_FROM_CODE')) {
        $get_result = get_query_result('__query_get_all_networks', '', 'FETCH_ASSOC');

        foreach ($get_result as $key => $network) {
            $network_id = $network['network_id'];
            $network_code = $network['network_code'];
            $network_name = $network['network_name'];

            $networks[$network_code]['network_id'] = $network_id;
            $networks[$network_code]['network_code'] = $network_code;
            $networks[$network_code]['network_name'] = $network_name;
        }

        define('__NETWORKS_FROM_CODE', serialize($networks));

    }

    return unserialize(__NETWORKS_FROM_CODE);
}

function get_all_networks_from_code($network_code, $detail = 'network_name')
{
    $networks = get_all_networks_array_from_code();
    $code = '';
    $code_list = explode(',', $network_code);

    foreach ($code_list as $value) {
        if (isset($networks[$value]) && isset($networks[$value][$detail])) {
            $code .= $networks[$value][$detail] . ',';
        }

    }
    return rtrim($code, ',');
}

function get_all_display_networks_from_code($applicable_net_codes, $user_tracking_data, $detail = 'network_name')
{
    $ids = $user_tracking_data['id'];
    $applicable_codes = explode(',', $applicable_net_codes);

    $tracked_code = array();
    $common_code = array();
    $common_net_names = array();

    if (array_key_exists("network", $ids)) {
        $code_list = $ids['network'];

        foreach ($code_list as $network_id) {
            $tracked_code[] = get_network_details($network_id, 'network_code');
        }
    }
    if (count($tracked_code) > 0) {
        $common_code = array_intersect($applicable_codes, $tracked_code);
    } else {
        $common_code[] = $applicable_codes[0];
    }
    if (empty($common_code)) {
        $common_code[] = $applicable_codes[0];
    }

    foreach ($common_code as $value) {
        $common_net_names[] = get_all_networks_from_code($value, $detail);
    }

    return implode(',', $common_net_names);
}

function get_network_ids_from_multiple_network_codes($network_codes)
{
    $network_code_sql_string = "'";
    $network_code_sql_string .= str_replace(',', "','", $network_codes) . "'";
    $network_code_sql_string = rtrim($network_code_sql_string, ',');

    $sql = "SELECT GROUP_CONCAT(network_id) as network_ids FROM `network` where network_code IN ($network_code_sql_string)";
    $get_result = execute_query_get_result($sql);

    if ($get_result[0]['network_ids'] == null) {
        $get_result[0]['network_ids'] = -1;
    }
    return $get_result[0]['network_ids'];
}

function check_associative_array_blank($associative_array)
{
    $return = array();
    foreach ($associative_array as $array) {
        foreach ($array as $value) {
            $return[] = $value;
        }
    }

    return count($return);
}

function check_classification_and_return_value($get_result, $data)
{
    $return = array();

    if (count($get_result) > 0) {
        foreach ($get_result as $key => $value) {
            $data['alert_type'] = $value['alert_type'];
            $classification = check_creative_classification_for_tracking($data, $value['classification']);
            if ($classification == 1) {
                $return[] = $value['alert_type'];
            }
        }

        $return = array_unique($return);
    }

    return $return;
}

function check_new_record_for_tracking($advertiser, $brand, $creative, $data, $user_id, $frequency)
{
    $main_sql = "SELECT id, classification, alert_type FROM `tracking_and_alerts` WHERE user_id = '[[user_id]]' and frequency like '%$frequency%' and status = 'active' and track_elements like '%[[match_type]]%' and ((alert_type = 'advertiser' and type_id='[[advertiser_id]]') or (alert_type = 'brand' and type_id='[[brand_id]]') or (alert_type = 'category' and type_id='[[category_id]]') or (alert_type = 'network' and type_id IN ([[network_id]])))";
    $replace['[[user_id]]'] = $user_id;
    $replace['[[advertiser_id]]'] = $data['advertiser'];
    $replace['[[brand_id]]'] = $data['brand'];
    $replace['[[category_id]]'] = $data['category'];
    $replace['[[network_id]]'] = get_network_ids_from_multiple_network_codes($data['network']);
    $return = array();

    if ($advertiser == "NEW") {
        $sql = $main_sql;
        $replace['[[match_type]]'] = 'advertiser';
        $sql = str_replace(array_keys($replace), $replace, $sql);

        $get_result = execute_query_get_result($sql);

        $return = array_merge($return, check_classification_and_return_value($get_result, $data));
    }

    if ($brand == "NEW") {
        $sql = $main_sql;
        $replace['[[match_type]]'] = 'brand';
        $sql = str_replace(array_keys($replace), $replace, $sql);

        $get_result = execute_query_get_result($sql);

        $return = array_merge($return, check_classification_and_return_value($get_result, $data));
    }

    if ($creative == "NEW") {
        $sql = $main_sql;
        $replace['[[match_type]]'] = 'creative';
        $sql = str_replace(array_keys($replace), $replace, $sql);
        $get_result = execute_query_get_result($sql);

        $return = array_merge($return, check_classification_and_return_value($get_result, $data));
    }

    return array_unique($return);
}

function check_creative_classification_for_tracking($array, $classification)
{
    if (!strcasecmp($classification, 'NA')) {
        return 1;
    }

    $classification_array = explode(',', $classification);
    extract($array);
    $return = 0;

    foreach ($classification_array as $classification_type) {
        switch ($classification_type) {
            case 'short_form_products':
                if ($length <= LENGTH && $class == "DR" && !strcasecmp($type, "PRODUCT")) {
                    $return = $return + 1;
                }
                break;
            case 'lead_generation':
                if ($length <= LENGTH && $class == "DR" && !strcasecmp($type, "LEAD GEN")) {
                    $return = $return + 1;
                }
                break;
            case 'brand_direct':
                if ($length <= LENGTH && !strcasecmp($class, "BRAND DR") && (!strcasecmp($type, "LEAD GEN") || !strcasecmp($type, "PRODUCT"))) {
                    $return = $return + 1;
                }
                break;
            case '285_mins':
                if ($length > LENGTH) {
                    $return = $return + 1;
                }
                break;
            default:
                # code...
                break;
        }
    }

    if ($return >= 1) {
        return 1;
    } else {
        return 0;
    }
}

function get_all_categories()
{
    if (!defined('__CATEGORIES')) {
        $get_result = get_query_result('__query_get_all_categories', '', 'FETCH_ASSOC');

        foreach ($get_result as $key => $category) {
            $category_id = $category['sub_category_id'];
            $category_name = $category['category'];
            $sub_category_name = $category['sub_category'];

            $categories[$category_id]['category_id'] = $category_id;
            $categories[$category_id]['category_name'] = $category_name;
            $categories[$category_id]['sub_category_name'] = $sub_category_name;
        }

        define('__CATEGORIES', serialize($categories));
    }

    return unserialize(__CATEGORIES);
}

function get_category_details($category_id, $detail = 'category_name', $appendCategory = false)
{
    $categories = get_all_categories();

    if ($category_id == null) {
        return '';
    }
    return $appendCategory ? $categories[$category_id]['category_name'] . ' <strong>></strong> ' . $categories[$category_id][$detail] : $categories[$category_id][$detail];
}

function get_category_names_by_ids($category_id1, $category_id2)
{
    if ($category_id1 == null) {
        $name = get_category_details($category_id2);
    } else {
        $name = get_category_details($category_id1);
    }

    return $name;
}

function get_all_brands()
{
    if (!defined('__BRANDS')) {
        $get_result = get_query_result('__query_get_all_brands', '', 'FETCH_ASSOC');

        foreach ($get_result as $key => $brand) {
            $brand_id = $brand['brand_id'];
            $brand_name = $brand['brand_name'];

            $brands[$brand_id]['brand_id'] = $brand_id;
            $brands[$brand_id]['brand_name'] = $brand_name;
        }

        define('__BRANDS', serialize($brands));
    }

    return unserialize(__BRANDS);
}

function get_brand_details($brand_id, $detail = 'brand_name')
{
    $brands = get_all_brands();

    return $brands[$brand_id][$detail];
}

function get_all_advertiser()
{
    if (!defined('__ADVERTISERS')) {
        $get_result = get_query_result('__query_get_all_advertisers', '', 'FETCH_ASSOC');

        foreach ($get_result as $key => $advertiser) {
            $advertiser_id = $advertiser['adv_id'];
            $advertiser_name = $advertiser['display_name'];

            $advertisers[$advertiser_id]['advertiser_id'] = $advertiser_id;
            $advertisers[$advertiser_id]['display_name'] = $advertiser_name;
        }

        define('__ADVERTISERS', serialize($advertisers));
    }

    return unserialize(__ADVERTISERS);
}

function get_advertiser_details($advertiser_id, $detail = 'display_name')
{
    $advertisers = get_all_advertiser();

    if (!empty($advertisers[$advertiser_id])) {
        return $advertisers[$advertiser_id][$detail];
    }
}

function get_all_tracking_alert_subscribers()
{
    $get_result = get_query_result('__query_get_all_tracking_alert_subscribers', '', 'FETCH_ASSOC');

    return $get_result;
}

function get_all_scheduled_email_alert_subscribers($frequency)
{
    $get_result = get_query_result('__query_get_all_scheduled_email_alert_subscribers', array('frequency'=>$frequency), 'FETCH_ASSOC');

    return $get_result;
}

function setTrackingDataIntoSession()
{
    $params['user_id'] = $_SESSION['user_id'];
    $result = get_query_result('__query_get_tracking_data_for_user', $params, 'FETCH_OBJ');
    $status = false;
    $track_data = array();

    if (count($result) > 0) {

        foreach ($result as $key => $value) {
            if ($value->alert_type == 'advertiser') {

                $track_data['advertiser'] = explode(',', $value->type_ids);
            } else if ($value->alert_type == 'brand') {

                $track_data['brand'] = explode(',', $value->type_ids);
            } else if ($value->alert_type == 'network') {

                $networks = get_network_codes($value->type_ids);
                $track_data['network'] = explode(',', $value->type_ids);
            } else if ($value->alert_type == 'category') {

                $track_data['category'] = explode(',', $value->type_ids);
            }
        }
        $_SESSION['tracking_data'] = $track_data;
    } else {
        $_SESSION['tracking_data'] = "No Data";
    }
}

function isTrackingPresent($type, $id)
{

    if (!isset($_SESSION['tracking_data'])) {
        $tracking_data = 'No Data';
    } else {
        $tracking_data = $_SESSION['tracking_data'];
    }

    $search = false;

    if ($tracking_data != "No Data") {
        if ($type == 'brand') {
            if (isset($tracking_data['brand'])) {
                $brands = $tracking_data['brand'];
                if (in_array($id, $brands)) {
                    $search = true;
                }
            }
        } else if ($type == 'advertiser') {
            if (isset($tracking_data['advertiser'])) {
                $advertisers = $tracking_data['advertiser'];
                if (in_array($id, $advertisers)) {
                    $search = true;
                }
            }
        } else if ($type == 'network') {
            if (isset($tracking_data['network'])) {
                $network = $tracking_data['network'];
                if (in_array($id, $network)) {
                    $search = true;
                }
            }
        } else if ($type == 'category') {
            if (isset($tracking_data['category'])) {
                $category = $tracking_data['category'];
                if (in_array($id, $category)) {
                    $search = true;
                }
            }
        }
    }
    return $search;
}

function getClassificationData($classification_data)
{
    $class = explode(',', $classification_data);
    $class_string = '';
    foreach ($class as $value) {
        switch ($value) {
            case 'short_form_products':
                $class_string .= 'Short Form Products,';
                break;
            case 'lead_generation':
                $class_string .= 'Lead Generation,';
                break;
            case 'brand_direct':
                $class_string .= 'Brand/Direct,';
                break;
            case '285_mins':
                $class_string .= '28.5 Mins';
                break;
            case 'NA':
                $class_string .= 'NA';
                break;
        }
    }
    return rtrim($class_string, ',');
}

function checkUserEulaData($user_id)
{
    $sql = "SELECT eula_flag FROM user WHERE user_id = '" . $user_id . "'";
    $get_result = execute_query_get_result($sql);
    return $get_result;
}

function get_classification_condition($classification_array)
{
    $condition = array();
    $class_array = array(
        2 => '(c.class = "DR" and c.type = "PRODUCT")',
        3 => '(c.class = "DR" and c.type = "LEAD GEN")',
        4 => '(c.class = "BRAND DR" and c.type in("LEAD GEN", "PRODUCT"))',
        5 => '(c.class IN ("DR", "BRAND DR") and c.type in("LEAD GEN", "PRODUCT") and b.exclude_short != 1 and b.retail_report = 1)',
    );

    foreach ($classification_array as $key => $value) {
        $condition[] = $class_array[$value];
    }

    $condition_str = implode(" OR ", $condition);

    return ' AND c.class != "BRAND" AND (' . $condition_str . ' ) ';
}

function custom_email($to, $subject, $message)
{
    $headers = 'From: info@drmetrix.com' . "\r\n" .
        'MIME-Version: 1.0' . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    require_once 'PHPMailer/class.phpmailer.php';

    try {
        $mail = new PHPMailer(); //New instance, with exceptions enabled
        $mail->IsSendmail(); // tell the class to use Sendmail
        $body = $message;
        $mail->SetFrom('info@drmetrix.com', 'DRMetrix');
        $mail->AddAddress($to);
        $mail->Subject = $subject;
        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->WordWrap = 80; // set word wrap
        $mail->MsgHTML($body);
        $mail->IsHTML(true); // send as HTML
        $mail->Send();
    } catch (phpmailerException $e) {
        echo $e->getMessage();
    }
}

function check_between_dates($date, $start_date, $end_date)
{
    $date = date('Ymd', strtotime($date));
    $start_date = date('Ymd', strtotime($start_date));
    $end_date = date('Ymd', strtotime($end_date));

    if (($date >= $start_date) && ($date <= $end_date)) {
        return true;
    } else {
        return false;
    }
}

/* returns 0 if equal
 * returns 1 if first date is greater
 * returns 2 if second date is greater
 */
function date_compare($first_date, $second_date)
{
    $first_date = date('Ymd', strtotime($first_date));
    $second_date = date('Ymd', strtotime($second_date));

    if ($first_date == $second_date) {
        return 0;
    } else {
        if ($first_date > $second_date) {
            return 1;
        } else {
            return 2;
        }
    }
}

function get_opacity_class($date, $start_date, $end_date)
{
    $in_between_dates = check_between_dates($date, $start_date, $end_date);
    if ($in_between_dates !== true) {
        $disabled_class = 'other-opa';
    } else {
        $disabled_class = '';
    }

    $compare_date = date_compare($end_date, customDate('Y-m-d'));
    if ($compare_date == 2) {
        $disabled_class = '';
    }

    return $disabled_class;
}

function get_zoho_acccount_id_from_adsphere_account_id($adsphere_company_zoho_id)
{
    // $param['url'] = "https://crm.zoho.com/crm/private/json/Accounts/getRecordById";
    // $param['query'] = "authtoken=".ZOHO_APIKEY."&scope=crmapi&id=".$adsphere_company_zoho_id."&selectColumns=Accounts(ACCOUNTID,Account Name)";
    // $account_data = process_json_curl_($param['url'], $param['query']);
    $account_data = getRecordByIdInZoho('Accounts',$adsphere_company_zoho_id);

    $zoho_account_id = '';
    $zoho_account_name = '';

    if (!empty($account_data) && isset($account_data->data)) {
        $account_info       = $account_data->data[0];
        $zoho_account_id    = $account_info->id;
        $zoho_account_name  = $account_info->Account_Name;
    }
    // if(isset($account_data['response']['result']['Accounts']['row']) || !empty($account_data['response']['result']['Accounts']['row'])){
    //     foreach ($account_data['response']['result']['Accounts']['row']['FL']  as $ky => $val) {
    //         if($val['val'] == 'ACCOUNTID'){
    //             $zoho_account_id = $val['content'];
    //         } else if($val['val'] == 'Account Name'){
    //             $zoho_account_name = $val['content'];
    //         }
    //     }
    // }

    return array('zoho_account_id' => $zoho_account_id, 'zoho_account_name' => $zoho_account_name);
}

function validateUserInZoho($username, $company_name, $admin_email = null)
{
    $param = array();
    // $param['url'] = "https://crm.zoho.com/crm/private/json/Contacts/searchRecords";
    // $param['query'] = "authtoken=".ZOHO_APIKEY."&scope=crmapi&criteria=(Email:".$username.")&selectColumns=Contacts(Email,Account Name,ADS User Role)";
    // $contact_data = process_json_curl_($param['url'], $param['query']);

    $userInfo                   = searchUserInZoho('Contacts/search', 'email=' . $username);
    $sql                        = "SELECT zoho_account_id FROM company WHERE company_name = '$company_name'";
    $company                    = execute_query_get_result($sql);
    $adsphere_company_zoho_id   = !empty($company[0]['zoho_account_id']) ? $company[0]['zoho_account_id'] : '';
    $zoho_account_details       = get_zoho_acccount_id_from_adsphere_account_id($adsphere_company_zoho_id);
    extract($zoho_account_details);

    if (!empty($userInfo) && isset($userInfo->data)) {
        $user_role          = $account_name = '';
        $contact_data       = $userInfo->data[0];
        $account_name       = $contact_data->Account_Name->name;
        $user_role          = $contact_data->ADS_User_Role;

        if ($adsphere_company_zoho_id == '') { //if user exists in zoho and not in Adsphere and its a new company, then create user with new company
            $zoho_account_details['zoho_account_id'] = '';
            $zoho_account_details['zoho_account_name'] = $company_name;

            return $zoho_account_details;
        }

        if (!empty($zoho_account_id) && $user_role == 'Admin' && ($zoho_account_id != $adsphere_company_zoho_id)) {
            $email_msg = "Super Admin trying to create new company " . $company_name . " with admin " . $username . ". But user " . $username . " is associated with company " . $account_name . " in Zoho.";
            $subject = "Super Admin trying to create new company";
            newUserMoveToNewAccountEmail($email_msg, $subject);
            return $zoho_account_details;
        }

        if (!empty($zoho_account_id) && ($zoho_account_id != $adsphere_company_zoho_id)) {
            if ($admin_email == 'super_admin') {
                $email_msg = "Super Admin has created new company " . $company_name . " with Admin " . $username . ", however " . $username . " was previously associated with " . $account_name . ".";
                $subject = "User shifted to new company with role as Admin";

            } else {
                $email_msg = "Admin " . $admin_email . " successfully moved user " . $username . " from " . $account_name . " to " . $company_name;
                $subject = "User shifted to new company";
            }

            newUserMoveToNewAccountEmail($email_msg, $subject);
            return $zoho_account_details;
        }

        $zoho_account_details['new_account'] = 0;
        // return 0;
        return $zoho_account_details;
    }

    // if (isset($contact_data['response']['result']['Contacts']['row']) || !empty($contact_data['response']['result']['Contacts']['row'])) {
    //     $user_role = $account_name = '';
    //     foreach ($contact_data['response']['result']['Contacts']['row']['FL'] as $ky => $val) {
    //         if ($val['val'] == 'Account Name') {
    //             $account_name = $val['content'];
    //         } else if ($val['val'] == 'ADS User Role') {
    //             $user_role = $val['content'];
    //         }
    //     }

    //     if ($adsphere_company_zoho_id == '') { //if user exists in zoho and not in Adsphere and its a new company, then create user with new company
    //         $zoho_account_details['zoho_account_id'] = '';
    //         $zoho_account_details['zoho_account_name'] = $company_name;

    //         return $zoho_account_details;
    //     }

    //     if (!empty($zoho_account_id) && $user_role == 'Admin' && ($zoho_account_id != $adsphere_company_zoho_id)) {
    //         $email_msg = "Super Admin trying to create new company " . $company_name . " with admin " . $username . ". But user " . $username . " is associated with company " . $account_name . " in Zoho.";
    //         $subject = "Super Admin trying to create new company";
    //         newUserMoveToNewAccountEmail($email_msg, $subject);
    //         return $zoho_account_details;
    //     }

    //     if (!empty($zoho_account_id) && ($zoho_account_id != $adsphere_company_zoho_id)) {
    //         if ($admin_email == 'super_admin') {
    //             $email_msg = "Super Admin has created new company " . $company_name . " with Admin " . $username . ", however " . $username . " was previously associated with " . $account_name . ".";
    //             $subject = "User shifted to new company with role as Admin";

    //         } else {
    //             $email_msg = "Admin " . $admin_email . " successfully moved user " . $username . " from " . $account_name . " to " . $company_name;
    //             $subject = "User shifted to new company";
    //         }

    //         newUserMoveToNewAccountEmail($email_msg, $subject);
    //         return $zoho_account_details;
    //     }

    //     $zoho_account_details['new_account'] = 0;
    //     return 0;
    // }

    $zoho_account_details['new_account'] = 1;
    return $zoho_account_details;

}

function process_json_curl_($url, $query)
{
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

function get_real_value_from_formatted_value($value) {		
    $value = (float) str_replace(',', '', $value);     		
    return $value;		
}
function get_processed_number_spendIndex_totalShare($input1, $input2)
{
    $input1 = get_real_value_from_formatted_value($input1);
    $input2 = get_real_value_from_formatted_value($input2);
    $first_number_integer_pad = 10;
    $first_number_fraction_pad = NO_OF_DIGITS_FOR_NETWORK_MARKET_SHARE + 1;
    $second_number_integer_pad = 10;
    $second_number_fraction_pad = NO_OF_DIGITS_FOR_NETWORK_MARKET_SHARE + 1;

    if ($input1 == 0) {
        $first_number_integer_pad = 1;
        $first_number_fraction_pad = 0;
        $second_number_integer_pad = NO_OF_DIGITS_FOR_NETWORK_MARKET_SHARE + 2;
        $second_number_fraction_pad = NO_OF_DIGITS_FOR_NETWORK_MARKET_SHARE + 1;
    }

    $fraction = get_number_parts($input1)['fraction'];
    $integer = get_number_parts($input1)['integer'];
    $pad_length_integer = $first_number_integer_pad - strlen($integer);
    $pad_length_fraction = $first_number_fraction_pad - strlen($fraction);
    $pad_string = 0;

    $result1 = pad_numbers($integer, $pad_length_integer, 'left');
    $result2 = pad_numbers($fraction, $pad_length_fraction, 'right');
    $input1 = $result1 . "." . $result2;

    ////number 2
    $fraction = get_number_parts($input2)['fraction'];
    $integer = get_number_parts($input2)['integer'];
    $pad_length_integer = $second_number_integer_pad - strlen($integer);
    $pad_length_fraction = $second_number_fraction_pad - strlen($fraction);
    $pad_string = 0;

    $result1 = pad_numbers($integer, $pad_length_integer, 'left');
    $result2 = pad_numbers($fraction, $pad_length_fraction, 'right');
    $input2 = $result1 . $result2;

    // echo $input1 = floatval($input1 . $input2);
    return floatval($input1 . $input2);
}

function get_number_parts($input)
{
    $number = explode('.', $input);
    $integer = $number[0];

    if (!empty($number[1])) {
        $fraction = $number[1];
    } else {
        $fraction = 0;
    }

    return array('integer' => $integer, 'fraction' => $fraction);
}

function pad_numbers($number, $no_of_digits, $direction)
{
    for ($i = 1; $i <= $no_of_digits; $i++) {
        if ($direction == 'right') {
            $number = $number . '0';
        } else {
            $number = '0' . $number;
        }
    }

    return $number;
}

function get_all_weeks($year)
{
   // if (!defined('ALL_WEEKS')) {
    $query = 'SELECT media_week_start , media_week_end, media_week, media_year FROM `media_calendar` WHERE media_year = '.$year.' order by media_week';
    $weeks = execute_query_get_result($query, 'FETCH_ASSOC');
    foreach ($weeks as $key => $value) {
        $year = $value['media_year'];
        $week = ($value['media_week']);
        $return[$year][$week] = $value;
    }
    // define('ALL_WEEKS', serialize($return));
    // }
    // return unserialize(ALL_WEEKS);
    return $return;
}

function get_weeks_by_year($year)
{
    // $weeks = get_all_weeks($year);
    $query = 'SELECT media_week_start , media_week_end, media_week, media_year FROM `media_calendar` WHERE media_year = '.$year.' order by media_week';
    $weeks = execute_query_get_result($query, 'FETCH_ASSOC');
    $w = array();
    foreach ($weeks as $key => $value) {
        array_push($w, $value);
    }
    return $w;
}

function get_all_months()
{
    if (!defined('ALL_MONTHS')) {
        $query = 'SELECT media_month_start , media_month_end, media_month, media_year FROM `media_calendar` group by media_month, media_year order by media_year,media_month';
        $months = execute_query_get_result($query, 'FETCH_ASSOC');

        foreach ($months as $key => $value) {
            $year = $value['media_year'];
            $month = $value['media_month'];
            $return[$year][$month] = $value;
        }

        define('ALL_MONTHS', serialize($return));
    }

    return unserialize(ALL_MONTHS);
}

function get_months_by_year($year)
{
    // $months = get_all_months();
    $query = 'SELECT media_month_start , media_month_end, media_month, media_year FROM `media_calendar` WHERE media_year  = '.$year.'  group by media_month order by media_month';
    $months = execute_query_get_result($query, 'FETCH_ASSOC');
    $m = array();
    foreach ($months as $key => $value) {
        array_push($m, $value);
    }
    return $m;
}


function get_all_qtrs()
{
    if (!defined('ALL_QTRS')) {
        $query = 'SELECT media_qtr_start , media_qtr_end, media_qtr, media_year FROM `media_calendar` group by media_qtr, media_year order by media_year,media_qtr';
        $qtrs = execute_query_get_result($query, 'FETCH_ASSOC');

        foreach ($qtrs as $key => $value) {
            $year = $value['media_year'];
            $qtr = $value['media_qtr'];
            $return[$year][$qtr] = $value;
        }

        define('ALL_QTRS', serialize($return));
    }

    return unserialize(ALL_QTRS);
}
function get_qtrs_by_year($year)
{
    // $qtrs = get_all_qtrs();
    $query = 'SELECT media_qtr_start , media_qtr_end, media_qtr, media_year FROM `media_calendar` WHERE media_year =  '.$year.'  group by media_qtr order by media_qtr';
    $qtrs = execute_query_get_result($query, 'FETCH_ASSOC');
    $q = array();
    foreach ($qtrs as $key => $value) {
        array_push($q, $value);
    }
    return $q;
    // return $qtrs[$year];
}
function get_all_years()
{
    if (!defined('ALL_YEARS')) {
        $query = 'SELECT media_year_start, media_year_end, media_year FROM `media_calendar` where "'.customDate('Y-m-d').'" >= media_year_start group by media_year order by media_year ASC
        ';
        $years = execute_query_get_result($query, 'FETCH_ASSOC');

        foreach ($years as $key => $value) {
            $year = $value['media_year'];
            $return[$year] = $value;
        }

        define('ALL_YEARS', serialize($return));
    }

    return unserialize(ALL_YEARS);
}

function get_networks_from_cache($requestData)
{
    $last_week_details = Slim_App_Lib_Common::getLastMediaWeek();

    if ($requestData->sd == $last_week_details['sd'] &&
        $requestData->ed == $last_week_details['ed'] &&
        $requestData->c == 1 &&
        $requestData->cat == "all" &&
        $requestData->creative_duration == "all_short_duration" &&
        $requestData->new_filter_opt == "none" &&
        $requestData->responseType == "( response_url=1  or  response_sms=1  or  response_tfn=1  or  response_mar=1 )" &&
        urldecode($requestData->spanish) == '0,1' &&
        $requestData->unchecked_category == "" &&
        $requestData->length_unchecked == "0" &&
        ($requestData->applied_ids == '') &&
        ($requestData->primary_tab == '')
    ) {

        $clause = "start_date = {$last_week_details['sd']} AND end_date = {$last_week_details['ed']}";
        $params['component'] = 'ranking_network_popup';
        $params['clause'] = $clause;

        $cached_network_result = get_query_result('__query_get_cached_data', $params);
        if (empty($cached_network_result)) {
            return array('status' => 2, 'data' => '');
        } else {
            return array('status' => 1, 'data' => json_decode($cached_network_result[0]['result']));
        }
    } else {
        return array('status' => 0, 'data' => '');
    }
}

function get_rankings_from_cache($requestData, $tab)
{
    //$tab = 1 => brand
    //$tab = 0 => advertiser

    $last_week_details = Slim_App_Lib_Common::getLastMediaWeek();

    $encoded_responsetype = urlencode(" response_url=1  or  response_sms=1  or  response_tfn=1  or  response_mar=1 ");
    $encoded_responsetype = '('.$encoded_responsetype.')';
    if ($requestData['sd'] == $last_week_details['sd'] &&
        $requestData['ed'] == $last_week_details['ed'] &&
        $requestData['val'] == 1 &&
        $requestData['c'] == 1 &&
        ($requestData['cat'] == "" || $requestData['cat'] == "all" || $requestData['unchecked_category'] == "") &&
        $requestData['flag'] == 2 &&
        urldecode($requestData['spanish']) == '0,1' &&
        $requestData['responseType'] == $encoded_responsetype &&
        $requestData['new_filter_opt'] == "none" &&
        $requestData['refine_filter_opt'] == "" &&
        $requestData['refine_filter_opt_text'] == "" &&
        $requestData['refine_apply_filter'] == "0" &&
        $requestData['creative_duration'] == "all_short_duration" &&
        $requestData['new_filter_opt'] == "none" &&
        $requestData['applied_ids'] == "" &&
        (!isset($requestData['list_id'])) &&
        (empty($requestData['network_code']) || $requestData['network_code'] == "")

    ) {
        if(SMI_BUILD == 1) {
            $last_week_data_html  = ($tab == 1) ?  'last_week_data_html_brand_smi' : 'last_week_data_html_advertiser_smi';
            $last_week_export_data  = ($tab == 1) ?  'last_week_export_data_brand_smi' : 'last_week_export_data_advertiser_smi';
        } else {
            if ($tab == 1) {
                $last_week_data_html = 'last_week_data_html_brand';
                $last_week_export_data = 'last_week_export_data_brand';
            } else {
                $last_week_data_html = 'last_week_data_html_advertiser';
                $last_week_export_data = 'last_week_export_data_advertiser';
            }
        }

        $clause = "start_date = {$last_week_details['sd']} AND end_date = {$last_week_details['ed']}";
        $params['component'] = $last_week_data_html;
        $params['clause'] = $clause;

        $cached_ranking_result = get_query_result('__query_get_cached_data', $params);
        if (empty($cached_ranking_result)) {
            return array('status' => 2, 'data' => '');
        } else {
            return array('status' => 1, 'data' => json_decode($cached_ranking_result[0]['result']));
        }
    } else {
        return array('status' => 0, 'data' => '');
    }
}

function newUserMoveToNewAccountEmail($email_msg, $subject)
{
    $name = 'Joseph';
    // $to = FEEDBACK_EMAIL;
    $to = 'ashwini.rewatkar@v2solutions.com';
    $subject = $subject;
    $message = 'Dear ' . $name . ',<br/><br/>';
    $message .= $email_msg . "</b>
                    <br><br><br>
                    Thanks,<br>
                    DRMetrix";

    $headers = 'From: info@drmetrix.com' . "\r\n" .
        'MIME-Version: 1.0' . "\r\n" .
        'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    require 'PHPMailer/class.phpmailer.php';
    ob_start();
    $mail = new PHPMailer(); //New instance, with exceptions enabled
    $mail->IsSendmail(); // tell the class to use Sendmail
    $body = $message;
    $mail->SetFrom('info@drmetrix.com', 'DRMetrix');
    $mail->AddAddress($to);
    $mail->Subject = $subject;
    $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
    $mail->WordWrap = 80; // set word wrap
    $mail->MsgHTML($body);
    $mail->IsHTML(true); // send as HTML
    $mail->Send();
    ob_end_clean();
}

function getWeekAllDays($begin, $end) {
    $begin      = new DateTime($begin);
    $end        = new DateTime($end);
    $end        = $end->modify('+1 day');
    $current_d  = date('Y-m-d');
    $daterange  = new DatePeriod($begin, new DateInterval('P1D'), $end);
    $return     = array();

    foreach ($daterange as $date) {
        $broadcast_day = $date->format("Ymd") . date("His");
        if ($date->format("Ymd") == date("Ymd") && $broadcast_day >= date("Ymd060000")) {
            if ($current_d >= $date->format("Y-m-d")) {
                $return[] = $date->format("m/d/Y");
            }
        } elseif ($date->format("Ymd") < date("Ymd")) {
            if ($current_d >= $date->format("Y-m-d")) {
                $return[] = $date->format("m/d/Y");
            }
        }
    }

    if (count($return) == 0) {
        $return[] = date('m/d/Y');
    }

    return $return;
}

function getMediaCalendarWeekData($params)
{
    $result = get_query_result('__query_get_media_calendar_data', $params, 'FETCH_ASSOC');
    foreach ($result as $key => $value) {
        $year       = $value['media_year'];
        $week       = $value['media_week'];
        $week_days  = getWeekAllDays($value['media_week_start'], $value['media_week_end']);
        
        if(count($week_days) != 0) {
            $response[$year][$week]['media_week']   = $week;
            $response[$year][$week]['start_date']   = date_format(new DateTime($value['media_week_start']),"m/d/Y");
            $response[$year][$week]['end_date']     = date_format(new DateTime($value['media_week_end']),"m/d/Y");
            $response[$year][$week]['week_days']    = $week_days;
        }
    }
    return $response;
}

function getMediaYearOnly()
{
    $result = get_query_result('__query_get_media_year_only', '', 'FETCH_ASSOC');
    return $result;
}

function createImageLink_ranking($creative_id, $airing_id, $no_of_days = EXCEL_VIDEO_EXPIRY_DAYS)
{
    $query_string = base64_encode("creative_id=$creative_id&airing_id=$airing_id&date=" . time()."&page=Thumbnail");
    return 'http://' . HOST . "/drmetrix/video/{$query_string}?video=2";
}

function getAllCategories()
{
    $cat_array = array();
    $sub_cat_arr = $power = $cat_array = array();

    $get_res = get_query_result('__query_get_all_category_detail', '', 'FETCH_ASSOC');

    for ($i = 0; count($get_res) > $i; $i++) {
            $power['category'] = $get_res[$i]['category'];
            $power['category_id'] = $get_res[$i]['category_id'];

            $suub = explode('|', $get_res[$i]['Sub']);
            $appendMe = [];
            foreach($suub as $k => $val){
                $suub1 = explode('$', $val);
                $data = [];
                $data['sub_category_id'] = $suub1[0];
                $data['sub_category'] = $suub1[1];
                $appendMe[] = $data;
                $suub1 = [];
            }
            $power['subcategory'] = $appendMe;
            $cat_array[] = $power;
    }
    return $cat_array;
}

function checkAdminStatus($user2)
{
    $db = getConnection();
    $sql = "SELECT status, email, first_name, last_name, company_id FROM user WHERE user_id = (SELECT admin_id FROM admin_user WHERE user_id = " . $user2[0]->user_id . ")";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $admin_user = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (!empty($admin_user)) {
        return $admin_user[0];
    }
}

function inactiveUserLoginAlertEmail($user)
{
    $status = "";
    if ($user[0]->status == 'inactive') {
        $status = 'inactive';
    } elseif ($user[0]->status == 'active' && $user[0]->role == "user") {
        $adminDetls = checkAdminStatus($user);
        $status = $adminDetls->status;
    }

    if ($status == 'inactive') {
        $name = 'Joseph';
        $to = FEEDBACK_EMAIL;
        $subject = "Inactive User logged-in";
        $message = 'Dear ' . $name . ',<br/><br/>';
        $message .= "User " . $user[0]->username . " has INACTIVE in the AdSphere system but still logged-in</b>
                        <br><br><br>
                        Thanks,<br>
                        DRMetrix";

        $headers = 'From: info@drmetrix.com' . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        require 'PHPMailer/class.phpmailer.php';
        ob_start();
        $mail = new PHPMailer(); //New instance, with exceptions enabled
        $mail->IsSendmail(); // tell the class to use Sendmail
        $body = $message;
        $mail->SetFrom('info@drmetrix.com', 'DRMetrix');
        $mail->AddAddress($to);
        $mail->Subject = $subject;
        $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
        $mail->WordWrap = 80; // set word wrap
        $mail->MsgHTML($body);
        $mail->IsHTML(true); // send as HTML
        $mail->Send();
        ob_end_clean();
    }
}

function get_market_share_for_tooltip($spend_index, $total_share, $round = 0)
{
    if ($total_share == 0) {
        $total_share = 1;
    }

    return round(($spend_index / $total_share * 100), $round);
}

function dateDiff($start, $end)
{
    $start_ts = strtotime($start);
    $end_ts = strtotime($end);
    $diff = $end_ts - $start_ts;

    return round($diff / 86400);
}

function moveFiles($oldFilePath, $newFilePath)
{
    if ($_SERVER['HTTP_HOST'] == "localhost") {
        // rename( "'.$oldFilePath.'",  "'.$newFilePath.'" );
        if (copy("'.$oldFilePath.'", "'.$newFilePath.'")) {
            unlink($oldFilePath);
        }
    } else {
        shell_exec('mv "' . $oldFilePath . '" "' . $newFilePath . '"');
    }
}

function cleanFileName($file_name)
{
    $file_name = preg_replace('!\s+!', ' ', $file_name);
    $file_name = preg_replace("/[^a-zA-Z0-9\[\]\.\_\+\ \-\(\)]/", "", $file_name);
    //   $find       = array("{", "%7B", "}", "%7D", "'","!","@","#","$","%","^","&","*","(",")","~","`","\\", "/",":",'"',"?",";","<",">","(",")","[","]");
    //    $file_name = str_replace($find, "", $file_name);
    return $file_name;
}

function findSpendIndex($spendIndexCalculate, $format=1)
{
    // if ($spendIndexCalculate['classification'] > DEFAULT_CLASSIFICATION) {
    $pos = strpos($spendIndexCalculate['classification'],DEFAULT_CLASSIFICATION);

    if($pos === false) {
     // string needle NOT found in haystack
        $spendIndex = $spendIndexCalculate['projected_score'];
    } else {
        $projected_score = ($spendIndexCalculate['projected_score'] * 100 / $spendIndexCalculate['sum']);
        $spendIndex = custom_round($projected_score);
    }


    if ($format == 1) {
        $return = number_format($spendIndex, 0);
    } else {
        $return = $spendIndex;
    }

    return $return;
}

function getMaxSpendValue($total, $classification) {
    if ($classification > 5) {
        return 100;    
    } else {
        return $total;
    }
}

function setColumnNumberFormat($objPHPExcel , $column_index_array) {
    foreach($column_index_array as $key => $value) {
        $objPHPExcel->getActiveSheet()->getStyle($value)->getNumberFormat()->setFormatCode('#,##0');
    }

}
/***** Start-- Excel common functions *******/
function styleHeaderExcel()
{
    $styleHeader = array(
        'font' => array(
            'size' => 16,
            'name' => 'Calibri',
        ), 'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ), 'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
            'rotation' => 90,
            'startcolor' => array(
                'argb' => 'B5B5B5',
            ), 'endcolor' => array(
                'argb' => 'E0E0E0',
            ),
        ),
    );
    return $styleHeader;
}
function styleHeaderAdvBrandExcel()
{
    $styleHeader = array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => 'FFFFFF'),
            'size' => 11,
            'name' => 'Verdana',
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '202b39'),
        ),
    );
    return $styleHeader;
}
function styleSubHeaderExcel()
{
    $styleSubHeader = array(
        'font' => array(
            'size' => 11,
            'bold' => true,
            'name' => 'Calibri',
        ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ), 'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '00beff'),
        ),
    );
    return $styleSubHeader;
}
function styleSubHeaderAdvBrandExcel()
{
    $styleSubHeader = array(
        'font' => array(
            'color' => array('rgb' => 'FFFFFF'),
            'size' => 11,
            'name' => 'Verdana',
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '202b39'),
        ),
    );
    return $styleSubHeader;
}
function styleSubHeaderRankingExcel()
{
    $styleSubHeader = array(
        'font' => array(
            'color' => array('rgb' => 'FFFFFF'),
            'size' => 11,
            'bold' => true,
            'name' => 'Calibri',
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '202b39'),
        ),
    );
    return $styleSubHeader;
}
function highlightRowExcel()
{
    $highlightrow = array(
        'font' => array(
            'color' => array('rgb' => 'FFFFFF'),
            'size' => 11,
            'bold' => true,
            'name' => 'Calibri',
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '05beff'),
        ),
    );
    return $highlightrow;
}
function styleExcel()
{
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
            ),
        ),
    );
    return $styleArray;
}
function styleRowsCatExcel()
{
    $styleRowsCat = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => 'acacac'),
        ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    return $styleRowsCat;
}
function styleRowsExcel()
{
    $styleRows = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '00beff'),
        ), 'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    return $styleRows;
}
function styleHorizontalCenterAlignmentExcel()
{
    $styleDataRows = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    return $styleDataRows;
}
function styleHorizontalLeftAlignmentExcel()
{
    $styleDataRows = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        ),
    );
    return $styleDataRows;
}
/***** End-- Excel common functions *******/

function createTable($table_name, $columns_array)
{
    // $table_name = 'zoho_contact_information_v1';
    $drop_table = "DROP TABLE IF EXISTS $table_name";
    execute_sql($drop_table);
    $table = "CREATE TABLE `$table_name` (";
    $table .= 'id int NOT NULL PRIMARY KEY  AUTO_INCREMENT,';

    foreach ($columns_array as $column) {
        $column = str_replace(' ', '__', $column);
        $table .= $column . ' varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,';
    }

    $table = trim($table, ',');
    $table .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';

    execute_sql($table);
}

function process_curl_for_zoho($url, $query)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response_info = curl_getinfo($ch);
    $response = curl_exec($ch);
 
    curl_close($ch);
    return true;
}

function get_sql_query_for_contact_mismatch()
{
    $sql_query_for_contact_mismatch = " SELECT
    zoho_contact_information.CONTACTID zoho_contact_id,
    zoho_contact_information.Email zoho_email, user.email ads_email,
    zoho_contact_information.Mobile zoho_mobile, CONCAT(IF(user.country_code=1 , '', CONCAT(user.country_code, '-')), user.phone_number) ads_mobile,
    zoho_contact_information.ADS__User__Status zoho_user_status, IF(u.status = 'inactive',zoho_contact_information.ADS__User__Status,IF(u.status = 'inactive' OR user.status = 'inactive', 'inactive', IF(user.status = 'deleted','deleted','active'))) ads_user_status,
    zoho_contact_information.ADS__Username zoho_username, user.username ads_username,
    zoho_contact_information.ADS__User__Role zoho_user_role, CONCAT(UCASE(LEFT(user.role, 1)), SUBSTRING(user.role, 2)) ads_user_role,
    zoho_contact_information.ADS__Record__ID zoho_record_id, user.user_id ads_record_id,
    zoho_contact_information.ADS__Login__Count zoho_login_count, COUNT(IF(user_logs.user_id IS NOT NULL, 1, NULL)) as ads_login_count ,
    zoho_contact_information.Account__Name zoho_account_name, concat(IF(company.company_name IS NULL, '', company.company_name), IF(company2.company_name IS NULL, '', company2.company_name)) ads_account_name,
    zoho_contact_information.Title zoho_title, user.position ads_title,
    zoho_contact_information.Contact__Owner zoho_contact_owner, account_owner.name ads_contact_owner,
    account_owner.email ads_contact_owner_email,
    zoho_contact_information.Tracking__And__Alerts zoho_tracking_alert, IF(user.tracking_alert_subscribed=1 AND user.no_of_alerts_subscribed IS NOT NULL AND user.no_of_alerts_subscribed > 0, 'true', 'false') ads_tracking_alert,
    zoho_contact_information.EULA__Accepted zoho_eula_flag, IF(user.eula_flag='1', 'true', 'false') ads_eula_flag,
    zoho_contact_information.ADS__Last__Login zoho_last_login, user.last_login ads_last_login,
    zoho_contact_information.ADS__Authenticated zoho_ads_authenticated, IF(user.adsphere_authenticate='1', 'true', 'false') ads_adsphere_authenticate,
    zoho_contact_information.Authy__Authenticated zoho_authy_authenticated, IF(user.authy_cookie IS NULL, 'false', 'true') ads_authy_authenticated,
    zoho_contact_information.EULA__Accept__IP zoho_eula_accept_ip, user.eula_ip ads_eula_accept_ip,
    zoho_contact_information.EULA__Accept__Date zoho_eula_accept_date, user.eula_datetime ads_eula_accept_date,
    zoho_contact_information.Client zoho_client, IF(u.client='Yes' OR (user.client='Yes' AND u.client IS NULL), 'true', 'false') ads_client,
    concat(user.first_name, ' ', user.last_name) as Name

    FROM  user LEFT join admin_user on user.user_id =  admin_user.user_id
    LEFT JOIN
    user u on u.user_id = admin_user.admin_id LEFT JOIN user_logs on user.user_id = user_logs.user_id LEFT JOIN pricing on admin_user.admin_id = pricing.user_id LEFT JOIN company on company.id = pricing.company_id LEFT JOIN company company2 on user.company_id=company2.id LEFT JOIN zoho_contact_information on user.zoho_contact_id=zoho_contact_information.CONTACTID LEFT JOIN account_owner ON account_owner.email = user.account_owner

    GROUP by user.user_id
    HAVING

    zoho_email <> user.email OR
    zoho_contact_information.Mobile <> ads_mobile OR
    LCASE(zoho_user_status) <> LCASE(ads_user_status) OR
    zoho_contact_information.ADS__Username <> user.username OR
    LCASE(zoho_contact_information.ADS__User__Role) <> LCASE(ads_user_role) OR
    zoho_contact_information.ADS__Record__ID <> user.user_id OR
    zoho_contact_information.ADS__Login__Count <> ads_login_count OR
    zoho_contact_information.Tracking__And__Alerts <> ads_tracking_alert OR
    zoho_contact_information.EULA__Accepted <> ads_eula_flag OR
    /*(zoho_contact_information.ADS__Last__Login != user.last_login AND DATE_FORMAT(CONVERT_TZ(zoho_contact_information.ADS__Last__Login,'+00:00','+03:00'), '%Y%m%d%H%i') <> DATE_FORMAT(user.last_login, '%Y%m%d%H%i')) OR*/
    zoho_contact_information.ADS__Authenticated <> ads_adsphere_authenticate OR
    zoho_contact_information.Authy__Authenticated <> ads_authy_authenticated OR
    zoho_contact_information.EULA__Accept__IP <> user.eula_ip OR
    /*(zoho_contact_information.EULA__Accept__Date != user.eula_datetime AND DATE_FORMAT(CONVERT_TZ(zoho_contact_information.EULA__Accept__Date,'+00:00','+03:00'), '%Y%m%d%H%i') <> DATE_FORMAT(user.eula_datetime, '%Y%m%d%H%i')) OR*/
    zoho_contact_information.Client <> ads_client
    ";
    return $sql_query_for_contact_mismatch;
}

function update_users_tracking_alert_count()
{
    // update tracking alert count in users
    $update_tracking_count = '
        UPDATE user
        LEFT JOIN (SELECT user_id,COUNT(*) alert_count FROM tracking_and_alerts GROUP BY user_id) as tracking_and_alerts
        ON tracking_and_alerts.user_id = user.user_id
        SET user.no_of_alerts_subscribed = tracking_and_alerts.alert_count';
    execute_sql($update_tracking_count);
}

function get_user_details_for_tracking()
{
    $sql = 'SELECT user.user_id, concat(user.first_name, " ", user.last_name) as name, user.role, company.company_name, company.network_tab, IF(u.username IS NULL, user.username, u.username) as admin_name FROM `user` LEFT JOIN admin_user on user.user_id=admin_user.user_id
    LEFT JOIN user u ON u.user_id=admin_user.admin_id
    LEFT JOIN pricing ON admin_user.admin_id=pricing.user_id OR user.user_id=pricing.user_id
    LEFT JOIN company ON pricing.company_id=company.id';

    $user = execute_query_get_result($sql);
}

function setSessionForMaxSpend($is_adv_page){
    if($is_adv_page == 1) {
        if( !isset($_SESSION['max_spend_adv']) || $_SESSION['max_spend_adv'] <= 0) {
            $max_spend = 1;
        } else {
            $max_spend = $_SESSION['max_spend_adv'];
        }
    } else {
        if( !isset($_SESSION['max_spend']) || $_SESSION['max_spend'] <= 0) {
            $max_spend = 1;
        } else {
            $max_spend = $_SESSION['max_spend'];
        }
    }
    return $max_spend; 
}

function check_base64_encoded($string)
{
    $regex = '/^\d+(?:,\d+)*$/';
    if (preg_match($regex, $string)) {
        return 0;
    } else {
        return 1;
    }
}

function getVideoStreamingUrl()
{
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        $video_api = 'http://video.drmetrix.com/';
    } else {
        $video_api = VIDEO_STREAMING_URL;
    }

    return $video_api;
}

function createFolderForDownloads($creative_info)
{
    if($creative_info[0]->page == 'Video') {
        $url = CREATIVE_VIDEOS_FOLDER . $creative_info[0]->user_id . '/' . $creative_info[0]->creative_id;
    } else {
        $url = CREATIVE_THUMBNAILS_FOLDER . $creative_info[0]->user_id . '/' . $creative_info[0]->creative_id;
    }
    if (!file_exists($url)) {
        mkdir($url, 0777, true);
    }

    return $url;
}

function redirect($url, $statusCode = 303)
{
    header('Location: ' . $url, true, $statusCode);
    die();
}

// download_video();
function download_video_on_local($url, $creative_info)
{
    set_time_limit(0);
    $file_name = $creative_info[0]->creative_name . '-' . $creative_info[0]->length;
    $file_name = str_replace(" ", "_", $file_name) . '.' . $creative_info[0]->video_extension;
    //$url    = 'https://www.sample-videos.com/video/mp4/720/big_buck_bunny_720p_1mb.mp4';
    $download_url = createFolderForDownloads($creative_info);
    $download_url = $download_url . '/' . $file_name;
    file_put_contents($download_url, fopen($url, 'r'));
    return $file_name;
}

function push_video_on_browser($filepath)
{
    //$filepath = 'http://dev.drmetrix.com/drmetrix/creative_videos/669/50627219/Lemonade_Stand-30.mp4';
    header('Expires: Mon, 1 Apr 1974 05:00:00 GMT');
    header('Pragma: no-cache');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Download');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    header('Content-Transfer-Encoding: binary');
    readfile($filepath);
    exit();
}

function prepend_leading_characters($num, $number_of_digits = 2, $characters = '0')
{
    return sprintf("%{$characters}{$number_of_digits}d", $num);
}

function deleteUser($params)
{
    extract($params);
    if($_SESSION['role'] == 'superadmin') {
        // $params['soft_delete'] = 1;
        if ($role == 'admin') {
            deleteFromPricing($params);
            deleteAdminUserFromAdminUser($params);
        } else {
            $users = selectUserFromAdminUser($params);
            if (!empty($users)) {
                $users = implode($users, ",");
                $params['id'] = $users;
                deleteUserFromAdminUser($params);
            }
        }
        deleteFromExcelExport($params);
        deleteFromResultLog($params);
        deleteFromTrackingAlerts($params);
        deleteFromUserFilters($params);
        deleteFromUserLogs($params);
        deleteFromSearchLogs($params);
        deleteUserFromUser($params);    
    } else {
        $params['soft_delete'] = 1;
        updateDeleteFlag($params);
    }
    // if ($role == 'admin') {
    //     $params['soft_delete'] = 0;
    //     // deleteFromPricing($params);
    //     // deleteAdminUserFromAdminUser($params);
    // } else {
    //     $params['soft_delete'] = 1;
    //     $users = selectUserFromAdminUser($params);
    //     if (!empty($users)) {
    //         $users = implode($users, ",");
    //         $params['id'] = $users;
    //         deleteUserFromAdminUser($params);
    //     }
    //     deleteFromExcelExport($params);
    //     deleteFromResultLog($params);
    //     deleteFromTrackingAlerts($params);
    //     deleteFromUserFilters($params);
    //     deleteFromUserLogs($params);
    //     deleteFromSearchLogs($params);
    //     deleteUserFromUser($params);
    // }
    
}

function  updateDeleteFlag($params) {
    $sql = __query_update_delete_by_admin_field($params);
    execute_sql($sql);
}

function deleteFromPricing($params)
{
    $sql = __query_delete_from_pricing($params);
    execute_sql($sql);
}

function deleteUserFromUser($params)
{
    $sql = __query_delete_user_from_user($params);
    execute_sql($sql);
}

function deleteUserFromAdminUser($params)
{
    $sql = __query_delete_user_from_admin_user($params);
    execute_sql($sql);
}

function deleteAdminUserFromAdminUser($params)
{
    $sql = __query_delete_admin_user_from_admin_user($params);
    execute_sql($sql);
}

function deleteFromExcelExport($params)
{
    $sql = __query_delete_user_from_excel_export($params);
    execute_sql($sql);
}

function deleteFromResultLog($params)
{
    $sql = __query_delete_user_result_log($params);
    execute_sql($sql);
}

function deleteFromTrackingAlerts($params)
{
    $sql = __query_delete_user_tracking_alerts($params);
    execute_sql($sql);
}

function deleteFromUserFilters($params)
{
    $sql = __query_delete_user_from_user_filters($params);
    execute_sql($sql);
}

function deleteFromUserLogs($params)
{
    $sql = __query_delete_user_from_userlogs($params);
    execute_sql($sql);
}

function selectUserFromAdminUser($params)
{
    if(!isset($params['type'])) { $type = ''; }

    extract($params);
    $result = get_query_result('__query_get_user_from_admin_user', $params, 'FETCH_OBJ');
    $users = [];
    foreach ($result as $key => $value) {
        if($type != '') {
            array_push($users, $value->user_id);
        }else{
            array_push($users, $value->id);
        }
        
    }
    return $users;
}

function deleteFromSearchLogs($params)
{
    $sql = __query_delete_user_from_search_logs($params);
    execute_sql($sql);
}

function updateUserIdPricing($params)
{
    $sql = __query_update_user_id_in_pricing($params);
    execute_sql($sql);
}

function updateUserInUsers($params)
{
    $sql = __query_update_user_in_users($params);
    execute_sql($sql);
}

function swapAdminUser($params)
{
    $sql = __query_swap_admin_user($params);
    execute_sql($sql);
}

function updateAdminIdAdminUser($params)
{
    $sql = __query_update_admin_id_admin_user($params);
    execute_sql($sql);
}

function getAdminOfUser()
{
    if ($_SESSION['role'] == 'user') {
        return $_SESSION['admin_id'];
    } else {
        return $_SESSION['user_id'];
    }
}

function zoho_log_in_db($zoho_id, $action, $variable, $value, $date = '')
{
    $date = empty($date) ? date('Y-m-d H:i:s') : $date;

    $sql = "INSERT INTO zoho_log (zoho_id, action, variable, value, date) VALUES($zoho_id, '{$action}', '{$variable}', '{$value}', '{$date}')";
    execute_sql($sql);
}

function api_exception_log($filename, $event, $response, $date = '')
{
    $date = empty($date) ? date('Y-m-d H:i:s') : $date;

    $sql = "INSERT INTO api_exception_log (filename, event , response,  date) VALUES('{$filename}', '{$event}', '{$response}', '{$date}')";
    execute_sql($sql);
}

function getLastLogin($redirect = 0)
{
    global $skip_urls;
    if(MAINTENANCE_PAGE == 1) {
        header('HTTP/1.1 307 Temporary Redirect');
        exit;
    }
    if (!empty($_SERVER['SHELL'])) {
        return;
    }

    $url = str_replace('/drmetrix/api/index.php/', "", $_SERVER['REQUEST_URI']);
    $url_arr = explode("?", $url);
    $url = $url_arr[0];
    if ((isset($_SESSION['username']) && $_SESSION['username'] == 'demo.user@drmetrix.com')) {
        return;
    }
    if (!in_array($url, $skip_urls)) {
        $db = getConnection();
        // $request    = Slim::getInstance()->request();
        // $user = json_decode($request->getBody());
        if (isset($_SESSION['user_id'])) {
            if($_SESSION['role'] == 'superadmin') {
                $sql = "SELECT DISTINCT(status),user_id, last_login FROM `user` where  user_id = " . $_SESSION['user_id'];
            } else {
                $sql = "SELECT DISTINCT(status),user_id, last_login FROM `user` where user_id IN (select admin_user.admin_id from admin_user where admin_user.user_id= " . $_SESSION['user_id'] . ") OR user_id = " . $_SESSION['user_id'];
            }
            $result = execute_query_get_result($sql, 'FETCH_OBJ');
            $active_count = 0;

            foreach ($result as $key => $value) {
                if ($value->status == 'active') {
                    $active_count += 1;
                    $user_id = $value->user_id;
                    $last_login = $value->last_login;
                    $user_array[$user_id] = $last_login;
                }
            }
            if (isset($user_array[$_SESSION['user_id']])) {
                $last_login = $user_array[$_SESSION['user_id']];
            } else {
                $last_login = '';
            }
            if ($active_count != count($result)) { //if admin or user both are not active
                header('HTTP/1.1 406 Not Acceptable');
                exit;
            } elseif (isset($_SESSION['lastLoginTime']) && strcmp($_SESSION['lastLoginTime'], $last_login) !== 0) {
                header('HTTP/1.1 401 Unauthorized');
                exit;
            } elseif (empty($_SESSION['version'])) {
                header('HTTP/1.1 409 Conflict');
                exit;
            } elseif (isset($_SESSION['version']) && $_SESSION['version'] != (defined('BETA_VERSION') ? BETA_VERSION : VERSION)) {
                header('HTTP/1.1 409 Conflict');
                exit;
            } else {
                $_SESSION['lastLoginTime'] = $last_login;
            }
        } else {
            header('HTTP/1.1 403 Unauthorized');
            if ($redirect == 1) {
                header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/drmetrix');
            }
            exit;
        }
    }
}

function isoDateFormatForZoho($create_date) {
    return date('c', strtotime($create_date));
}

function zoho_exception_log($updateResponse, $zoho_error_name) {
    if(isset($updateResponse->code) &&  $updateResponse->code!= 'SUCCESS'){         
        $filename               = basename($_SERVER['PHP_SELF']);
        api_exception_log($filename, $zoho_error_name , serialize($updateResponse));
        $updateResponse = [];
    } else {
        if(isset($updateResponse->data) && ($updateResponse->data[0]->code != 'SUCCESS')) {
            $filename               = basename($_SERVER['PHP_SELF']);
            api_exception_log($filename, $zoho_error_name , serialize($updateResponse));
            $updateResponse = [];
        }
    }
    return $updateResponse; 
}

function updatePricingCompanyId($params) {
    // update company id in pricing table
    $sql = __query_update_pricing_company_id($params);
    execute_sql($sql);
}

function updateUserCall($company_details, $user) {
    $db             = getConnection();
    $user_details   = createUserData($company_details, $user);

    $skip_authy_condition = '';
    if(isset($user->skip_authy) ) {
        $skip_authy_condition = ', authy_bypass_until = ' . ($user->skip_authy ? 'NOW()' : '"0000-00-00"');
    }

    $sql            = "UPDATE user SET client='".$user_details['client']."',zoho_contact_id = '".$user_details['zoho_contact_id']."', first_name = '".addslashes($user_details['first_name'])."',last_name = '".addslashes($user_details['last_name'])."',phone_number = '".$user_details['mobile']."',username = '".addslashes($user_details['username'])."',email = '".addslashes($user_details['username'])."', country_code='".$user_details['country_code']."', account_owner ='".addslashes($user_details['account_owner']) ."',position='".$user_details['position']."',assistant_admin = '".$user_details['assistant_admin']."', status='".$user_details['status']."' ".$user_details['update_authy'].", modified_date='".date("Y-m-d H:i:s")."' $skip_authy_condition WHERE user_id = '".$user_details['ads_record_id']."';";
    $stmt           = $db->prepare($sql);
    $stmt->execute();

}

function saveUserCall($company_details, $user) {
    $db             = getConnection();
    $user_details   = createUserData($company_details, $user);
    $userCreateArray = [];
    // $get_authy_detail = ConnectToAuthy($user_details['mobile'],$user_details['username'], $user_details['country_code']);
    $admin_id       = isset($user->admin_id) ? $user->admin_id : '';
    $passphrase     = isset($user->passphrase) ? $user->passphrase : date('Y-m-d-H:i:s'); //2015-06-30 12:40:27
    // if(!empty($get_authy_detail) && isset($get_authy_detail['authy_id'])){
        // $authy_id = $get_authy_detail['authy_id'];     
        if(!isset($company_details['company_id'])) {
            $sql      = "INSERT INTO user (first_name,last_name,username,phone_number,position,role,email,status,created_date,country_code,timeout,no_of_apps,authy_id,account_owner,passphrase,assistant_admin) VALUES('".addslashes($user_details['first_name'])."','".addslashes($user_details['last_name'])."','".addslashes($user_details['username'])."','".$user_details['mobile']."','".addslashes($user_details['position'])."','".$user_details['role']."','".addslashes($user_details['username'])."','active','".date("Y-m-d H:i:s")."','".$user_details['country_code']."','".$user_details['timeout']."','".$user_details['no_of_apps']."','".$user_details['authy_id']."','".$user_details['account_owner']."','".$passphrase."','".$user_details['assistant_admin']."' )";
        } else {
            $sql    = "INSERT INTO user (company_id,username,phone_number,email,role,created_date,modified_date,country_code,timeout,no_of_apps,authy_id,first_name,last_name,access_type,client,account_owner,passphrase,domain_override) VALUES ('".$company_details['ads_record_id']."','".addslashes($user_details['username'])."','".$user_details['mobile']."','".addslashes($user_details['username'])."','admin','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."','".$user_details['country_code']."','".$user_details['timeout']."','".$user_details['no_of_apps']."','".$user_details['authy_id']."','".addslashes($user_details['first_name'])."','".addslashes($user_details['last_name'])."', '".$user_details['access_type']."' ".$user_details['monthly_cap'].",'".$user_details['client']."','".$user_details['account_owner']."','".$passphrase."','".$user_details['domain_override']."')";
        }    
        
        $stmt    = $db->prepare($sql);
        $stmt->execute();
        $userId  = $db->lastInsertId();
        $userCreateArray['userId']   = $userId;

        if(!empty($admin_id)) {
            $query = "INSERT INTO admin_user (admin_id,user_id) VALUES ('".$admin_id."','".$userId."')";
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
       
    // }
    return $userCreateArray;

}

function checkAccountNameInZoho($companydata) {
    extract($companydata);
    $companyInfoArray              = [];
   
    $companyInfo                   = searchUserInZoho('Accounts/search', 'criteria=(Account_Name:equals:'.urlencode($companydata['company_name']).')');

    if(isset($companyInfo->data)) {
            $companyInfo->account_found_flag = 1;
    } else {
        $companyInfo                        = (object)$companyInfoArray ;
        $companyInfo->account_found_flag    = 0;
    }

    return $companyInfo;
}

function checkEmailInZohoSameCompany($userdata) {
    extract($userdata);
    $userInfoArray              = [];
   
    $userInfo                   = searchUserInZoho('Contacts/search', 'criteria=(((Email:equals:'.urlencode($userdata['email']).')or(Secondary_Email:equals:'.urlencode($userdata['email']).'))and(Account_Name:equals:'.urlencode($userdata['company_name']).'))');

    if(isset($userInfo->data)) {
        if(isset($userdata['type']) && $userdata['type'] == 'edit') {
            if($userInfo->data[0]->ADS_User_Role == 'Admin' && ((empty($userInfo->data[0]->Account_Name)))) {
                $userInfo->email_found_flag = 1;
            }
        } else {
            $userInfo->email_found_flag = 1;
        }
    } else {
        $userInfo                   = (object)$userInfoArray ;
        $userInfo->email_found_flag = 0;
    }
    return $userInfo;
}

function checkEmailInZohoDifferentCompany($userdata) {
    extract($userdata);
    $userInfoArray              = [];
   
    $userInfo                   = searchUserInZoho('Contacts/search', 'criteria=((Email:equals:'.urlencode($userdata['email']).')or(Secondary_Email:equals:'.urlencode($userdata['email']).'))');

    if(isset($userInfo->data)) {
        if(isset($userInfo->data[0]->Account_Name)) {
                if($userInfo->data[0]->Account_Name->name != $company_name) {
                    $userInfo->email_found_flag = 1;
                }else{
                    $userInfo->email_found_flag = 0;
                }
        }else{
            if(empty($userInfo->data[0]->Account_Name) ){
                $userInfo->email_found_flag = 0;
            }
            
        }
    } 
    else {
        $userInfo                   = (object)$userInfoArray ;
        $userInfo->email_found_flag = 0;
    }
    return $userInfo;
}

function checkContactNameInSameCompanyInAds($params) {
    extract($params);
    $userContact['contact_found']       = 0;

    $params['check_contact']            = 1;
    // $result                             = get_query_result('__query_get_contact_name_same_company', $params, 'FETCH_OBJ');
    $result                             = get_query_result('__query_get_user_of_company', $params, 'FETCH_OBJ');
    

    $userContact['contact_found_active_check']      = 0;
    $userContact['contact_found_inactive_check']    = 0;
   
    foreach($result as $key => $value) {
        if((strtolower(trim($value->first_name)) == strtolower(trim($first_name))) && (strtolower(trim($value->last_name)) == strtolower(trim($last_name))) ) {
            $userContact['contact_found']                   = $userContact['contact_found'] + 1;
            if($value->status == 'active') {
                $userContact['contact_found_active_check']   =  1;
                // $userContact['zoho_contact_id']              = $value->zoho_contact_id;
                $userContact['zoho_contact_id']              = $value->zoho_contact_id;
                $userContact['user_id']                      = $value->user_id;
                $userContact['id']                           = $value->zoho_contact_id;
                $userContact['ADS_Record_ID']                = $value->user_id;
                $userContact['ADS_Username']                 = $value->email;
                $userContact['role']                         = $value->role;
                $userContact['username']                = $value->email;
                break;
            } else {
                $userContact['zoho_contact_id']              = $value->zoho_contact_id;
                $userContact['contact_found_inactive_check'] =  1;
                $userContact['user_id']                     = $value->user_id;
                $userContact['id']                          = $value->zoho_contact_id;
                $userContact['ADS_Record_ID']               = $value->user_id;
                $userContact['ADS_Username']                = $value->email;
                $userContact['role']                         = $value->role;
                $userContact['username']                    = $value->email;
                break;
            }
            
        }
    }
    return $userContact;
}

function checkContactNameInSameCompanyInZoho($userdata) {
    extract($userdata);
    $userInfoArray              = [];

    if($status == 'Inactive'){
        $userInfo                   = searchUserInZoho('Contacts/search', 'criteria=((First_Name:equals:'.urlencode(trim($first_name)).')and(Last_Name:equals:'.urlencode($last_name).')and(Account_Name:equals:'.urlencode(trim($company_name)).')and((ADS_User_Status:equals:'.$status.')or(ADS_User_Status:equals:Deleted)))');
  
    } else{
        $userInfo                   = searchUserInZoho('Contacts/search', 'criteria=((First_Name:equals:'.urlencode(trim($first_name)).')and(Last_Name:equals:'.urlencode($last_name).')and(Account_Name:equals:'.urlencode(trim($company_name)).')and(ADS_User_Status:equals:'.$status.'))');
  
    }
   
    
    if(isset($userInfo->data)) {
        $userInfo->contact_found_flag   = 1;
    }else {
        $userInfo                       = (object)$userInfoArray ;
        $userInfo->contact_found_flag   = 0;
    }
    
    if($userInfo->contact_found_flag == 1) {
        foreach($userInfo->data as $key => $value) {
            $userInfo->contact_found_active     = 0;
            $userInfo->contact_found_inactive   = 0;
            if($value->ADS_User_Status == 'Active') {
                $userInfo->contact_found_active = 1;
                break;
            } else if($value->ADS_User_Status == 'Inactive'  || $value->ADS_User_Status == 'Deleted' ) {
                $userInfo->contact_found_inactive = 1;
                break;
            }
        }
    }

    return $userInfo;
}

function checkContactNameEditInSameCompanyInZoho($userdata) {
    extract($userdata);
    $userInfoArray              = [];
    $userInfo                   = searchUserInZoho('Contacts/search', 'criteria=((First_Name:equals:'.urlencode(trim($first_name)).')and(Last_Name:equals:'.urlencode(trim($last_name)).')and(Account_Name:equals:'.urlencode($company_name).'))');
  
    if(isset($userInfo->data)) {
        $userInfo->contact_found_flag   = 1;
        $userInfo->data[0]->username    = $userInfo->data[0]->ADS_Username;
    }else {
        $userInfo                       = (object)$userInfoArray ;
        $userInfo->contact_found_flag   = 0;
    }
    
   
    return $userInfo;
}


function checkDomainOnDifferentAccountADS($params) {
    extract($params);
    $domain['domain_found_flag']    = 0;
    $admin_domain                   = explode('@', $admin_email);
    $result                         = get_query_result('__query_get_domain_override', $params, 'FETCH_OBJ');

    foreach($result as $key => $value) {
        if(($value->domain == $admin_domain[1]) && ($value->company_name != $company_name) && ($value->company_name != '')) {
            $domain['company_id']           = $value->company_id;
            $domain['account_name']         = $value->company_name;
            $domain['zoho_user_id']         = $value->zoho_contact_id;
            $domain['user_id']              = $value->user_id;
            $domain['domain_found_flag']    = 1;
            break;
        }
    }
    return $domain;
}

function checkDomainOnDifferentAccountADSForSaveCompany($params) {
    extract($params);
    $domain['domain_found_flag']    = 0;
    $admin_domain                   = explode('@', $admin_email);
    $result                         = get_query_result('__query_get_domain_override', $params, 'FETCH_OBJ');

    foreach($result as $key => $value) {
        if($value->domain == $admin_domain[1] && $value->company_name != '') {
            $domain['company_id']           = $value->company_id;
            $domain['account_name']         = $value->company_name;
            $domain['zoho_user_id']         = $value->zoho_contact_id;
            $domain['user_id']              = $value->user_id;
            $domain['domain_found_flag']    = 1;
            break;
        }
    }
    return $domain;
}

function checkDomainOnDifferentAccountZOHO($params) {
    extract($params);
    $userInfoArray              = [];
    $userInfo                   = searchUserInZoho('Contacts/search', 'word='.$params['admin_domain'][1]);
//   show($userInfo->data[0]->ADS_User_Role , 1 );
    // $userInfo->zoho_domain_found_flag = 0;
    if(isset($userInfo->data)) {
        if(isset($userInfo->data[0]->Account_Name)) {
            if($userInfo->data[0]->Account_Name->name != $company_name) {
                $userInfo->zoho_domain_found_flag = 1;
            }else{
                $userInfo->zoho_domain_found_flag = 0;
            }
        }else{
            if(empty($userInfo->data[0]->Account_Name) ){
                $userInfo->zoho_domain_found_flag = 0;
            }
            
        }
    }else {
        $userInfo                   = (object)$userInfoArray ;
        $userInfo->zoho_domain_found_flag = 0;
    }
    
    return $userInfo;
}

function checkContactNameInDifferentCompanyInZoho($params) {
    extract($params);
    $userInfoArray              = [];
    $userInfo                   = searchUserInZoho('Contacts/search', 'criteria=((First_Name:equals:'.urlencode($first_name).')and(Last_Name:equals:'.urlencode($last_name).'))');
  
    if(isset($userInfo->data)) {
        if(isset($userInfo->data[0]->Account_Name)) {
            if(strtolower($userInfo->data[0]->Account_Name->name) == strtolower($company_name) && strtolower($userInfo->data[0]->Email) != strtolower($admin_email)) {
                $userInfo->contact_found_flag = 1;
            }else{
                $userInfo->contact_found_flag = 0;
            }
        }else{
            if(empty($userInfo->data[0]->Account_Name) ){
                $userInfo->contact_found_flag = 0;
            }
        }
    // $userInfo->contact_found_flag   = 1;
    }else {
        $userInfo                       = (object)$userInfoArray ;
        $userInfo->contact_found_flag   = 0;
    }
    
    if($userInfo->contact_found_flag == 1) {
        foreach($userInfo->data as $key => $value) {
            $userInfo->contact_found_active     = 0;
            $userInfo->contact_found_inactive   = 0;
            if($value->ADS_User_Status == 'Active') {
                $userInfo->contact_found_active = 1;
                break;
            } else if($value->ADS_User_Status == 'Inactive' ) {
                $userInfo->contact_found_inactive = 1;
                break;
            }
        }
    }
    return $userInfo;
}

function createCompanyData($user) {
    $company_name                   = isset($user->company_name) ? $user->company_name : '';
    $type                           = isset($user->company_type) ? $user->company_type : '';
    $company_size                   = isset($user->company_size) ? $user->company_size : '';
    $revenue                        = isset($user->revenue) ? $user->revenue : ''; 
    $account_owner                  = isset($user->account_owner) ? $user->account_owner : ''; 
    $account_owner_zoho_id          = isset($user->account_owner_zoho_id) ? $user->account_owner_zoho_id : ''; 
    $domain_override                = isset($user->domain_override) ? $user->domain_override : '0'; 
    $download_limit                 = $user->download_limit;
   

    $company_details['company_name']                = $company_name;
    $company_details['company_type']                = $type;
    $company_details['company_size']                = $company_size;
    $company_details['company_revenue']             = $revenue;
    $company_details['account_owner']               = $account_owner;
    $company_details['download_limit']              = $download_limit;

    if(isset($user->price)){
        $company_details['users_limit']                 = $user->price[0]->users_limit;
    }
    
    $company_details['account_owner_zoho_id']       = $account_owner_zoho_id;
    $company_details['ads_record_id']               = isset($user->company_id) ? $user->company_id : '';
    $company_details['status']                      = 'Active';
    $company_details['domain_override']             = $domain_override;
    $company_details['client']                      = ($user->client == 'Yes') ? true : false;

    return $company_details;
}

function createUserData($company_details , $user) {
    
    $domain_override                 = isset($user->domain_override) && ($user->domain_override != '') ? $user->domain_override : '0'; 
    $country_code                     = $user->country_code;
    $position                         = isset($user->position) ? $user->position : '';
    $account_owner_zoho_id            = isset($user->account_owner_zoho_id) ? $user->account_owner_zoho_id : ''; 
    if(isset($user->price)) {
        foreach($user->price as $k => $v){
            $company_details['users_limit'] = $v->users_limit;
       }
       $user_details['users_limit']      =  $company_details['users_limit'];
    }

    $user_details['company_name']     = $company_details['company_name'];
    $user_details['first_name']       = $user->first_name;
    $user_details['last_name']        = $user->last_name;
    $user_details['username']         = $user->username;
    $user_details['mobile']           =  $user->mobile;
    $user_details['country_code']     = $country_code;
    $user_details['client']           = $company_details['client'] == true ? 'Yes' : 'No';
    $user_details['position']         = isset($company_details['position']) ? $company_details['position'] : $position;
    $user_details['company_type']     = $company_details['company_type'];
    $user_details['contact_owner']    = $account_owner_zoho_id;
    $user_details['account_owner']    = isset($user->account_owner) ?$user->account_owner :  $company_details['account_owner'];;
    $user_details['assistant_admin']  = isset($user->assistant_admin) ? $user->assistant_admin : 0 ;
    $user_details['role']             = isset($user->role) ? $user->role : 'admin'; 
    $user_details['status']           = 'active';
    $user_details['domain_override']  = $domain_override;
    $user_details['timeout']          = isset($user->timeout) ? $user->timeout : '';
    $user_details['no_of_apps']       = isset($user->no_of_apps) ? $user->no_of_apps : '';
    $user_details['authy_id']         = isset($user->authy_id) ? $user->authy_id : '';
    $user_details['passphrase']       = isset($user->passphrase) ? $user->passphrase : '';
    $user_details['access_type']      = isset($user->access_type) ? $user->access_type : '';
    $user_details['monthly_cap']      = isset($user->monthly_cap) ? $user->monthly_cap : '';
    $user_details['update_authy']     = isset($user->update_authy) ?$user->update_authy : '' ;

    if(isset($user->user_id) ) {
        $user_details['ads_record_id']    = $user->user_id;
        // $user_info                        = getUserInfoById($user->user_id);
    //  $user_details['zoho_contact_id']  = isset($userInfo) ? $user_info[0]->zoho_contact_id: $user->zoho_user_id;
        $user_details['zoho_contact_id']   = isset($user->zoho_user_id) ? $user->zoho_user_id : $user->zoho_contact_id;
    } else {
        $user_details['zoho_contact_id']   = isset($user->zoho_user_id) ? $user->zoho_user_id : '';
    }
    return $user_details;
}

function saveCompanyCall($user) {
    $db                 = getConnection();
    $companyCreateArray = [];
    
    $company_details    = createCompanyData($user);
    $save_sql= "INSERT INTO company (company_name,company_type,company_size,video_download_limit,revenue,created_date,modified_date) VALUES ('".addslashes($company_details['company_name'])."','".$company_details['company_type']."','".$company_details['company_size']."','".$company_details['download_limit']."','".$company_details['company_revenue']."','".date("Y-m-d h:i:s")."','".date("Y-m-d h:i:s")."')"; 
    $stmt    = $db->prepare($save_sql);
    $stmt->execute();
    $companyId  = $db->lastInsertId();
    $companyCreateArray['companyId']   = $companyId;
    return $companyCreateArray;

}

function updateCompanyCall($user) {
    $db                 = getConnection();
    $companyCreateArray = [];
    $company_details    = createCompanyData($user);

    if(isset($user->edit_comp) && ($user->edit_comp == 1)) {
        $company_id = $user->company_id;
    }else {
        if(!isset($_SESSION['userInfo'])) {
            $company_id = $user->company_id;
        } else {
            $company_id = $_SESSION['userInfo']['company_id'];
        }
    }
    

    $sql = "UPDATE company SET company_name='".addslashes($company_details['company_name'])."',company_type='".$company_details['company_type']."',company_size='".$company_details['company_size']."',revenue='".$company_details['company_revenue']."', video_download_limit='".$company_details['download_limit']."',modified_date='".date("Y-m-d h:i:s")."' WHERE id = '".$company_id."'";
    $stmt    = $db->prepare($sql);
     $stmt->execute();
}

function companyAdminCheck($user, $companyInfo) {
    $company['company_name']  = $user->company_name;
    $company['admin_email']   = $user->username;

    if(($user->zoho_contact_id == $companyInfo->data[0]->ADS_Admin->id ) || ( $companyInfo->data[0]->ADS_Admin == '' )) {
        return 0; // same admin or empty admin
    }else {
        return 1;
    }
    
}

function get_calendar_details($date) {
    $sql = "SELECT * FROM media_calendar WHERE '$date' >= media_week_start AND '$date' <= media_week_end";
    $result = execute_query_get_result($sql);
    
    return $result[0];
}

function updateDownloadsCountInZoho($user_id) {
    $excelCounts = getExcelDownloadCountFromUser($user_id);

    $params['user_id']              = $user_id;
    $params['zoho_contact_id']      = $excelCounts['zoho_contact_id'];
    $params['excel_lifetime_count'] = $excelCounts['excel_lifetime_count'] + 1 ;
    $params['excel_30days_count']   = $excelCounts['excel_30days_count'] + 1;

    updateUserExcelDownloadsCounts($params);
    APIManageZOHOContact('excelUpdate',$params);
}

function updateUserExcelDownloadsCounts($params) {
    $db             = getConnection();
    extract($params);

    $sql            = "UPDATE user SET excel_lifetime_count='".$excel_lifetime_count."',excel_30days_count = '".$excel_30days_count."' WHERE user_id = '".$user_id."'"; 
    $stmt           = $db->prepare($sql);
    $stmt->execute();
}

function getExcelDownloadCountFromUser($user_id) {
    $db         = getConnection();
    $sql        = "SELECT excel_lifetime_count, excel_30days_count,zoho_contact_id FROM user WHERE user_id = ".$user_id;
    $result     = execute_query_get_result($sql);
    return $result[0];
}

function getExcelExport($id) {
    $sql            = "SELECT * FROM `excel_exports` WHERE  id =  $id";
    $excel_results = execute_query_get_result($sql, 'FETCH_OBJ');
    return $excel_results;
}

function getMyFilters($id) {
    $sql            = "SELECT * FROM `user_filters` WHERE  id =  $id";
    $filter_results = execute_query_get_result($sql, 'FETCH_OBJ');
    return $filter_results;
}

function getNumbersForAlphabets($refineBySearchText) {
    // $letters = array('ABC'=> 2, 'DEF' => 3, 'GHI' => 4, 'JKL' => 5, 'MNO' => 6, 'PQRS' => 7, 'TUV' => 8, 'WXYZ' => 9)
    $ReplacementPattern = array(
        'a' => '2',
        'b' => '2',
        'c' => '2',
        'd' => '3',
        'e' => '3',
        'f' => '3',
        'g' => '4',
        'h' => '4',
        'i' => '4',
        'j' => '5',
        'k' => '5',
        'l' => '5',
        'm' => '6',
        'n' => '6',
        'o' => '6',
        'p' => '7',
        'q' => '7',
        'r' => '7',
        's' => '7',
        't' => '8',
        'u' => '8',
        'v' => '8',
        'w' => '9',
        'x' => '9',
        'y' => '9',
        'z' => '9',
        '+' => '00',
        ' ' => '',
    );
    return str_ireplace(array_keys($ReplacementPattern), array_values($ReplacementPattern), $refineBySearchText);

}

function str_replace_first($search, $replace, $subject) {
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function getColNameForRefineFeature($refine_filter_opt_text) {
    $get_first_digits = substr($refine_filter_opt_text,0,1);
    $get_second_digits = substr($refine_filter_opt_text,1,1);
    $position_of_dash  = strpos($refine_filter_opt_text, '-');
    if($position_of_dash == 1 && $get_first_digits == 1) {  //1-800  // 1-80021380
        $whatIWant = substr($refine_filter_opt_text, strrpos($refine_filter_opt_text, "-") + 1);
        $lastDigitsLength   =  strlen($whatIWant); // get length of last digits
        if($lastDigitsLength <= 3) { //1-800
            $GLOBALS['add_underscore_operator'] = 1;
            $return['colName']                  = 'tfn';
            $return['refine_filter_opt_text']   = str_replace_first("1","",$refine_filter_opt_text);
        } else { // 1-80021380
            $return['colName']                  = 'tfn_num';
            $return['refine_filter_opt_text']    = str_replace_first("1", "", $refine_filter_opt_text) ;
            $return['refine_filter_opt_text']   = str_replace("-","",$return['refine_filter_opt_text']);
        }
    } else if ($position_of_dash > 1 && $get_first_digits == 1) { // 1-800-
        $return['colName']                  = 'tfn';
        $position_of_first_digit            = strpos($refine_filter_opt_text, '1');
        $return['refine_filter_opt_text']   = str_replace_first("1","",$refine_filter_opt_text);
        if($position_of_first_digit == 0 && $position_of_dash == 3 ) { // 111-
            $return['refine_filter_opt_text']   = $refine_filter_opt_text;
        }
    } else if($position_of_dash === 0  && $get_first_digits != 1) { //-800
        $GLOBALS['add_underscore_operator'] = 1;
        $return['colName']                  = 'tfn';
        $return['refine_filter_opt_text']   = $refine_filter_opt_text;
    } else if ($position_of_dash > 1 && $get_first_digits != 1) { //-800-8
        if($position_of_dash >= 4) { // 8888-
            $return['colName']                  = 'tfn_num';
            $return['refine_filter_opt_text']    = str_replace("-", "", $refine_filter_opt_text) ;
        } else {
            $return['colName']                  = 'tfn';
            $return['refine_filter_opt_text']   = $refine_filter_opt_text;
        }
    } else if($get_first_digits == 1){ 
        if($get_second_digits != 1) {// 180021380
            $return['refine_filter_opt_text']    = str_replace_first("1", "", $refine_filter_opt_text) ;
        }   else {
            $return['refine_filter_opt_text']    = ($refine_filter_opt_text) ; //111
        } 
        $return['colName']                  = 'tfn_num';
    } else{
        if($position_of_dash === 0) {
            $return['colName']                  = 'tfn';
        } else {
            $return['colName']                  = 'tfn_num';
        }
        $return['refine_filter_opt_text']    =  $refine_filter_opt_text ;
    }
    return $return;
}

function getRefineTextWithStringFilters($refine_filter_opt_text, $replaced) {
    $GLOBALS['add_underscore_operator'] = 0;
    $refine_filter_array['colName'] = 'tfn_num';
    $whatIWant  = $refine_filter_opt_text;
    if(preg_match_all('/-/',$refine_filter_opt_text,$matches)) {
        $whatIWant = substr($refine_filter_opt_text, strrpos($refine_filter_opt_text, "-") + 1); // get number after last hiphen i.e 800-456-4566 will retrieve 4566
    }
    $lastDigitsLength   =  strlen($whatIWant); // get length of last digits
    $get_first_digits   = substr($refine_filter_opt_text,0,1);
    if($lastDigitsLength != 4 && strlen($refine_filter_opt_text) < 10) {
        $return             = getColNameForRefineFeature($refine_filter_opt_text,$get_first_digits);
        $refine_filter_opt_text = $return['refine_filter_opt_text'];
        $refine_filter_array['colName']  = $return['colName'];
    } else if(strlen($refine_filter_opt_text) >= 10) {
        $return['refine_filter_opt_text'] = str_replace("-","",$refine_filter_opt_text);
    }
    if($replaced){
        if(preg_match_all('/1-/',$refine_filter_opt_text,$matches)) {
            $search  = array('1-', '-');
            $replace = array('');
            $replacedDashNumber = str_replace($search, $replace, $refine_filter_opt_text);
        } else {
            $replacedDashNumber = str_replace("-","",$refine_filter_opt_text);
        }
        $refine_filter_array['colName'] = 'tfn_num';
        $refine_filter_array['refine_filter_opt_text'] = $refine_filter_opt_text = $replacedDashNumber;
    }
   
    if($lastDigitsLength == 4) {
        if(preg_match_all('/1-/',$refine_filter_opt_text,$matches)) {
            $refine_filter_opt_text = ltrim($refine_filter_opt_text, "1-");
            if(strlen($refine_filter_opt_text) == 4) {
                $get_first_three_digits = substr($refine_filter_opt_text,0,3);
                $get_last_digit = substr($refine_filter_opt_text,3);
                $refine_filter_opt_text = $get_first_three_digits .'-'.$get_last_digit;
            }
            $refine_filter_array['colName'] = 'tfn';
            $refine_filter_array['refine_filter_opt_text'] = substr($refine_filter_opt_text, 0, 13);
        } else {
            if(preg_match_all('/-/', $refine_filter_opt_text,$matches)) {
                $refine_filter_array['colName'] = 'tfn';
            }
            if(strlen($refine_filter_opt_text) >= 10) {
                $refine_filter_opt_text = str_replace("-","",$refine_filter_opt_text);
                $refine_filter_array['colName'] = 'tfn_num';
            } 
            $refine_filter_array['refine_filter_opt_text'] = substr($refine_filter_opt_text, 0, 10);
        }
    } else {
        if(preg_match_all('/1-/',$refine_filter_opt_text,$matches)) {
            if(strrpos($refine_filter_opt_text, "1-") == 1) {
                $refine_filter_opt_text = ltrim($refine_filter_opt_text, "1-");
            }
        } else if(preg_match_all('/1/',$refine_filter_opt_text,$matches) && strlen($refine_filter_opt_text) >= 10 ) {
            $refine_filter_opt_text = ltrim($refine_filter_opt_text, "1");
        } 
        $refine_filter_array['refine_filter_opt_text'] = substr($refine_filter_opt_text, 0, 10);
       
    }
    return $refine_filter_array;
}

function getUseIndex($refine_filter_opt) {
    $use_index = $refine_filter_opt == '800' ? 'use index(ci_tfn_num)' : 'use index(ci_tfn_num)';

    return $use_index;
}

function retreiveBrandCreativeName($name_array) {
    $msg = '';
    if(!empty($name_array)) {
        $msg = 'Brand: '.$name_array['brand_name'].'&nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp;Creative: '.$name_array['creative_name'];
    }
    return $msg;
}

function getExcelInformation($excel_id) {
    $db         = getConnection();
    $sql        = "SELECT * FROM excel_exports WHERE id = ".$excel_id;
    $result     = execute_query_get_result($sql);
    return $result[0];
}

function getNoRecordIfNetworkConditions($query_params) {
    extract($query_params);
    $sql = "SELECT airing_id FROM `airings` as a  WHERE   a.`creative_id` = '$creative_id'  $where  AND a.network_id = $network_id   AND a.start_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 9 DAY) and CURDATE()
    UNION ALL
    SELECT airing_id  FROM `airings` as a WHERE   a.`creative_id` = '$creative_id'  $where  AND a.start_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 9 DAY) and CURDATE() ORDER BY `airing_id` DESC limit 1;";
    $network_results = execute_query_get_result($sql, 'FETCH_OBJ');
    return $network_results;
}

function getUrlFilters($refine_filter_opt_text) {
    $refine_filter_opt_text = strtolower($refine_filter_opt_text);
    if(substr($refine_filter_opt_text, 0, strlen('www.')) == $refine_filter_opt_text ) {
        $refine_filter['refine_filter_opt_text'] = $refine_filter_opt_text;
        $refine_filter['where']    = 'AND d.url LIKE "%' . $refine_filter_opt_text . '%"';
    }  else {
        if(has_prefix($refine_filter_opt_text, 'www.')) {
            $refine_filter['refine_filter_opt_text'] = str_replace("www.","",$refine_filter_opt_text);
            $refine_filter['where']    = 'AND d.url LIKE "' . $refine_filter['refine_filter_opt_text'] . '%"';
        } else {
            $refine_filter['where']    = 'AND d.url LIKE "%' . $refine_filter_opt_text . '%"';
        }
    }
    return $refine_filter;
}

//You can check if your string starts with www or any prefix(http) using the small function below.
function has_prefix($string, $prefix) {
    return substr($string, 0, strlen($prefix)) == $prefix;
 }

 function getProgramsByNetwork($params)
{
    $sql = __query_get_programs_by_network_id($params);
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $resultset = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $resultset;
}

function getProgramParams($params) {
    extract($params);
    $return_arr['table'] = '';
    $return_arr['table_join'] = '';
    $return_arr['join_condition'] = '';
    $return_arr['where_program'] = '';
    $return_arr['table_join_on'] = '';

    if(!empty($program_ids)){
        $return_arr['table']          = ' , program_master p ';
        $return_arr['table_join']     = ' AND if(d.program = "", "Program unknown", d.program_id) = p.program_id';
        $return_arr['table_join_on']  = '  ON  if(d.program = "", "Program unknown", d.program_id) = p.program_id';
        $return_arr['join_condition'] = ' JOIN program_master p ';
        $return_arr['where_program'] = ' AND p.program_id IN ('.$program_ids.')';
    }
    return $return_arr;
}

 //find position for any character in parameter
 function findPositionOfChar($search_string, $search) {
    if( strpos($search, $search_string) !== false ) {
       return 1;
    }
    return 0;
 }

 function deleteRecordFromExcel($id) {
    $db = getConnection();
    $sql = "UPDATE  excel_exports SET status = 'deleted' WHERE id =".$id;
    $stmt = $db->prepare($sql);
    $stmt->execute();
 }

 function swap(&$a, &$b){
    list($a, $b) = [$b, $a];
}

function readMoreHelper($story_desc, $chars = 35) {
	// strip tags to avoid breaking any html
    $string = strip_tags($story_desc);
    if (strlen($string) > $chars) {

        // truncate string
        $stringCut = substr($string, 0, $chars);
        $endPoint = strrpos($stringCut, ' ');

        //if the string doesn't contain any space then it will cut without word basis.
        $string = $endPoint? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
        $string .= '...';
    }
    return $string;
}

function getMyList($id) {
    $sql            = "SELECT * FROM `users_list` WHERE  id =  $id";
    $list_results   = execute_query_get_result($sql, 'FETCH_OBJ');
    return $list_results;
}

function getDayOfWeek($start_date) {
     $unixTimestamp = strtotime($start_date);
     $dayOfWeek = date("l", $unixTimestamp);
     return $dayOfWeek;
}
