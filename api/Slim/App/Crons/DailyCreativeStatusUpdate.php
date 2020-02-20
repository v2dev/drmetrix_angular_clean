<?php

/**
 * Author : Ashwini Shinde
 * Date: 15-01-2015
 * Modified date : 01-10-2016
 * Purpose: Change the status of brand/creative/advertiser to active if it is aired in last 30 days else change to inactive status.
 * Modified Purpose : Change the status of creative/brand/advertiser to active or inactive according to short form or long form classification.
 */
set_time_limit(0);
ini_set('max_execution_time', 0);

class Slim_App_Crons_DailyCreativeStatusUpdate {
    private $db = NULL;
    
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
      
        $this->date = $this->lastThirtyDays();
        $this->getCreatives();
        $this->updateBrands('short');
        $this->updateBrands('long');
        $this->updateAdvs('short');
        $this->updateAdvs('long');
    }
    
    public function lastThirtyDays(){ 
        return  date('Y-m-d', strtotime('-30 days'));
    }
    
    public function getCreatives(){
        $creative_array = array();
        $creative_status_id = array();
        $airings = array();
        $creatives_id_update = '';
        $todaysDate = date('Y-m-d h:i:s');
        $sql = "SELECT creative_id FROM creative WHERE last_aired <='".$todaysDate."' AND last_aired >'".$this->date."'";
       // $sql = "SELECT creative_id FROM creative WHERE   last_aired BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
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
           $stmt = $this->db->prepare($update_sql);
           $stmt->execute();
           
           $update_sql = "UPDATE creative SET is_active = 0 WHERE creative_id NOT  IN (".$creative_ids.")";
           $stmt = $this->db->prepare($update_sql);
           $stmt->execute();
        }
        
        echo 'Total creative updated are '.count($creative_array);
    }
    
   
     public function updateBrands($length){
        $this->setAllInactive($length);
        
        $operator = $length == 'short' ? '<=' : '>';
        
        $sql = "SELECT brand_id FROM `creative` where length ". $operator.LENGTH." GROUP by brand_id HAVING (SUM(is_active) >= 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if(!empty($brands)){
            $this->updateBrandsResult($brands, $length);
            echo '</br>Total '.$length.' brands updated are'.count($brands);
        }
    }
    
    public function updateAdvs($length){
        $colname = $length.'_active';
                
        $sql = "SELECT a.adv_id FROM `advertiser` a INNER JOIN brand b ON b.adv_id = a.adv_id GROUP by a.adv_id HAVING (SUM(b.".$colname.") >= 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $advertiser = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if(!empty($advertiser)){
                $this->updateAdvertiserResult($advertiser, $length);
                echo '</br>Total '.$length.' advertiser updated are'.count($advertiser);
        }
    }
   
    public function setAllInactive($length){
        $colname = $length.'_active';
                
        $update_brand = 'UPDATE brand SET '.$colname.' = 0 ';
        $stmt = $this->db->prepare($update_brand);
        $stmt->execute();
        
        $update_adv = 'UPDATE advertiser SET '.$colname.' = 0 ';
        $stmt = $this->db->prepare($update_adv);
        $stmt->execute();
    }
    
    
    public function updateBrandsResult($brands, $length){
        $brandIds = implode(",",$brands);
        $brandIds = rtrim($brandIds);
        
        $colname = $length.'_active';
        
        $update_brand = 'UPDATE brand SET '.$colname.' = 1 WHERE brand_id IN ('.$brandIds.')';
        $stmt = $this->db->prepare($update_brand);
        $stmt->execute();
    }
    
    
     public function updateAdvertiserResult($advertiser, $length){
        $advIds = implode(",",$advertiser);
        $advIds = rtrim($advIds);
       
        $colname = $length.'_active';
        
        $update_adv = 'UPDATE advertiser SET '.$colname.' = 1 WHERE adv_id IN ('.$advIds.')';
        $stmt = $this->db->prepare($update_adv);
        $stmt->execute();
    }
}