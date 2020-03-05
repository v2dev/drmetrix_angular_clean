<?php
global $dbh;
// ini_set("display_errors", "1");
// error_reporting(E_ALL);

define("DEBUG", FALSE);
define("DEBUG_FILE_PATH", 'E:\xampp7.3\htdocs\drmetrix_log\\');
define("SESSION_DIR", 'E:\xampp7.3\htdocs\drmetrix_session\\');
date_default_timezone_set('US/Eastern');
ini_set('max_execution_time', 2000);
define("HOST", $_SERVER['HTTP_HOST']);
define("HOST_RETAIL_REPORT", $_SERVER['HTTP_HOST'].'/drmetrix');


//--Mailchimp Automation setting
//define("API_KEY", '99b697583b64da82c26f901d71543f2b-us13');//'99b697583b64da82c26f901d71543f2b-us13'); //99b697583b64da82c26f901d71543f2b-us13
//"API_KEY_LIVE" => '0aaf9373b3f8bd7fdd3e64687b3ed67c-us13';
//define("ZOHO_APIKEY", '156a8024c09e2b3481cdfa1fce5f924a'); //'2af6b5f76605e95016ded9a9770eb082' //'156a8024c09e2b3481cdfa1fce5f924a'
//define("FROM_EMAIL", 'gaurav.sharma@v2solutions.com');
//define("FROM_NAME", 'DRMetrix');

//define("LIST_DRM",'DRMetrix Report');
//define("LIST_LT", 'LeisureTime Report');
//define("LIST_TD", 'Team Direct Report');

//define('ACCOUNT_ADD_URL','https://crm.zoho.com/crm/private/xml/Accounts/insertRecords?');
//define('ACCOUNT_UPDATE_URL','https://crm.zoho.com/crm/private/xml/Accounts/updateRecords?');

//define('CONTACT_ADD_URL','https://crm.zoho.com/crm/private/xml/Contacts/insertRecords?');
//define('CONTACT_UPDATE_URL','https://crm.zoho.com/crm/private/xml/Contacts/updateRecords?');

//define("USER_COMPANY",'Network');
$GLOBALS['db_failed'] = 0;
function getConnection() {
    global $dbh;
    try
    {
            if(0){
            $dbhost="192.168.40.15";
            $dbuser="root";
            $dbpass="root123";
            }else if(1){                            // localhost
                $dbhost="207.32.16.241";
                $dbuser="v2_dev";
                $dbpass="ZXd&Ad9D";
                // $dbname="drmetrix_production"; //drmetrix_old
                $dbname="drmetrix_staging"; //drmetrix_old
                
                /*
                $dbhost="207.32.16.242";
                $dbuser="drm_user";
                $dbpass="drmetrix";
                $dbname="drmetrix_dev"; //drmetrix_old
                */
            }else{                                          // drm
                $dbhost = "localhost";
                $dbuser = "ssuser";
                $dbpass = "ss@123";
            }
            if(empty($dbh)){
                $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8;", $dbuser, $dbpass);
                $dbh->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
                $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
    }
    catch(Exception $e)
    {
        $GLOBALS['db_failed'] = 1;
        define('MAINTENANCE_PAGE', 1);
        if(strstr($_SERVER['REQUEST_URI'] , 'maintenance') != 'maintenance.php') {
            header('Location: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/drmetrix/maintenance.php');
            exit;
        }
      
        // echo $e->getMessage();
    }

       

        /*
        $stmt = $dbh->prepare("SELECT CONNECTION_ID()");
        $stmt->execute();
        $connection_id  = $stmt->fetchColumn();

        global $mysql_connection_array;
        $mysql_connection_array[] = $connection_id;
        
        if (!defined('MYSQL_CONNECTION_ID')) {
            define("MYSQL_CONNECTION_ID", $connection_id);
        }

        $debug_file_path = DEBUG_FILE_PATH . DIRECTORY_SEPARATOR . gmdate('Y-m-d') . '.txt';
        $fp = fopen($debug_file_path, 'a+');
        $message = 'mysql - ' . $connection_id . ' php ' . getmypid();
        fwrite($fp, print_r($message . PHP_EOL, 1));
        fclose($fp);
        */
        return $dbh;
}

function closeConnection(){
    global $dbh;
    $dhb = null;
}

function errorLog($exception) {
    $debug_file_path = DEBUG_FILE_PATH . DIRECTORY_SEPARATOR . gmdate('Y-m-d') . '.txt';
    $fp = fopen($debug_file_path, 'a+');
    $message = clearSpaces($_SERVER['GATEWAY_INTERFACE']) . "\t" . clearSpaces($_SERVER['REQUEST_URI']) . "\t" . clearSpaces(serialize($_REQUEST)) . "\t" . clearSpaces($exception->getMessage()) . "\t" . clearSpaces($exception->getFile()) . "\t" . clearSpaces($exception->getLine() . "\t" . gmdate('H:i:s'));
    fwrite($fp, print_r($message . PHP_EOL, 1));
    fclose($fp);

    header('HTTP/1.1 500 Internal Server Error');
    exit;
}

function clearSpaces($str) {
    return str_replace(array("\r","\n"),"",$str);
}

defineConstants();
// define_cached_results();
// exit;

function show($var, $exit = 0) {
    $debug_file_path = 'E:\xampp7.3\htdocs\drmetrix_log\show.txt';
    $fp = fopen($debug_file_path, 'a+');
    fwrite($fp, print_r($var, 1) . PHP_EOL);
    fclose($fp);

    if ($exit == 1) exit;
}


function defineConstants(){
    if (!defined('SUMMARY_AIRINGS')) {
        $db = getConnection();
        if($GLOBALS['db_failed'] == 1) {
            return;
        }
        $sql = "SELECT `name`,`value` FROM `configs` UNION SELECT QUERY, result from cached_queries" ;
        $sql = "SELECT `name`,`value` FROM `configs`" ;
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $key => $value){
             define($value['name'], $value['value']);
        }
    }
}

function define_cached_results() {
    if (!defined('SELECTFROMcategoriesgroupbycategoryid')) {
        $db = getConnection();
        $sql = "SELECT * FROM cached_queries";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $key => $value){
            if (!defined($value['query'])) {
                define($value['query'], $value['result']);
            }
        }
    }
}
/*define("DOCROOT", $_SERVER['DOCUMENT_ROOT']);
define("HOST", $_SERVER['HTTP_HOST']);*/
if(!defined("CONFIG_MAKE_ZOHO_API_CALLS")){
    define("CONFIG_MAKE_ZOHO_API_CALLS", 1);
}

define("DRMDEV", 'dev.drmetrix.com');
define("DRMQA", 'qa.drmetrix.com');
define("DRMSTAGING", 'staging.drmetrix.com');
define("DRMBETA", 'beta.drmetrix.com');
define("ADSPHERE", 'adsphere.drmetrix.com');
// define('BETA_VERSION', 3);