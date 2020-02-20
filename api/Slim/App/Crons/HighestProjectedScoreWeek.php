<?php

/**
 * Author : Ashwini Shinde
 * Date: 6-10-2015
 * Purpose: Get highest projected score for last week (Monday to Sunday) of brand as well as advertisement.
 */

class Slim_App_Crons_HighestProjectedScoreWeek {
    private $db = NULL;
 
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
        $this->this_week = $this->getLastMediaWeek();
    }

    public function getLastMediaWeek(){
        if((FROMDATE == '') && (TODATE == '')){
            $monday = strtotime("Monday last week");
            $monday = date('W', $monday)==date('W') ? $monday-7*86400 : $monday;
            $sunday = strtotime(date("Y-m-d",$monday)." +6 days");
            $this_week_sd = date("Y-m-d",$monday);
            $this_week_ed = date("Y-m-d",$sunday);
            $this_week['sd'] = $this_week_sd;
            $this_week['ed'] = $this_week_ed;
            
            $this_week['start_date'] =  date("M d, Y",$monday);
            $this_week['end_date'] =  date("M d, Y",$sunday);
        }else{
            $this_week['sd'] = FROMDATE;
            $this_week['ed'] = TODATE;
            
            $this_week['start_date'] =  'Oct 10, 2015';
            $this_week['end_date'] =  'Oct 17, 2015';
        }
        return $this_week;
    }
    
    public function getProjectedScoreForBrands(){
         // Short form result
        $sql = "SELECT b.brand_id as ID,count(a.creative_id) no_of_airings,SUM(rate) projected_score,c.length  FROM brand b INNER JOIN creative c ON b.brand_id = c.brand_id INNER JOIN airings a ON  a.creative_id = c.creative_id  WHERE c.length <= 120   AND  c.is_active = 1 AND a.end >= '".$this->this_week['sd']."  00:00:00' AND a.end <=  '".$this->this_week['ed']." 00:00:00' GROUP BY b.brand_id ORDER BY `projected_score` DESC ";
       
        $brandArray = array();
         $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $brands_projected_score_sf = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $brand_adv_flag = 1;
            if(!empty($brands_projected_score_sf)){
                    $highestScore = $brands_projected_score_sf[0]['projected_score']; // highest projected score for shot form brand
                    $this->saveSpendIndex($brands_projected_score_sf,$brand_adv_flag,$highestScore);
            }else{
                echo '<br><br>No brands found between '.$this->this_week['sd'].' and '.$this->this_week['ed'].' for short form';
            }
        }
        
         // Long form result
        $sql = "SELECT b.brand_id as ID,count(a.creative_id) no_of_airings,SUM(rate) projected_score,c.length  FROM brand b INNER JOIN creative c ON b.brand_id = c.brand_id INNER JOIN airings a ON  a.creative_id = c.creative_id  WHERE c.length > 120  AND  c.is_active = 1 AND a.end >= '".$this->this_week['sd']."  00:00:00' AND a.end <=  '".$this->this_week['ed']." 00:00:00' GROUP BY b.brand_id ORDER BY `projected_score` DESC";
       
         $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $brands_projected_score_lf = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $brand_adv_flag = 1;
            if(!empty($brands_projected_score_lf)){
                    $highestScore = $brands_projected_score_lf[0]['projected_score']; // highest projected score for long form brand
                    $this->saveSpendIndex($brands_projected_score_lf,$brand_adv_flag,$highestScore);
            }else{
                echo '<br><br>No brands found between '.$this->this_week['sd'].' and '.$this->this_week['ed'].' for long form';
            }
        }
    }
    
     public function getProjectedScoreForAdv(){
         // Short form result
        $sql = "SELECT b.adv_id as ID,count(a.creative_id) no_of_airings,SUM(rate) projected_score,c.length  FROM brand b INNER JOIN creative c ON b.brand_id = c.brand_id INNER JOIN  airings a ON a.creative_id = c.creative_id WHERE c.length <= 120  AND c.is_active = 1 AND a.end >= '".$this->this_week['sd']."  00:00:00' AND a.end <= '".$this->this_week['ed']." 00:00:00' AND b.adv_id IS NOT NULL AND b.adv_id != 0 GROUP BY b.adv_id ORDER BY `projected_score` DESC  ";
         $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $adv_projected_score_sf = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $brand_adv_flag = 2;
            if(!empty($adv_projected_score_sf)){
                  $highestScore = $adv_projected_score_sf[0]['projected_score']; // highest projected score for short form adv
                  $this->saveSpendIndex($adv_projected_score_sf,$brand_adv_flag,$highestScore);
            }else{
                echo '<br>No advertise found between '.$this->this_week['sd'].' and '.$this->this_week['ed'] .' for short form';
            }
        }
        
        // Long form result
        $sql = "SELECT b.adv_id as ID,count(a.creative_id) no_of_airings,SUM(rate) projected_score,c.length  FROM brand b INNER JOIN creative c ON b.brand_id = c.brand_id INNER JOIN airings a ON  a.creative_id = c.creative_id  WHERE  c.length > 120 AND  c.is_active = 1 AND a.end >= '".$this->this_week['sd']."  00:00:00' AND a.end <=  '".$this->this_week['ed']." 00:00:00' AND b.adv_id IS NOT NULL AND b.adv_id != 0 GROUP BY b.adv_id ORDER BY `projected_score` DESC";
       
         $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $adv_projected_score_lf = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $brand_adv_flag = 2;
            if(!empty($adv_projected_score_lf)){
                    $highestScore = $adv_projected_score_lf[0]['projected_score']; // highest projected score for long form brand
                    $this->saveSpendIndex($adv_projected_score_lf,$brand_adv_flag,$highestScore);
            }else{
                echo '<br><br>No advertise found between '.$this->this_week['sd'].' and '.$this->this_week['ed'].' for long form';
            }
        }
    }
    
    public function saveSpendIndex($brands_adv_projected_score,$brand_adv_flag,$highestScore){
        foreach($brands_adv_projected_score as $key => $value){
            $type = $value['length'] <= 120 ? 'sf' : 'lf';
            $spendIndex = $this->calculateSpendIndex($highestScore,$value);
            $value['spend_index'] = $spendIndex;

           $saveSpendIndexSql = "INSERT INTO brand_adv_spend_index (brand_adv_id,brand_adv_flag,no_of_airings,week_month,from_date,to_date,projected_score,spend_index,type,created_date) VALUES ('".$value['ID']."','".$brand_adv_flag."','".$value['no_of_airings']."','1','".$this->this_week['sd']."','".$this->this_week['ed']."','".$value['projected_score']."','".$value['spend_index']."','".$type."','".date('Y-m-d h:i:s')."')";
            $stmt = $this->db->prepare($saveSpendIndexSql);
            $stmt->execute();
        } 
        $category = $brand_adv_flag == 1 ? 'brands' : 'advertise';
        echo '<br><br>Stored spend index for '.count($brands_adv_projected_score) . ' '.$category ;
    }
    
    public function updateRanking($brand_adv_flag){
         $category = $brand_adv_flag == 1 ? 'brands' : 'advertise';
         
        //short form update ranking
         $sql = "SELECT * FROM brand_adv_spend_index WHERE from_date = '".$this->this_week['sd']."' AND to_date = '".$this->this_week['ed']."' AND  brand_adv_flag = '".$brand_adv_flag."' AND week_month = 1 AND type = 'sf' ORDER BY  projected_score DESC";
        $stmt = $this->db->prepare($sql);
       if ($stmt->execute()) {
            $rankingBrands = $stmt->fetchAll(PDO::FETCH_ASSOC);
       }
       
        foreach($rankingBrands as $key => $value){
            if($value['projected_score']!=0){
               $updateRanking = "UPDATE brand_adv_spend_index SET ranking = '".($key+1)."' WHERE id = '".$value['id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
            }else{
                $updateRanking = "UPDATE brand_adv_spend_index SET ranking = '0' WHERE  id = '".$value['id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
            }
        }
        
       
        echo '<br><br>Ranking updated for  '.count($rankingBrands) . ' records of '.$category;
        
        //long form update ranking
        $sql = "SELECT * FROM brand_adv_spend_index WHERE from_date = '".$this->this_week['sd']."' AND to_date = '".$this->this_week['ed']."' AND  brand_adv_flag = '".$brand_adv_flag."' AND week_month = 1 AND type = 'lf' ORDER BY  projected_score DESC";
        $stmt = $this->db->prepare($sql);
       if ($stmt->execute()) {
            $rankingBrands = $stmt->fetchAll(PDO::FETCH_ASSOC);
         }

       foreach($rankingBrands as $key => $value){
            if($value['projected_score']!=0){
                 $updateRanking = "UPDATE brand_adv_spend_index SET ranking = '".($key+1)."' WHERE id = '".$value['id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
            }else{
                $updateRanking = "UPDATE brand_adv_spend_index SET ranking = '0' WHERE  id = '".$value['id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
            }
        }
        
        echo '<br><br>Ranking updated for  '.count($rankingBrands) . ' records of '.$category;
    }
    
    //function to update national and local
    public function updateNationalLocal($brand_adv_flag){
        $brand_adv_array = array();
        $id = $brand_adv_flag == 1 ? 'b.brand_id' : 'b.adv_id';
        $fun = $brand_adv_flag == 1 ? 'totalAiringsBrands' : 'totalAiringsAdvertise';
        $where = $brand_adv_flag == 1 ? '' : ' AND b.adv_id IS NOT NULL ';
        $percent = 0;
        $sql = "SELECT ".$id." as ID,count(a.creative_id) no_of_airings  FROM brand b INNER JOIN  creative c ON  b.brand_id = c.brand_id INNER JOIN airings a ON a.creative_id = c.creative_id WHERE a.end >= '".$this->this_week['sd']."  00:00:00' AND  c.is_active = 1 AND  a.end <= '".$this->this_week['ed']." 00:00:00' AND a.breakType = 'N' AND a.length <= 120 ".$where." GROUP BY ".$id;
      
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
           
        foreach($results as $key => $value){
            array_push($brand_adv_array, $value['ID']);
        }

        if(!empty($brand_adv_array)){
            $ids = implode(",", $brand_adv_array);
            if($brand_adv_flag == 1){
                 $sql = "SELECT b.brand_id as ID,count(a.creative_id) no_of_airings FROM brand b INNER JOIN creative c ON  b.brand_id = c.brand_id INNER JOIN airings a ON  a.creative_id = c.creative_id  AND  b.brand_id IN (".$ids.")  AND  c.is_active = 1 AND a.end >= '".$this->this_week['sd']." 00:00:00' AND '".$this->this_week['ed']." 00:00:00' GROUP BY b.brand_id";
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute()) {
                   $totalAirings = $stmt->fetchAll(PDO::FETCH_ASSOC);

                }
            }else{
                 $sql = "SELECT b.brand_id as ID,count(a.creative_id) no_of_airings FROM brand b INNER JOIN creative c ON  b.brand_id = c.brand_id INNER JOIN airings a ON  a.creative_id = c.creative_id  AND  b.adv_id IN (".$ids.")  AND   c.is_active = 1 AND a.end >= '".$this->this_week['sd']." 00:00:00' AND '".$this->this_week['ed']." 00:00:00' GROUP BY b.brand_id";
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute()) {
                   $totalAirings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }

        if(!empty($results) && !empty($totalAirings)){
            $i = 1;
            foreach($results as $k1 => $v1){
                foreach($totalAirings as $k2 => $v2){
                    if($v1['ID'] == $v2['ID']){
                        $percent = ($v1['no_of_airings']*100)/$v2['no_of_airings'];
                        $n = round($percent,2);
                        $l = round((100-$n),2);

                        $updateQuery = "UPDATE brand_adv_spend_index SET national_airings = '".$n."',local_airings = '".$l."' WHERE from_date = '".$this->this_week['sd']."' AND to_date = '".$this->this_week['ed']."' AND brand_adv_flag = '".$brand_adv_flag."' AND week_month = '1' AND brand_adv_id = '".$v1['ID']."'";
                        $stmt = $this->db->prepare($updateQuery);
                        $stmt->execute();
                        $i++;
                    }
                }
            }

        }  
        
        $category = $brand_adv_flag == 1 ? 'brands' : 'advertise';
        echo '<br><br>National airings updated for  '.($i-1) . ' records of '.$category;
    }

    
    public function calculateSpendIndex($highestScore,$brandAdvValue){
         $spendIndex = 0;
         if($highestScore!= 0){
            $spendIndex =  round((($brandAdvValue['projected_score'] * 100)/$highestScore),2);
         }
        return $spendIndex;
    }
}


