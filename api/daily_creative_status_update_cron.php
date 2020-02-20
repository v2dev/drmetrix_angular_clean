<?php
/**
 * Author : Ashwini Shinde
 * Date: 24-10-2016
 * Purpose: Change the status of brand/creative/advertiser to active if it is aired in last 30 days else change to inactive status.
 * Modified Purpose : Change the status of creative/brand/advertiser to active or inactive according to short form or long form classification.
 */
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';

ignore_user_abort();

creativeStatusUpdate();

function creativeStatusUpdate(){
    getCreatives();
    updateBrands('short');
    updateBrands('long');
    updateAdvs('short');
    updateAdvs('long');
    callSendMail();
}

function callSendMail(){
    $from = "info@drmetrix.com";
    $subject = "For Cron check Creative status active/inactive End on ".HOST." Server";
    $msg = "Process End at ".date('Y-m-d H:i:s');
    mail("pravin.sapkal@v2solutions.com", $subject, $msg, "From:" . $from);
    echo 'mail sent';
}

function lastThirtyDays(){ 
    return  date('Y-m-d', strtotime('-30 days'));
}

 function getCreatives(){
    $db = getConnection();
    $creative_array = array();
    $creative_status_id = array();
    $airings = array();
    $creatives_id_update = '';
    $todaysDate = date('Y-m-d h:i:s');
    $date =  lastThirtyDays();
   // $sql = "SELECT creative_id FROM creative WHERE last_aired <='".$todaysDate."' AND last_aired >'".$date."'";
    $sql = "SELECT creative_id FROM creative WHERE datediff(curdate(), last_aired) <= 30";
   // $sql = "SELECT creative_id FROM creative WHERE   last_aired BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()";
    $result = getResult($sql);
    
    if(!empty($result)){
        foreach($result as $k => $v){
            array_push($creative_array, $v['creative_id']);
        }
    }

    if(!empty($creative_array)){
        $creative_ids = implode(",",$creative_array);
    }


    if(!empty($creative_ids)){
       $update_sql = "UPDATE creative SET is_active = 1 "
               . "WHERE creative_id IN (".$creative_ids.")";
       $stmt = $db->prepare($update_sql);
       $stmt->execute();

       $update_sql = "UPDATE creative SET is_active = 0 WHERE creative_id NOT  IN (".$creative_ids.")";
       $stmt = $db->prepare($update_sql);
       $stmt->execute();
    }

    echo 'Total creative updated are '.count($creative_array);
}


  function updateBrands($length){
    $db = getConnection();
    $brands_array = array();
    setAllInactive($length);

    $operator = $length == 'short' ? '<=' : '>';

    $sql = "SELECT brand_id FROM `creative` where length ". $operator.LENGTH." GROUP by brand_id HAVING (SUM(is_active) >= 1)";
    $brands = getResult($sql);
    
    if(!empty($brands)){
        foreach($brands as $key => $value){
            array_push($brands_array, $value['brand_id']);
        }
        updateBrandsResult($brands_array, $length);
        echo '</br>Total '.$length.' brands updated are'.count($brands_array);
    }
}

 function updateAdvs($length){
    $db = getConnection();
    $adv_array = array();
    $colname = $length.'_active';

    $sql = "SELECT a.adv_id FROM `advertiser` a INNER JOIN brand b ON b.adv_id = a.adv_id GROUP by a.adv_id HAVING (SUM(b.".$colname.") >= 1)";
    $advertiser = getResult($sql);
   
    if(!empty($advertiser)){
        foreach($advertiser as $key => $value){
            array_push($adv_array, $value['adv_id']);
        }
        updateAdvertiserResult($adv_array, $length);
        echo '</br>Total '.$length.' advertiser updated are'.count($adv_array);
    }
}

 function setAllInactive($length){
    $db = getConnection();
    $colname = $length.'_active';

    $update_brand = 'UPDATE brand SET '.$colname.' = 0 ';
    $stmt = $db->prepare($update_brand);
    $stmt->execute();

    $update_adv = 'UPDATE advertiser SET '.$colname.' = 0 ';
    $stmt = $db->prepare($update_adv);
    $stmt->execute();
}


 function updateBrandsResult($brands, $length){
    $db = getConnection();
    $brandIds = implode(",",$brands);
    $brandIds = rtrim($brandIds);
    $colname = $length.'_active';

    $update_brand = 'UPDATE brand SET '.$colname.' = 1 WHERE brand_id IN ('.$brandIds.')';
    $stmt = $db->prepare($update_brand);
    $stmt->execute();
}


  function updateAdvertiserResult($advertiser, $length){
    $db = getConnection();
    $advIds = implode(",",$advertiser);
    $advIds = rtrim($advIds);

    $colname = $length.'_active';

    $update_adv = 'UPDATE advertiser SET '.$colname.' = 1 WHERE adv_id IN ('.$advIds.')';
    $stmt = $db->prepare($update_adv);
    $stmt->execute();
}
