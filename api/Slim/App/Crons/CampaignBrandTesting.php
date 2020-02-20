<?php

/**
 * Author : Ashwini Shinde
 * Date: 16-12-2015
 * Purpose: Get all brands and form an array for first detection and first dectection plus six days. Get all airings for respected brand. Compare start and first detection seventh day, If start is greater means it is not present in rolling window. then update status to testing.
 */
set_time_limit(0);
ini_set('max_execution_time', 0);

class Slim_App_Crons_CampaignBrandTesting {
    private $db = NULL;
    
    
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
    }
    
    public function allBrands(){
        $brand_array = array();
        $airings_array = array();
        $sql = "SELECT brand_id,first_detection FROM brand WHERE first_detection!=''";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
         
        if(!empty($brands)){
            foreach($brands as $bKey => $bValue){
                $firstDetection = strtotime($bValue['first_detection']);
                $firstDetectionSeventh = strtotime("+6 day", $firstDetection);
                $firstDetectionSeventh = date('Y-m-d h:i:s',$firstDetectionSeventh);
                $bValue['first_dection_seventh'] = $firstDetectionSeventh;
              
                array_push($brand_array,$bValue['brand_id']);
            }
            
            if(!empty($brand_array)){
                $brand_ids = implode(",",$brand_array);
                $sql = "SELECT a.start,a.airing_id,c.creative_id,c.brand_id FROM airings a INNER JOIN creative c ON  a.creative_id = c.creative_id WHERE c.brand_id IN (".$brand_ids.")";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $airings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach($airings as $airings_key => $airing_value){
                    $airing_timestamp = strtotime($airing_value['start']);
                    $first_detection_timestamp = strtotime($bValue['first_dection_seventh']);
                    
                    if($airing_timestamp > $first_detection_timestamp) { 
                        array_push($airings_array,$airing_value['airing_id']);
                    }
                }
               
                if(!empty($airings_array)){
                      //update status of airing to testing.
                      $this->updateStatus($airings_array);   
                }
            }
        }  
        echo count($airings_array). 'airings updated.';
     } 
    
    function updateStatus($airings_array){
        $date = date("Y-m-d");
        $airings_id = implode(",",$airings_array);
        $sql = "UPDATE airings set status='testing',status_date='".$date."' WHERE airing_id IN (".$airings_id.")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
?>