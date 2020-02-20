<?php

/**
 * Author : Ashwini Shinde
 * Date: 16-12-2015
 * Purpose: brand will go into roll out when
1) 6th week spend index for a brand is greater than 1 st week spend index for a brand. if not then take 7th and 12th week and so on.
2) If first condition matches, if brand appeared in consecutive weeks then make it rollout
 */
set_time_limit(0);
ini_set('max_execution_time', 0);

class Slim_App_Crons_CampaignBrandRollout {
    private $db = NULL;
    
    
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
        $this->count = 0;
        $this->brands = array();
    }
    
    public function allBrands(){
        $sql = "SELECT brand_id,first_detection FROM brand WHERE first_detection!=''";
        $todaysDate = date("Y/m/d h:i:s");
        $stmt = $this->db->prepare($sql);
        // $k = 1;
        if ($stmt->execute()){
           $this->brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($this->brands as $key => $value){
                $diff = $this->difference($value['first_detection'],$todaysDate);
                
                //convert first_detection into y-m-d format
                $value['first_detection'] = strtotime($value['first_detection']);
                $value['first_detection'] = date('Y-m-d',$value['first_detection']);
                
                $first_sixth = strtotime("+6 day", strtotime($value['first_detection']));
                $first_sixth_date = date('Y-m-d',$first_sixth);
                $this->brands[$key]['difference'] = $diff;
               
                if($diff >= 6){
                    //form array for six weeks or next weeks for particular brand.
                   for($i=1;$i<=6;$i++){
                        if($i == 1){
                            $this->brands[$key]['week'][$i]['sd']= $value['first_detection'];
                            $this->brands[$key]['week'][$i]['ed']= $first_sixth_date;
                        }else{
                            $j = $i - 1;
                           
                            $this->brands[$key]['week'][$i]['sd'] = strtotime("+1 day", strtotime( $this->brands[$key]['week'][$j]['ed']));
                            $this->brands[$key]['week'][$i]['sd'] = date('Y-m-d', $this->brands[$key]['week'][$i]['sd']);
                            
                            $this->brands[$key]['week'][$i]['ed'] = strtotime("+6 days", strtotime( $this->brands[$key]['week'][$i]['sd']));
                            $this->brands[$key]['week'][$i]['ed'] = date('Y-m-d', $this->brands[$key]['week'][$i]['ed']);
                        }
                        
                        //find spend index for first and sixth, seventh and 12th and so on...
                        if($i == 1 || $i == 6){
                            $brand_id = $value['brand_id'];
                            $this->brands[$key]['week'][$i]['spend_index'] = $this->calculate_spend_index($this->brands[$key]['week'][$i]['sd'],$this->brands[$key]['week'][$i]['ed'],$brand_id);
                        }
                        
                        if($i == 6){
                            if($this->brands[$key]['week'][6]['spend_index'] >= $this->brands[$key]['week'][1]['spend_index']){
                                //check airings for first 6 weeks
                                $this->check_airing($brand_id,$this->brands);
                                
                                //check consecutive weeks
                                
                            }else{
                                //previously set $k =1 and Add 6 to it. continue same steps for further weeks. $k =6, $tillIncm = $k+5 till what??
                            }
                        }
                    }
                }
           }
           echo "Status updated for ".$this->count.' airings';
        } 
    }
    
    //find diffrence between first detection and todays date
    function difference($from,$to){
        $day   = 24 * 3600;
        $from  = strtotime($from);
        $to    = strtotime($to) + $day;
        $diff  = abs($to - $from);
        $weeks = floor($diff / $day / 7);
        $days  = $diff / $day - $weeks * 7;
        $out   = array();
        return $weeks;
    }
    
    function calculate_spend_index($sd,$ed,$brand_id){
        $spendIndex = 0;
        //get highest score for brands in particular weeek.
      $sql ="SELECT br.brand_id,SUM(ar.rate) as price  FROM brand br "
                . "INNER JOIN creative cr ON cr.brand_id = br.brand_id  "
                . "INNER JOIN airings ar ON ar.creative_id = cr.creative_id  WHERE ar.end BETWEEN  '".$sd." 00:00:00' AND '".$ed." 00:00:00' GROUP BY br.brand_id ORDER BY price DESC LIMIT 0,1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $highest_score = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
       
        //find spend index for particular week for a brand given
        $get_brand ="SELECT br.brand_id,SUM(ar.rate) as price  FROM brand br "
                . "INNER JOIN creative cr ON cr.brand_id = br.brand_id  "
                . "INNER JOIN airings ar ON ar.creative_id = cr.creative_id  WHERE ar.end BETWEEN  '".$sd." 00:00:00' AND '".$ed." 00:00:00' AND br.brand_id = '".$brand_id."' ";
        
        $stmt = $this->db->prepare($get_brand);
        $stmt->execute();
        $brand = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
        foreach($brand as $k => $v){
            if(!empty($highest_score) && $highest_score[0]['price']!=''){
                $spendIndex =  round((($v['price'] * 100)/$highest_score[0]['price']),2);
                $v['spend_index'] = $spendIndex;
            }else{
                $v['spend_index'] = 0;
            }
            
            return $v['spend_index'];
        }
        
    }
    
    function check_airing($brand_id,$brands){
        foreach($brands as $key => $value){
            if($value['brand_id'] == $brand_id){
                for($i=1; $i<=6; $i++){
                    $sd = $value['week'][$i]['sd'];
                    $ed = $value['week'][$i]['ed'];
                    
                    $sql = "SELECT ar.creative_id,ar.airing_id FROM brand br INNER JOIN creative cr ON br.brand_id = cr.brand_id INNER JOIN airings ar ON ar.creative_id = cr.creative_id WHERE br.brand_id = ".$brand_id." AND ar.end BETWEEN '".$sd." 00:00:00' AND '".$ed." 00:00:00' ";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $airings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if(!empty($airings)){
                        foreach($airings as $k => $v){
                           $aired = 'yes';
                            $this->brands[$key]['week'][$i]['aired'] = $aired;
                            
                            //check whether it is present in previous week
                            if($i != 1){
                               if (array_key_exists('aired', $this->brands[$key]['week'][$i-1])) {
                                    $this->count++;
                                    $this->updateStatus($v['airing_id']);
                                }
                            }
                        }
                    }
                    
                }
            }
        } 
    }
    
    function updateStatus($airing_id){
        $sql = "UPDATE airings set status='rollout',status_date='".date("Y-m-d")."' WHERE airing_id=".$airing_id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
?>