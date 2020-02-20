<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/queries.php';
/*define('ROWNUM', 20);
define('MODE', 'NATURAL');
define('OFFSET', '- 3 hours'); 
define("LENGTH",300);
define('STRING_LENGTH', 23);
define('MAX_NO_OF_RECORDS_FOR_EXCEL', 30000);
define('LARGE_EXCEL_DOWNLOAD_PATH', 'excels/');
define('EXCEL_VIDEO_EXPIRY_DAYS', 10);
define('EXCEL_FILE_EXPIRY_DAYS', 30);
define('EXCEL_FILE_VALID_TILL', 29);
define('TIME_REQUIRED_FOR_ONE_RECORD', 6/1000);
define('NO_OF_FILE_DOWNLOAD_TASKS', 2);
define('THRESHOLD_FOR_PHPEXCEL', 30000);
define('OWNERSHIP', 'www-data:www-data');
define('LIFETIME_START_DATE', '2014-12-29');
define('AIRINGS_TABLE', 'airings_detail');
define('CACHE_SQL_QUERY', ' SQL_NO_CACHE ');
define('CATEGORY_UNCHECKED_LIMIT', 10);
define('SUMMARY_AIRINGS', 'airings_master');
define("FROMDATE", '');
define("TODATE", '');
define("MONTHFROMDATE", '');
define("MONTHTODATE", '');
define("NULL_START_YEAR", 'AND start_year IS NOT NULL');
define("NULL_START_HOUR", 'AND start_hour IS NOT NULL');
define("NULL_START_WEEK", 'AND start_week IS NOT NULL');
define("NULL_START_WEEKDAY", 'AND start_weekday IS NOT NULL');*/
define("AIRINGS_NULL_START_YEAR", 'AND d.start_year IS NOT NULL');
define("AIRINGS_NULL_START_HOUR", 'AND d.start_hour IS NOT NULL');
define("AIRINGS_NULL_START_WEEK", 'AND d.start_week IS NOT NULL');
define("AIRINGS_NULL_START_WEEKDAY", 'AND d.start_weekday IS NOT NULL');
define("NULL_ALL_CONDITION", AIRINGS_NULL_START_YEAR.' '.AIRINGS_NULL_START_HOUR.' '.AIRINGS_NULL_START_WEEK.' '.AIRINGS_NULL_START_WEEKDAY);

require_once(dirname(__FILE__).'/PHPExcel_1.8.0_doc/Classes/PHPExcel.php');
define("IMAGE", dirname(__FILE__) . '/../'.'assets/img/logo-medium.png');

$prev_date = date('Y-m-d', strtotime(date('Y-m-d') .' -1 day'));
define("PREVIOUSDATE", " AND start >= '".LIFETIME_START_DATE."' ".NULL_START_WEEK." ".NULL_START_YEAR );

$generic_dayparts = array(1 => 'Early Morning', 2 => 'Daytime' , 3 => 'Early Fringe' , 4 => 'Primetime' ,5 => 'Late Fringe' , 6 => 'Overnight');
define('GENERTIC_DAYPARTS' ,serialize ( $generic_dayparts));
define('AIRINGS_RATE_COLUMN' ,'d.'.RATE_COLUMN);

define('SPEND_COLUMN', '
SUM(IF(d.breaktype = "N", '.AIRINGS_RATE_COLUMN.', 0))  as national_spend,
SUM(IF(d.breaktype = "L", '.AIRINGS_RATE_COLUMN.', 0))   as local_spend, SUM(d.'.RATE_COLUMN.')  as total_spend');


define('SPEND_COLUMN1', '
SUM(IF(d.breaktype = "N", '.AIRINGS_RATE_COLUMN.', 0))   as nat_spend,
SUM(IF(d.breaktype = "L", '.AIRINGS_RATE_COLUMN.', 0))   as loc_spend, SUM(d.'.RATE_COLUMN.') as total_spend');

define('TOTAL_SPEND', 'SUM('.AIRINGS_RATE_COLUMN.') as total_spend');

//define('DEFAULT_CLASSIFICATION', 10);

$creative_videos_folder = dirname(__FILE__) . '/../creative_videos/';
$creative_thumbnails_folder = dirname(__FILE__) . '/../creative_thumbnails/';
define('CREATIVE_VIDEOS_FOLDER', $creative_videos_folder);
define('DEFAULT_RESPONSE_TYPE', '( response_url=1  or  response_sms=1  or  response_tfn=1  or  response_mar=1 )');
define('CREATIVE_THUMBNAILS_FOLDER', $creative_thumbnails_folder);

$server_name =  $_SERVER['SERVER_NAME'] ;
// $base_folder        = '/www/html/'; 
// if($server_name == 'localhost') {
//     $base_folder = "";
// } 

if( $server_name == 'dev.drmetrix.com' ) {
    $environment_id = 'DEV';
} else if($server_name == 'qa.drmetrix.com') {
    $environment_id = 'QA';
} else if($server_name == 'beta.drmetrix.com') {
    $environment_id = 'BETA';
} else if( $server_name == 'adsphere.drmetrix.com'){
    $environment_id = 'PROD';
} else {
    $environment_id = 'localhost';
}

define("ENVIRONMENT_ID", $environment_id);
// define("ENVIRONMENT_FOLDER", $environment_folder);
// define("BASE_FOLDER", $base_folder);
//define('VIDEO_DOWNLOAD_DOMAIN', 'http://videos.drmetrix.com/');
//define('VIDEO_DOWNLOAD_DOMAIN', '');
//define('VIDEO_FOLDER','creative_videos');
