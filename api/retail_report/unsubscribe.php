<?php

require_once dirname(__FILE__) . '/../config.php';

if(!empty($_REQUEST['param'])){
    $userEmail = base64_decode($_REQUEST['param']);
    $response  = print_r($_SERVER,true);
    $time      = gmdate('Y-m-d H:i:s');

    $db        = getConnection();
    $insertSql = "INSERT INTO weekly_email_unsubscribe (email, unsubscribe_time, response) VALUES ('".addslashes($userEmail)."','".$time."', '".$response."')";
    $stmt      = $db->prepare($insertSql);

    if($stmt->execute()){
        $sql    = "UPDATE weekly_email_user SET subscription_status = '0' WHERE email = '".$userEmail."'";
        $stmt   = $db->prepare($sql);
        if($stmt->execute()){
             echo '<link href="/drmetrix/assets/css/style.css" rel="stylesheet" type="text/css">
                <div class="grid-content video_background " style="overflow: hidden; display: flex; align-items: center; justify-content: center; height: 100vh;">
                    <div class="img-pos" style="position: absolute;top: 1em;left: 1em;width:auto;">
                        <img media="large" src="/drmetrix/assets/img/video_logo.svg">
                    </div>
                    <div class="grid-block align-center" id="class_msg" style="color: #000; background: #fff; padding-top: 6px; padding-bottom: 2px; font-size: 18px; padding: 20px; border: 1px solid #fff; text-align: center;">You have been unsubscribed.  <br/>Thank you.</div>
                    
                </div>';
        }else{
            echo "Error while unsubscribe.";
        }    
    }
    closeConnection();
}

    
