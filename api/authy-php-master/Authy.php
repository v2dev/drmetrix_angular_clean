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
$userEmail =  $_REQUEST['email'];
$userPhone = $_REQUEST['mobile'];
$userCountryCode = '+'.$_REQUEST['country_code'];//+91|+1

$user = $api->registerUser($userEmail, $userPhone, $userCountryCode);

if ($user->ok()) {
    //$sendSMS = $api->requestSms($user->id(),array("force" => "true"));
    echo 'Authy ID for user "'.$userEmail.'": '.$user->id()."\n";
    return $user->id();

} else {	
    foreach ($user->errors() as $field => $error){
        echo 'Error on '.$field.': '.$error;
    }
    return 0;
}


?>
