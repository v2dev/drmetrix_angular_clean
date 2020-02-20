<?php

/**
 * Author : Ashwini Shinde
 * Date: 13-10-2015
 * Purpose: Send weekly report of short forms for Brands.
 */

class Slim_App_Crons_MonthlySpendIndex {
    private $db = NULL;
 
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
       
        $cronJob_month = new Slim_App_Crons_HighestProjectedScoreMonth();
        
        $this->monthly = $cronJob_month->getLastMediaMonth(); 
    }
   
    public function retrieveMonthlyDataBrands($type){
        $brand_adv_flag = 1;
      
        if($type == 1){
            $where = 'a.length <= 120';
        }else{
            $where = 'a.length >120';
        }
       $sql = "SELECT b.brand_id,SUM(rate) projected_score FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$this->monthly['sd']."  00:00:00' AND '".$this->monthly['ed']." 00:00:00' AND ".$where." GROUP BY b.brand_id ";
     
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($result)){
                $this->saveSpendIndexMonthly($result,$brand_adv_flag,$type); //save spend index and other details.
                $this->updateRankingBrandShortForm($brand_adv_flag,$type);  //update monthly ranking for short form and long form brands
            }else{
                 echo '<br><br>No short form brands found between '.$this->monthly['sd'].' and '.$this->monthly['ed'];
            }
        }  
    }
    
    public function retrieveMonthlyDataAdv($type){
        $brand_adv_flag = 2;
      
        if($type == 1){
            $where = 'a.length <= 120';
        }else{
            $where = 'a.length >120';
        }
       $sql = "SELECT b.brand_id,SUM(rate) projected_score FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$this->monthly['sd']."  00:00:00' AND '".$this->monthly['ed']." 00:00:00' AND ".$where." GROUP BY b.adv_id ";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($result)){
                $this->saveSpendIndexMonthly($result,$brand_adv_flag,$type); //save spend index and other details.
                $this->updateRankingBrandShortForm($brand_adv_flag,$type);  //update monthly ranking for short form and long form brands
            }else{
                 echo '<br><br>No short form adv found between '.$this->monthly['sd'].' and '.$this->monthly['ed'];
            }
        }  
    }
    
    public function saveSpendIndexMonthly($data,$brand_adv_flag,$type){
        $highestProjectedScoreMonthly = $this->getHighestProjectedScoreMonthly($brand_adv_flag,$type);
        
        foreach($data as $key => $value){
              $value['brand_adv_flag']= $brand_adv_flag;
              $value['type']= $type;
              $spendIndex = $this->calculateSpendIndex($highestProjectedScoreMonthly,$value);
              $value['spend_index'] = $spendIndex;
              $this->addToMonthlyTable($value);
        }
        echo '<br><br>Successfully saved '.count($data).'monthly records into monthly_spend_index_ranking table.';
    }
    
     public function addToMonthlyTable($data){
        $sql = "INSERT INTO monthly_spend_index_ranking (brand_adv_id,brand_adv_flag,price,spend_index,type,from_date,to_date) VALUES ('".$data['brand_id']."','".$data['brand_adv_flag']."','".$data['projected_score']."','".$data['spend_index']."','".$data['type']."','".$this->monthly['sd']."','".$this->monthly['ed']."')";
       $stmt = $this->db->prepare($sql);
       $stmt->execute();
    }
    
     public function updateRankingBrandShortForm($brand_adv_flag,$type){
        $sql = "SELECT * FROM monthly_spend_index_ranking WHERE from_date = '".$this->monthly['sd']."' AND to_date = '".$this->monthly['ed']."' AND type='".$type."' AND  brand_adv_flag = '".$brand_adv_flag."' ORDER BY  price DESC";
        $stmt = $this->db->prepare($sql);
       if ($stmt->execute()) {
            $rankingBrands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach($rankingBrands as $key => $value){
            if($value['spend_index']!=0){
                $updateRanking = "UPDATE monthly_spend_index_ranking SET ranking = '".($key+1)."' WHERE  brand_adv_id = '".$value['brand_adv_id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
            }else{
                $updateRanking = "UPDATE monthly_spend_index_ranking SET ranking = '-' WHERE  brand_adv_id = '".$value['brand_adv_id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
            }
        }

        echo '<br><br>Ranking updated for  '.count($rankingBrands) . ' Records';
    }
    
     public function getHighestProjectedScoreMonthly($brand_adv_flag,$type){
         if($brand_adv_flag == 1){
             $groupBy = 'b.brand_id';
         }else{
             $groupBy = 'b.adv_id';
         }
         
         if($type == 1){
             $where = "a.length <= 120";
         }else{
             $where = "a.length > 120";
         }
        $sql = "SELECT b.brand_id as ID,SUM(rate) projected_score  FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$this->monthly['sd']."  00:00:00' AND '".$this->monthly['ed']." 00:00:00' AND ".$where." GROUP BY ".$groupBy." ORDER BY projected_score DESC LIMIT 1";
      
        $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $highestprojected_score_month = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $highestprojected_score_month;
    }
    
    public function calculateSpendIndex($highestScore,$value){
        $spendIndex =  round((($value['projected_score'] * 100)/$highestScore[0]['projected_score']),2);
        return $spendIndex;
    }
    
   
}
