<?php
$filename = '/vendors/autoload.php';
$paths = [__DIR__.$filename, __DIR__.$filename];
foreach($paths as $path){
    if (file_exists($path))
        require $path;
}
class Authy_Api extends Authy\AuthyApi {}
$prod = true;
$apiKey = '957f5333b6e8917bff6ea03a40cbbbb5';//'geq138kHsklhS0x1hhUgEKKnsNhB5hSL';
$apiUrl = ($prod == true) ? 'http://api.authy.com' : 'http://sandbox-api.authy.com';

$api = new Authy_Api($apiKey, $apiUrl);
$authy_user_id = $_REQUEST['authy_user_id'];

if (empty($authy_user_id)) {
    echo 'There seems to be some problem with the user registration with Authy.';
    return;
}
$getSMS = $api->requestSms($authy_user_id,array("force" => "true"));
if(empty($getSMS->body->success)){
	// debugging authy error
	$debug_file_path = DEBUG_FILE_PATH . DIRECTORY_SEPARATOR . gmdate('Y-m-d') . '.txt';
    $fp = fopen($debug_file_path, 'a+');
    $message = "Authy issue" . "\t" . var_dump($getSMS) . "\t" . clearSpaces("From Login Interface > Sending Authy SMS") . "\t" . clearSpaces('NA' . "\t" . gmdate('H:i:s'));
    fwrite($fp, print_r($message . PHP_EOL, 1));
    fclose($fp);


	echo  $getSMS->body->message;
}else{
	echo 1;
}
?>
