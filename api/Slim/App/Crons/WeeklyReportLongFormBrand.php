<?php

/**
 * Author : Ashwini Shinde
 * Date: 13-10-2015
 * Purpose: Send weekly report of short forms for Brands.
 */
require_once("dompdf/dompdf_config.inc.php");
require_once('PHPMailer/class.phpmailer.php');

class Slim_App_Crons_WeeklyReportLongFormBrand {
    private $db = NULL;
 
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
        
        $cronJob = new Slim_App_Crons_HighestProjectedScoreWeek();
       
        $this->last_week = $cronJob->getLastMediaWeek(); //current week considering last week
        $this->previous_week = $this->getPreviousMediaWeek(); // previous week of current week
        $this->highestProjectedScore = $this->getHighestProjectedScore();
        
        $cronJob_month = new Slim_App_Crons_HighestProjectedScoreMonth(); 
        $this->monthly = $cronJob_month->getLastMediaMonth(); 
        
         $this->mediaWeek = Slim_App_Lib_Common::weeks_in_month();
    }
    
    public function getPreviousMediaWeek(){
        $previous_week['sd'] = date('Y-m-d',(strtotime ( '-7 day' , strtotime ( $this->last_week['sd']) ) ));
        $previous_week['ed'] = date('Y-m-d',(strtotime ( '-7 day' , strtotime ( $this->last_week['ed']) ) ));
        return $previous_week;
    }
    
    public function getHighestProjectedScore(){
       $sql = "SELECT b.brand_id as ID,SUM(rate) projected_score  FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$this->last_week['sd']." 00:00:00' AND '".$this->last_week['ed']." 00:00:00' AND a.length > 120 GROUP BY b.brand_id ORDER BY projected_score DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $highestprojected_score = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $highestprojected_score;
    }
    
    public function retrieveData(){
          $sql = "SELECT b.brand_id as ID,SUM(rate) projected_score,a.length,count(a.creative_id) no_of_airings,b.brand_name,count(a.network_code) as network,sum(a.length) as duration FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$this->last_week['sd']." 00:00:00' AND '".$this->last_week['ed']." 00:00:00' AND a.length > 120 GROUP BY b.brand_id ";
        $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($result)){
                $this->saveSpendIndex($result); //save spend index and other details.
                $this->updateRanking();  //update current week ranking
                $this->updateAdvertiser(); //update advertiser name
            }else{
                 echo '<br><br>No short form brands found between '.$this->last_week['sd'].' and '.$this->last_week['ed'];
            }
        }  
    }
    
    public function retrievePreviousWeek(){
        $sql = "SELECT brand_id,current_week FROM report_brand_long_form WHERE from_date BETWEEN '".$this->previous_week['sd']."' AND '".$this->previous_week['ed']."'";
        $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->updatePreviousWeekRanking($result);
        }  
    }
    
     public function updatePreviousWeekRanking($result){
        if(!empty($result)){
             foreach($result as  $key=>$value){
                   $updatePreviousWeekRanking = "UPDATE report_brand_long_form SET previous_week = '".$value['current_week']."' WHERE  brand_id = '".$value['brand_id']."'";
                   $stmt = $this->db->prepare($updatePreviousWeekRanking);
                   $stmt->execute();
             }
        }else{
            $updatePreviousWeekRanking = "UPDATE report_brand_long_form SET previous_week = '-'";
            $stmt = $this->db->prepare($updatePreviousWeekRanking);
            $stmt->execute();
        }
        
        echo '<br><br>Previous week ranking updated';
    }
      
    public function retrieveMonthlyData(){
        $sql = "SELECT brand_adv_id,ranking FROM monthly_spend_index_ranking WHERE from_date BETWEEN '".$this->monthly['sd']."' AND '".$this->monthly['ed']."' AND brand_adv_flag = 1 AND type = 2";
        $stmt = $this->db->prepare($sql);
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->updateMonthlyRanking($result);
        }  
    }
    
    public function updateMonthlyRanking($result){
        if(!empty($result)){
             foreach($result as  $key=>$value){
                   $updateMonthlykRanking = "UPDATE report_brand_long_form SET monthly = '".$value['ranking']."' WHERE  brand_id = '".$value['brand_adv_id']."'";
                   $stmt = $this->db->prepare($updateMonthlykRanking);
                   $stmt->execute();
             }
        }else{
            $updateMonthlykRanking = "UPDATE report_brand_long_form SET monthly = '-'";
            $stmt = $this->db->prepare($updateMonthlykRanking);
            $stmt->execute();
        }
        
        echo '<br><br>Monthly ranking updated';
    }
   
    public function saveSpendIndex($data){
        foreach($data as $key => $value){
              $spendIndex = $this->calculateSpendIndex($this->highestProjectedScore,$value);
              $value['spend_index'] = $spendIndex;
              $this->addToTable($value);
        }
        echo '<br><br>Successfully saved '.count($data).' records';
    }
    
     public function calculateSpendIndex($highestScore,$value){
        $spendIndex = 0;
        if($highestScore[0]['projected_score']!=0){
            $spendIndex =  round((($value['projected_score'] * 100)/$highestScore[0]['projected_score']),2);
         }
        return $spendIndex;
    }
    
    public function addToTable($data){
        $video_id = Slim_App_Lib_Common::randomVideos();
        
        $asd = round($data['duration']/$data['no_of_airings']);
        $sql =  "INSERT INTO report_brand_long_form (brand_id,from_date,to_date,spend_index,total_airings,brand_name,price,network,asd,video_id) VALUES ('".$data['ID']."','".$this->last_week['sd']."','".$this->last_week['ed']."','".$data['spend_index']."','".$data['no_of_airings']."','".addslashes(trim($data['brand_name']))."','".$data['projected_score']."','".$data['network']."','".$asd."','".$video_id."')";
       $stmt = $this->db->prepare($sql);
       $stmt->execute();
    }
    
    public function updateRanking(){
        $sql = "SELECT * FROM report_brand_long_form WHERE from_date = '".$this->last_week['sd']."' AND to_date = '".$this->last_week['ed']."' ORDER BY  CAST(spend_index as DECIMAL(10,2)) DESC";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $rankingBrands = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        foreach($rankingBrands as $key => $value){
              if($value['spend_index']!=0){
                $updateRanking = "UPDATE report_brand_long_form SET current_week = '".($key+1)."' WHERE  brand_id = '".$value['brand_id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
              }else{
                $updateRanking = "UPDATE report_brand_long_form SET current_week = '-' WHERE  brand_id = '".$value['brand_id']."'";
                $stmt = $this->db->prepare($updateRanking);
                $stmt->execute();
            }
        }
        
        echo '<br><br>Ranking updated for  '.count($rankingBrands) . ' Records';
    }
    
    function updateAdvertiser(){
        $sql = "SELECT b.adv_id,b.brand_id,a.company_name FROM brand b "
                . "INNER JOIN advertiser a ON b.adv_id = a.adv_id "
                . "INNER JOIN report_brand_long_form r ON r.brand_id=b.brand_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($result)){
            foreach($result as $k=>$v){
              $updateAdvertiserQuery = "UPDATE report_brand_long_form SET adv_id='".$v['adv_id']."', advertiser_name = '".addslashes(trim($v['company_name']))."' WHERE  brand_id = '".$v['brand_id']."'";
               $stmt = $this->db->prepare($updateAdvertiserQuery);
               $stmt->execute();
            }
        }

        echo '<br><br>Advertiser updated for '. count($result) . ' Records';
    }
    
    function renderPDFAndEmail(){
        $result = $this->getLongBrands();
        $html   = $this->createHtmlTemplate($result);
        $this->convertToPdf($html);
        $this->sendWeeklyReport();
    }
    
    function getLongBrands(){
        $report = array();
        $sql = "SELECT * FROM report_brand_long_form WHERE from_date = '".$this->last_week['sd']."' AND to_date = '".$this->last_week['ed']."' ORDER BY  CAST(spend_index as DECIMAL(10,2)) DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($result)){
            foreach($result as $key => $value){
                $report[$key]['current_week']  =  $value['current_week'];
                $report[$key]['previous_week'] =  $value['previous_week'];
                $report[$key]['monthly']       =  $value['monthly'];
                $report[$key]['brand_name']    =  $value['brand_name'];
                $report[$key]['company']       =  !empty($value['advertiser_name']) ? $value['advertiser_name'] : '-';
                $report[$key]['price']         =  $value['price'];
                $report[$key]['airings']       =  $value['total_airings'];
                $report[$key]['spend_index']   =  $value['spend_index'];
                $report[$key]['asd']           =  $value['asd'];
                $report[$key]['network']       =  $value['network'];
                $report[$key]['brand_id']      =  $value['brand_id'];
                $report[$key]['video_id']      =  $value['video_id'];
                $report[$key]['adv_id']        =  $value['adv_id'];
            }
        }
        return $report;
    }
    
    function createHtmlTemplate($result){
        $date = strtotime($this->monthly['sd']);
        $last_month =  date('M ', $date);
        $html = '<style>
            .main-report {font-family: Tahoma, Arial, sans-serif;}
            .report-head h5 {color: #ffffff;text-align: center;padding: 5px;margin: 0;font-weight: normal;background-color: #202b39;border:none;}
            .week-img {padding: 8px;height: 65px;background :#f5f5f7;position: relative;}
            .logo-img {position: absolute;}
            .center {text-align: center;padding-top: 19px;}
            .center span {font-size: 10px;}
            .center img {width: 16px;vertical-align: bottom;padding-right: 10px;}
            .main-report .main-navigation th {color: #222;}
            .main-report table tbody tr:nth-child(odd){background-color: #f5f5f7;}
            .table-info, td {border: none;border-collapse: collapse;}
            .table-info thead th {font-weight: normal;border-right: 0.01em solid #ccc;}
            .table-info thead th:last-child {border-right: none !important;}
            th, td {padding: 5px;text-align: center;font-size: 7px;}
            a {color: #2fcaff;text-decoration: none;}
            .table-info td:last-child img {width: 16px;}
            .table-info {width: 100%;margin-top: 12px;}
            .table-info tbody {border-top: 5px solid #fff;}
            .left-text {text-align: left;}
            footer p {font-size: 0.42em;margin-top: 22px;padding: 10px 20px;background: #f5f5f7}
            </style>
            <div class="main-report">
				<div class="report-head">
					<h5>Weekly Retail Ranking Infomercial Report</h5>
                                        <h5>Long Form - Brand</h5>
				</div>
                                <div style="display:none"><img src="http://'.HOST.'/drmetrix/assets/img/one.png" style="display:none"></div>
				<div class="week-img">
					<div class="logo-img">
					<img src="http://'.HOST.'/drmetrix/assets/img/adsSphere.png" width="120px">
                                            </div>
					<div class="center">
						<img src="http://'.HOST.'/drmetrix//assets/img/calendar.png" />
						<span><b>Week '.$this->mediaWeek.'</b> - '.date("m/d/Y", strtotime($this->last_week['sd'])).' thru '.date("m/d/Y", strtotime($this->last_week['ed'])).'</span>
					</div>
				</div>
				<table class="table-info">
					<thead>
					  <tr>
						<th>Current Week</th>
						<th>Previous Week</th>
						<th>'.$last_month.' Monthly</th>
						<th>Brand</th>
						<th>Advertiser</th>
						<th>Price</th>
						<th>Total Airings</th>
						<th>Spend Index</th>
						<th>ASD</th>
						<th>Networks</th>
						<th>Watch Sample</th>
					  </tr>
					</thead>
					<tbody>';
        if(empty($result)){
             $html.= '<tr><td colspan="11">No records found.</tr>';
        }else{
            foreach($result as $k => $v){
                if($v['price'] == 0){$price = 'NA';}else{$price = '$'.number_format((float)$v['price'], 2, '.', ',');}
                $html .='<tr>
                                                        <td>'.$v['current_week'].'</td>
                                                        <td>'.$v['previous_week'].'</td>
                                                        <td>'.$v['monthly'].'</td>
                                                        <td class="left-text"><a href="http://'.HOST.'/drmetrix/brandDetail/'.$v['brand_id'].'/brand/browse?pdf=1">'.$v['brand_name'].'</a></td>
                                                        <td class="left-text"><a href="http://'.HOST.'/drmetrix/advDetail/'.$v['adv_id'].'/adv/browse?pdf=1">'.$v['company'].'</a></td>
                                                        <td>'.$price.'</td>
                                                        <td>'.$v['airings'].'</td>
                                                        <td>'.$v['spend_index'].'</td>
                                                        <td>'.$v['asd'].'</td>
                                                        <td>'.$v['network'].'</td>
                                                        <td><a href="http://'.HOST.'/drmetrix/video/'.$v['video_id'].'?pdf=1"><img src="http://'.HOST.'/drmetrix/assets/img/icon-play.png" /></a></td>
                                                </tr>';
                                                }
        }
                       $html .='      </tbody>
				</table><footer>
				<p>
					&#169; Copyright 2015, <a href="#">DRMETRIX LLC.</a> All rights reserved. Occurrence data is collected on a 24/7 basis from direct monitoring of national cable network feeds. Short-form includes spots two minutes or less in length. Long-form
					includes programs of 28:30 in length. Spend index is based on a projection of direct response media expenditures with top long-form and short form campaigns earning a spend index score of 100 and all other advertisers
					calculated in comparison. The index also takes into account network and ROS daypart mix, and whether spots are airing in <a href="https://drmetrix.wordpress.com/2015/02/04/what-is-a-meant-by-local-vs-national-on-drmetrixs-new-report/">national or local ad breaks</a>. ASD represents "<a href="https://drmetrix.wordpress.com/2015/02/17/average-spot-duration-coming-soon/">average spot duration</a>" and is calculated by taking the
					total duration of detected spots divided by total airings. DRMetrix is a media monitoring and research company specializing in the direct response television marketplace. Phone <a href="#">(951) 234-3899</a>. E-mail: <a href="#">info@drmetrix.com</a>
				</p>
				</footer>
			</div>		';
           return $html ;
        
    }
    
    function convertToPdf($html){
        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->render();
        //$dompdf->stream("sample.pdf"); //download Pdf
        $output = $dompdf->output();
        if (!file_exists('../assets/pdf')) {
            mkdir('../assets/pdf', 0777, true);
        }

        $file_to_save = '../assets/pdf/Drmetrix_weekly_brands_longform_'.date("jS_M", strtotime($this->last_week['sd'])).'.pdf';
        file_put_contents($file_to_save, $output);

    }
    
    function sendWeeklyReport(){
        $file_name =  'Drmetrix_weekly_brands_longform_'.date("jS_M", strtotime($this->last_week['sd'])).'.pdf';
        $type = 'Brand';
        $form = 'Long Form';
        Slim_App_Lib_Common::sendMailWithAttachement($this->db,$file_name,$type,$form);
    }
   
}
