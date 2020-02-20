<?php
$filename = '/vendors/autoload.php';
$paths = [__DIR__.$filename, __DIR__.'/../../..'.$filename];
foreach($paths as $path){
    if (file_exists($path))
        require $path;
}

class Authy_Api extends Authy\AuthyApi {}
$prod = true;
$apiKey = '957f5333b6e8917bff6ea03a40cbbbb5';//'geq138kHsklhS0x1hhUgEKKnsNhB5hSL';//'TQ760vY3SR9U9DQwLsQw1blSDTr3VxR1';
$apiUrl = ($prod == true) ? 'http://api.authy.com' : 'http://sandbox-api.authy.com';

$api = new Authy_Api($apiKey, $apiUrl);
$getElem = explode("_",$_REQUEST['token']);
$token = $getElem[1];
$id = $getElem[0];

$verify = $api->verifyToken($id,$token);

/*
$getRowCookie = $verify->raw_response->headers['set-cookie'][0];
$getCookie = explode(';',$getRowCookie);
$finalCookieValue = explode('=',$getCookie[0]);
*/
if(empty($verify->body->success)){
 echo  0;
}else{
 // echo  $finalCookieValue[1];
 echo  md5($token."A.UT*HY/SALL-T".$id);

}
//return $verify;
