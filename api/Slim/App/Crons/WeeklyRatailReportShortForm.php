<?php

/**
 * Author : Ashwini Shinde
 * Date: 13-10-2015
 * Purpose: Send weekly report of short forms for Brands.
 */
require_once("dompdf/dompdf_config.inc.php");
require_once('PHPMailer/class.phpmailer.php');
ini_set('post_max_size','1000M');

class Slim_App_Crons_WeeklyRatailReportShortForm {
    private $db = NULL;
 
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
        
        $cronJob = new Slim_App_Crons_HighestProjectedScoreWeek();
        $this->last_week = $cronJob->getLastMediaWeek(); //current week considering last week
       
        $this->previous_week = $this->getPreviousMediaWeek(); // previous week of current week
       // $this->highestProjectedScore = $this->getHighestProjectedScore();
        
        $cronJob_month = new Slim_App_Crons_HighestProjectedScoreMonth(); 
        $this->monthly = $cronJob_month->getLastMediaMonth(); 
        
        $this->mediaWeek = Slim_App_Lib_Common::weeks_in_year($this->db,$this->last_week);
        if(empty($this->mediaWeek )){
             $this->mediaWeek = Slim_App_Lib_Common::weeks_in_month();
        }
    }
    
    public function getPreviousMediaWeek(){
        $previous_week['sd'] = date('Y-m-d',(strtotime ( '-7 day' , strtotime ( $this->last_week['sd']) ) ));
        $previous_week['ed'] = date('Y-m-d',(strtotime ( '-7 day' , strtotime ( $this->last_week['ed']) ) ));
        return $previous_week;
    }
    
    
    function renderPDFAndEmail(){
        $result = array();
        $result = $this->getShortBrands();
        $html   = $this->createHtmlTemplate($result);
        if(!empty($result)) {$this->convertToPdf($html);$this->sendWeeklyReport();}
    }
    
    function getShortBrands(){
       $calender_sql = "SELECT  media_week,media_year FROM media_calendar WHERE media_week_start = '".$this->last_week['sd']."' AND media_week_end = '".$this->last_week['ed']."' LIMIT 1";
       // $calender_sql = "SELECT  media_week,media_year FROM media_calendar WHERE media_week_start = '2016-02-22' AND media_week_end = '2016-02-28' LIMIT 1";
        
        $stmt = $this->db->prepare($calender_sql);
        $stmt->execute();
        $calender_result = $stmt->fetchAll(PDO::FETCH_OBJ);

        $report = array();
        if(!empty($calender_result)){
       $sql = "SELECT r.rank,r.rank_prev,r.monthly_rank,b.brand_name,a.company_name,r.price,r.ship,r.airings,r.national,r.local,r.spend_index,r.asd,r.networks,b.brand_id,a.adv_id,c.main_category,c.main_sub_category FROM  brand b INNER JOIN category c ON c.brand_id = b.brand_id INNER JOIN advertiser a ON b.adv_id = a.adv_id INNER JOIN rankings r ON b.brand_id = r.brand_id WHERE  calendar_id = '".$calender_result[0]->media_week."' AND media_year = '".$calender_result[0]->media_year."' AND calendar_type = 'W'  ANd report_length = 'short' AND ranking_type = 'retail' GROUP BY b.brand_id ORDER BY rank ASC LIMIT 0 , 50";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
         

            if(!empty($result)){
                foreach($result as $key => $value){
                    $report[$key]['current_week']           =  !empty($value['rank'])  ? $value['rank'] : '-';
                    $report[$key]['previous_week']          =  !empty($value['rank_prev']) ? $value['rank_prev'] : '-';
                    $report[$key]['monthly']                =  !empty($value['monthly_rank']) ? $value['monthly_rank'] : '-';
                    $report[$key]['brand_name']             =  $value['brand_name'];
                    $report[$key]['company']                =  !empty($value['company_name']) ? $value['company_name'] : '-';
                    $report[$key]['price']                  =  $value['price'];
                    $report[$key]['airings']                =  !empty($value['airings']) ? $value['airings'] : '-' ;
                    $report[$key]['national_total_airings'] =  !empty($value['national']) ? $value['national'] : '-';
                    $report[$key]['local_total_airings']    =  !empty($value['local']) ? $value['local'] : '-';
                    $report[$key]['spend_index']            =   !empty($value['spend_index']) ? $value['spend_index']: '-'; ;
                    $report[$key]['asd']                    =  !empty($value['asd']) ? $value['asd'] : '-';;
                    $report[$key]['network']                =  !empty($value['networks']) ? $value['networks']  : '-';
                    $report[$key]['main_category']          =  !empty($value['main_category']) ? $value['main_category'] : '-';
                    $report[$key]['main_sub_category']      =  !empty($value['main_sub_category']) ? $value['main_sub_category'] : '-';
                }
            }
        }
        
        return $report;
    }
    
    function createHtmlTemplate($result){
        $date = strtotime($this->monthly['sd']);
        $last_month =  date('M ', $date);
        $html = '<div style="font-family: Tahoma, Arial, sans-serif;">
             <!--Header-->
      <!-- <div style="color: #ffffff; text-align: center; padding: 5px; margin: 0; font-weight: normal; background-color: #202b39; border:none;">
            <h5>Weekly Retail Ranking Infomercial Report</h5>
            <h5>Long Form - Advertiser</h5>
      </div> -->
      <div style="display:none"><img src="http://'.HOST.'/drmetrix/assets/img/one.png" style="display:none"></div>
      <div style="padding: 8px; height: 65px; background: #f5f5f7; position: relative;">
            <div class="logo-img" style="position: absolute; margin-top: 8px;">
                  <img src="http://'.HOST.'/drmetrix/assets/img/logo.jpg" width="120px">
            </div>
            <div style="text-align: center; padding-top:19px;">
                  <span style="font-size: 10px;"><b>Weekly Retail Ranking Report - Short Form<br/>Media Week '.$this->mediaWeek .' - '.date("m/d/Y", strtotime($this->last_week['sd'])).' thru '.date("m/d/Y", strtotime($this->last_week['ed'])).'</b></span>
            </div>
      </div> <!--Header ends-->
      <!--Main Body-->
      <table width="100%" style="margin-top: 12px; border: none; border-collapse: collapse;">
             <thead>
                  <tr style="background-color: #4f81bd; color: #fff;">
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Current Week</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Previous Week</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">January Month</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Brand</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Company</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Category</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Sub-Category</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Price</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Airings</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">Spend Index</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">National %</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; border-right: 0.01em solid #ccc;">ASD</th>
                      <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; ">Networks</th>
                    <th style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal; "></th>
                  </tr>
            </thead>
            <tbody>';
          
          if(empty($result)){
             $html.= '<tr><td colspan="13">No records found.</tr>';
        }else{
            $i = 0;
            foreach($result as $k => $v){
               if($i % 2 == 0) {$tr_bk = 'background-color: #f5f5f7;';}else{ $tr_bk= 'background-color: #fff;';} 
                                
                $html .='<tr style="'.$tr_bk.'">
                            <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['current_week'].'</td>
                            <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['previous_week'].'</td>
                            <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['monthly'].'</td>
                            <td style="padding: 5px; text-align: left; font-size: 7px; font-weight: normal;">'.$v['brand_name'].'</td>
                            <td style="padding: 5px; text-align: left; font-size: 7px; font-weight: normal;">'.$v['company'].'</td>
                            <td style="padding: 5px; text-align: left; font-size: 7px; font-weight: normal;">'.$v['main_category'].'</td>
                            <td style="padding: 5px; text-align: left; font-size: 7px; font-weight: normal;">'.$v['main_sub_category'].'</td>
                            <td style="padding: 5px; text-align: left; font-size: 7px; font-weight: normal;">'.$v['price'].'</td>
                            <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['airings'].'</td>
                            <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['spend_index'].'</td>
                             <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['national_total_airings'].'</td>
                            <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['asd'].'</td>
                            <td style="padding: 5px; text-align: center; font-size: 7px; font-weight: normal;">'.$v['network'].'</td>
                            <td><img width="16px" src="http://'.HOST.'/drmetrix/assets/img/icon-play.png" /></td>
                        </tr>';
                        $i++;
                }
        }
                       $html .='</tbody>
          </table>
          <!--Main Body ends-->
          <!--Footer-->
          <footer>
              <p style="font-size: 0.42em; margin-top: 22px; padding: 10px 20px; background: #f5f5f7;">
                &#169; Copyright 2015, <a href="#">DRMETRIX LLC.</a> All rights reserved. Occurrence data is collected on a 24/7 basis from direct monitoring of national cable network feeds. Short-form includes spots two minutes or less in length. Long-form includes programs of 28:30 in length. Spend index is based on a projection of direct response media expenditures with top long-form and short form campaigns earning a spend index score of 100 and all other advertisers calculated in comparison. The index also takes into account network and ROS daypart mix, and whether spots are airing in <a href="#">national or local ad breaks</a>. ASD represents "<a href="#">average spot duration</a>" and is calculated by taking the total duration of detected spots divided by total airings. DRMetrix is a media monitoring and research company specializing in the direct response television marketplace. Phone (951) 234-3899. E-mail: <a href="#">info@drmetrix.com</a>
              </p>
          </footer>
          <!--Footer ends-->
    </div>';
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

        $file_to_save = '../assets/pdf/DRMetrix_weekly_retail_ranking_report_'.date("jS_M", strtotime($this->last_week['sd'])).'.pdf';
        file_put_contents($file_to_save, $output);

    }
    
    function sendWeeklyReport(){
        $file_name = 'DRMetrix_weekly_retail_ranking_report_'.date("jS_M", strtotime($this->last_week['sd'])).'.pdf';
        $type = 'Brand';
        $form = 'Short Form';
        Slim_App_Lib_Common::sendMailWithAttachement($this->db,$file_name,$type,$form);
    }
    
    
}
