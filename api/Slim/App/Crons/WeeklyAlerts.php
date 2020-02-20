<?php

/**
 * Author : Ashwini Shinde
 * Date: 15-01-2015
 * Purpose: Email will sent for weekly alerts.
 */
set_time_limit(0);
ini_set('max_execution_time', 0);

class Slim_App_Crons_WeeklyAlerts {
    private $db = NULL;
    
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
        $this->date = date('Y-m-d');
       // $this->date = '2015-07-06';
        $this->date_display = date('Y-m-d');
        $this->i = 1;
        $this->emailBrand = 1;
        $this->emailCreative = 1;
        
        $cronJob = new Slim_App_Crons_HighestProjectedScoreWeek();
        $this->last_week = $cronJob->getLastMediaWeek(); 
        $this->previous_week = $this->getPreviousMediaWeek(); // previous week of current week
        $this->new_brands = $this->result = $this->existing_brands = array();
    }
    
     public function retrieveData(){
        $sql = "SELECT * FROM tracking WHERE status='active' AND notification_type LIKE '%email%' AND frequency='weekly'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this->createEmailTemplate($result);
        
    }
    
     public function getPreviousMediaWeek(){
        $previous_week['sd'] = date('Y-m-d',(strtotime ( '-7 day' , strtotime ( $this->last_week['sd']) ) ));
        $previous_week['ed'] = date('Y-m-d',(strtotime ( '-7 day' , strtotime ( $this->last_week['ed']) ) ));
        return $previous_week;
    }
    
    public function createEmailTemplate($result_tracking){
        $email = array();
        $fetchUser = "SELECT user_id FROM tracking WHERE status = 'active' group by user_id ";
        $stmt = $this->db->prepare($fetchUser);
        $stmt->execute();
        $user_result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $users = array();
        foreach($user_result as $k => $v){
            array_push($users,$v->user_id);
        }
        
        foreach($users as $u1 => $u2){
          $brand_array = array();
          $category_array = array();
          $brand_short_ranking_array = array();
          $brand_long_ranking_array = array();
          $adv_short_ranking_array = array();
          $adv_long_ranking_array = array();
          $newRankingArray = array();
          $bShortArray = $bLongArray = array();
          $aShortArray = $aLongArray = array();
          $updateArray = array();
          
          $this->html = "";
          //retreive user name
          $fetchUser = "SELECT concat(first_name,' ',last_name) as name,email FROM user WHERE user_id = '".$u2."'";
          $stmt = $this->db->prepare($fetchUser);
          $stmt->execute();
          $usersresult = $stmt->fetchAll(PDO::FETCH_OBJ);
          $name = !empty($usersresult[0]->name) ? $usersresult[0]->name : 'User';
          $email = !empty($usersresult[0]->email) ? $usersresult[0]->email : 'ashwini.shinde@v2solutions.com';
          $cat = '';
          
          foreach($result_tracking as  $k=>$v){
              $this->result = array();    
            $cat = '';
            if($v->type == 'brand' && $u2 == $v->user_id){
              if($cat != '') {   $cat = $cat .','. $v->type;}else{$cat = $v->type;}
              array_push($brand_array,$v->id);
              
            }

            if(($v->type == 'category' || $v->type == 'subcategory') && ($u2 == $v->user_id)){
              if($cat != '') {   $cat = $cat .','. $v->type;}else{$cat = $v->type;}
              array_push($category_array,$v->id);  
            }
            
            if(($v->type == 'brand ranking') && ($v->form == 'sf') && ($u2 == $v->user_id)){
               if($cat != '') {   $cat = $cat .','. $v->type;}else{$cat = $v->type;}
                array_push($brand_short_ranking_array,$v->id);  
            }
            
            if(($v->type == 'brand ranking') && ($v->form == 'lf') && ($u2 == $v->user_id)){
               if($cat != '') {   $cat = $cat .','. $v->type;}else{$cat = $v->type;}
                array_push($brand_long_ranking_array,$v->id);  
            }
            
            if(($v->type == 'advertiser ranking') && ($v->form == 'sf') && ($u2 == $v->user_id)){
               if($cat != '') {   $cat = $cat .','. $v->type;}else{$cat = $v->type;}
                array_push($adv_short_ranking_array,$v->id);  
            }
            
            if(($v->type == 'advertiser ranking') && ($v->form == 'lf') && ($u2 == $v->user_id)){
               if($cat != '') {   $cat = $cat .','. $v->type;}else{$cat = $v->type;}
                array_push($adv_long_ranking_array,$v->id);  
            }
          }
             
          
          if(!empty($brand_array) || !empty($category_array) || !empty($brand_short_ranking_array) || !empty($brand_long_ranking_array) || !empty($adv_short_ranking_array) || !empty($adv_long_ranking_array)){

               $middle_content = 'Here is a weekly alert update for week '.date("m/d/Y", strtotime($this->last_week['sd'])).' thru '.date("m/d/Y", strtotime($this->last_week['ed'])).'<br>';
              $this->html.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                  <meta name="viewport" content="width=device-width, initial-scale=1">
                  <title>Weekly Alert Report</title>
                  <style type="text/css">
                    img {max-width: 600px;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;}
                    a {border: 0;outline: none;text-decoration:none!important; color:#00BEFF ;cursor: auto;}
                    a img {border: none;}
                    /* General styling */
                    td, h1, h2, h3  {font-family: Helvetica, Arial, sans-serif;font-weight: 400; }
                    td {font-size: 13px;line-height: 19px;text-align: left; }
                    body {-webkit-font-smoothing:antialiased;-webkit-text-size-adjust:none; width: 100%; height: 100%;color: #37302d;background: #ffffff; }
                    table {border-collapse: collapse !important;}
                    h1, h2, h3, h4 {padding: 0;margin: 0;color: #444444;font-weight: 400;line-height: 110%;}
                    h1 {font-size: 20px;}
                    h2 {font-size: 30px;}
                    h3 {font-size: 24px;}
                    h4 {font-size: 18px;font-weight: normal;}
                    .important-font {color: #21BEB4;font-weight: bold;}
                    .hide {display: none !important;}
                    .force-full-width {width: 100% !important; }
                  </style>
                  <style type="text/css" media="screen">
                    @media screen {
                      @import url(http://fonts.googleapis.com/css?family=Open+Sans:400);
                      /* Thanks Outlook 2013! http://goo.gl/XLxpyl */
                      td, h1, h2, h3 {font-family: Arial, sans-serif !important;}
                    }
                  </style>
                  <style type="text/css" media="only screen and (max-width: 600px)">
                    /* Mobile styles */
                    @media only screen and (max-width: 600px) {
                      table[class="w320"] {width: 320px !important;}
                      table[class="w300"] {width: 300px !important;}
                      table[class="w290"] {width: 290px !important;}
                      td[class="w320"] {width: 320px !important;}
                      td[class~="mobile-padding"] {padding-left: 14px !important;padding-right: 14px !important;}
                      td[class*="mobile-padding-left"] {padding-left: 14px !important;}
                      td[class*="mobile-padding-right"] {padding-right: 14px !important;}
                      td[class*="mobile-padding-left-only"] {padding-left: 14px !important;padding-right: 0 !important;}
                      td[class*="mobile-padding-right-only"] {padding-right: 14px !important;padding-left: 0 !important;}
                      td[class*="mobile-block"] {display: block !important;width: 100% !important;text-align: left !important;padding-left: 0 !important;padding-right: 0 !important;padding-bottom: 15px !important; }
                      td[class*="mobile-no-padding-bottom"] {padding-bottom: 0 !important;}
                      td[class~="mobile-center"] {text-align: center !important;}
                      table[class*="mobile-center-block"] {float: none !important;margin: 0 auto !important;}
                      *[class*="mobile-hide"] {display: none !important;width: 0 !important;height: 0 !important;line-height: 0 !important;font-size: 0 !important;}
                      td[class*="mobile-border"] {border: 0 !important;}
                    }
                  </style>
                </head>
                <body class="body" style="padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none" bgcolor="#ffffff">
                  <table align="center" cellpadding="0" cellspacing="0" width="100%" height="100%">
                    <tbody><tr>
                      <td align="center" valign="top" bgcolor="#ffffff" width="100%">
                        <table cellspacing="0" cellpadding="0" width="100%">
                        <tbody><tr>
                          <td width="100%">
                            <center style="">
                              <img height="auto" width="600" data-max-width="100%" data-default="placeholder" alt="What we do" src="http://www.drmetrix.com/images/newsletterimg.png" style="vertical-align: bottom;">
                            </center>
                          </td>
                        </tr>
                        <tr>
                          <td style="">
                            <center>
                              <table style="background-color: #1F1F1F;" cellpadding="0" cellspacing="0" width="600" class="w320">
                                <tbody><tr>
                                  <td valign="top" class="mobile-block mobile-no-padding-bottom mobile-center" width="270" style="background:#1f1f1f;padding: 0px 0px 0px 12px;font-size: 1.3em;color: #fff!important;line-height:normal">
                                      <p>Don&rsquo;t forget to subscribe to our Blog</p>
                                  </td>
                                  <td style="background:#1f1f1f;padding:10px 15px 10px 10px" width="270" class="mobile-block mobile-center" valign="top">
                                    <a href="https://drmetrix.wordpress.com/">
                                    <table border="0" cellpadding="0" cellspacing="0" class="mobile-center-block" align="right">
                                      <tbody><tr>                      
                                        <td class="mobile-block mobile-center" width="270" style="background-color:#00BEFF;border-radius:0px;color:#ffffff;display:inline-block;font-family:sans-serif;font-weight:bold;font-size:13px;line-height:33px;text-align:center;text-decoration:none;width:130px;-webkit-text-size-adjust:none;mso-hide:all;border-radius: 5px;">
                                          <span style="color:#ffffff">Sign up now!</span>
                                        </td>
                                      </tr></tbody>                                      
                                    </table></a>
                                  </td>
                                </tr></tbody>
                              </table>
                            </center>
                          </td>
                        </tr>
                        <tr>
                          <td style="">
                            <center>
                              <table cellpadding="0" cellspacing="0" width="600" class="w320">
                                <tbody><tr>
                                  <td align="left" class="mobile-padding" style="padding:20px 20px 30px;background-color: #f1f1f1;">
                                    <br class="mobile-hide">
                                    <h1>Dear '.$name.'</h1><br>'.$middle_content.'
                                  </td>
                                </tr></tbody>
                              </table>
                            </center>
                          </td>
                        </tr>
                        <tr>';

              if(!empty($brand_array)){ $this->html .= $this->newCreatives($brand_array);}

              
              if(!empty($category_array)){ $this->html.= '<tr><td valign="top" style="display:block"><center style=""><br>&nbsp;<br></center></td></tr>';
 $this->html.= $this->newBrandAdvertises($category_array);}

              if(!empty($brand_short_ranking_array)){$bShortArray = $this->rankingReport($brand_short_ranking_array,'sf',1,$u2);}
              
              if(!empty($brand_long_ranking_array)){$bLongArray = $this->rankingReport($brand_long_ranking_array,'lf',1,$u2);}
               
              if(!empty($adv_short_ranking_array)){$aShortArray = $this->rankingReport($adv_short_ranking_array,'sf',2,$u2);}
              
              if(!empty($adv_long_ranking_array)){$aLongArray = $this->rankingReport($adv_long_ranking_array,'lf',2,$u2);}

              foreach($bShortArray as $k => $v){
                $v['id'] = $k;
                $newRankingArray[] = $v;
              }
              
              foreach($bLongArray as $k => $v){
                $v['id'] = $k;
                $newRankingArray[] = $v;
              }

              foreach($aShortArray as $k => $v){
                $v['id'] = $k;
                $newRankingArray[] = $v;
              }
              
              foreach($aLongArray as $k => $v){
                $v['id'] = $k;
                $newRankingArray[] = $v;
              }
            
              if(!empty($newRankingArray)){ $this->html.= $this->newRanking($newRankingArray);}
                
              $this->html.= '<tr><td valign="top" style="display:block"><center style=""><br>&nbsp;<br></center></td></tr>
                        <tr>
                          <td valign="top" style="background-color:#ffffff;">
                            <center>
                              <table border="0" cellpadding="0" cellspacing="0" width="600" class="w320" style="height:100%;background-color: #f1f1f1;">
                                <tbody><tr>
                                  <td valign="top" class="mobile-padding" style="padding:20px 20px 30px;">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                      <tbody><tr style="display: inline-block;width: 560px;">
                                        <td style="">
                                         Regards, <br>DRMetrix
                                                      <br><br>
                                                      For more information on DRMetrix&rsquo;s breakthough reporting methodologies, please visit our blog. Be sure and follow us to be among the first to receive announcements of new products and services coming from DRMetrix throughout 2016!
                                        </td>
                                      </tr></tbody>
                                    </table>
                                  </td>
                                </tr></tbody>
                              </table>
                            </center>

                          </td>
                        </tr>
                        <tr>
                          <td>
                            <center>
                              <table border="0" cellpadding="0" cellspacing="0" width="600" class="w320" style="height:100%;color:#ffffff" bgcolor="#1f1f1f">
                                <tbody><tr>
                                <td style="/* border-bottom:1px solid #e7e7e7; */">
                                  <center>
                                    <table cellpadding="0" cellspacing="0" width="600" class="w320">
                                      <tbody><tr>
                                        <td align="right" valign="middle" style="font-size:12px;padding:20px; background-color:#1f1f1f; color:#ffffff; text-align:left; ">
                                          <a style="color:#ffffff;" href="info@drmetrix.com">Contact Us</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                                          <a style="color:#ffffff;" href="https://www.facebook.com/DRMetrix">Facebook</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                                          <a style="color:#ffffff;" href="https://twitter.com/DRMetrix">Twitter</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                                          <a style="color:#ffffff;" href="info@drmetrix.com">Support</a>
                                        </td>
                                        <td valign="top" class="mobile-block mobile-center" width="270" style="background:#1f1f1f;padding:10px 15px 10px 10px">
                                          <table border="0" cellpadding="0" cellspacing="0" class="mobile-center-block" align="right">
                                            <tbody><tr>
                                              <td align="right">
                                                <a href="https://www.facebook.com/DRMetrix">
                                                <img src="http://keenthemes.com/assets/img/emailtemplate/social_facebook.png" width="30" height="30" alt="social icon">
                                                </a>
                                              </td>
                                              <td align="right" style="padding-left:5px">
                                                <a href="https://twitter.com/DRMetrix">
                                                <img src="http://keenthemes.com/assets/img/emailtemplate/social_twitter.png" width="30" height="30" alt="social icon">
                                                </a>
                                              </td>
                                              <td align="right" style="padding-left:5px">
                                                <a href="https://drmetrix.wordpress.com/">
                                                <img src="http://drmetrix.com/images/wordpressnl.png" width="30" height="30" alt="social icon">
                                                </a>
                                              </td>
                                              <td align="right" style="padding-left:5px">
                                                <a href="https://www.linkedin.com/company/home?report%2Efailure=97FtKHe01TXZqDCgSwRuDAgNTvtFmw4BIp13UzOz81TxIU0_mpSjJzEz71T0SxI_14ZseOdd6PTdIUg_m4RXeOXe6Ljz4r26IOv3a38J8A5UFd8h">
                                                <img src="http://keenthemes.com/assets/img/emailtemplate/social_linkedin.png" width="30" height="30" alt="social icon">
                                                </a>
                                              </td>                              
                                            </tr>
                                          </tbody></table>
                                        </td>
                                      </tr>
                                    </tbody></table>
                                  </center>
                                </td>
                              </tr></tbody>
                              </table>
                            </center>
                          </td>
                        </tr></tbody>
                      </table>
                    </td>
                  </tr></tbody>
                </table> <!-- End main table -->
            </body></html>';
          }    
       
         
         
          if(!empty($this->new_brands) || !empty($this->existing_brands) || !empty($this->result) || !empty($newRankingArray)){ 
                echo $this->html;
               /* $this->sendDailyReport($email,$name); 
               $this->addEntry($email,$u2,$cat);
                $this->updateQuery($newRankingArray,1,'sf',$u2);
                $this->updateQuery($newRankingArray,1,'lf',$u2);
                $this->updateQuery($newRankingArray,2,'sf',$u2);
                $this->updateQuery($newRankingArray,2,'lf',$u2);
                $this->update($category_array,$u2);
                 $this->update($brand_array,$u2);*/
            }
        }
    }
    
    function update($category_array,$u2){
        $updateArray = array();
        foreach($category_array as $key => $value){
             $array = array_push($updateArray,$value);
            
          }
          $ids = implode(",",$updateArray);
         
          if($ids != ''){
           $sql = "UPDATE tracking SET triggered_on = '".date('Y-m-d h:i:s')."' WHERE id IN (".$ids.")  AND user_id ='".$u2."'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
          }
    }
    
    function updateQuery($newRankingArray , $brand_adv_flag,$form,$u2){
        $updateArray = array();
        foreach($newRankingArray as $key => $value){
              if($value['flag'] == $brand_adv_flag && $value['form'] == $form ){
                 $array = array_push($updateArray,$value['id']);
              }
          }
          $ids = implode(",",$updateArray);
          if($brand_adv_flag == 1){$type = 'brand ranking';}else{$type = 'advertiser ranking';}
          
          if($ids != ''){
           $sql = "UPDATE tracking SET triggered_on = '".date('Y-m-d h:i:s')."' WHERE id IN (".$ids.") AND type = '".$type."' AND form = '".$form."' AND user_id ='".$u2."'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
          }
    }
    
    
     function addEntry($email,$user_id,$category){  
        $arr = explode(",", $category);
        foreach($arr as $k => $v){
              $sql = "INSERT INTO configuration (username,user_id,type,category,triggered_on,frequency,send_mail) VALUES ('".$email."','".$user_id."','weekly alert','".$v."','".$_SESSION['triggered_on']."','monthly','".$_SESSION['sent']."')";
               $stmt = $this->db->prepare($sql);
               $stmt->execute();
        }
      
    }
    
    public function sendDailyReport($email,$name){
        $subject = 'Weekly Tracking Alert Report';
        Slim_App_Lib_Common::sendMail($this->db,$email,$name,$this->html,$subject);
    }
   
    
     public function rankingReport($brand_ranking_array,$type,$brand_adv_flag,$u2){
        if(!empty($brand_ranking_array)){
          $rank_increases = array();
          $rank_decreases = array();
          $rank_equal = array();
          $brand_adv = array();
          $adv_names = array();
          $brand_names = array();
          $names = array();
          
          $where = $brand_adv_flag == 1 ? "type = 'brand ranking'" : "type = 'advertiser ranking'";
          $rankIncreasesFind = "SELECT id,rank_increases FROM tracking WHERE ".$where." AND status = 'active' AND frequency='weekly' AND rank_increases!=0  AND form ='".$type."' AND user_id=".$u2;
          $stmt = $this->db->prepare($rankIncreasesFind);
          $stmt->execute();
          $rank_increases = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          $rankDecreasesFind = "SELECT id,rank_decreases FROM tracking WHERE ".$where." AND status = 'active' AND frequency='weekly' AND rank_decreases!=0 AND form ='".$type."' AND  user_id=".$u2;
          $stmt = $this->db->prepare($rankDecreasesFind);
          $stmt->execute();
          $rank_decreases = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
           
          if(!empty($rank_increases) || !empty($rank_decreases)){
            // find prev media rank 
            $ranking_ids = implode(",",$brand_ranking_array);
            $sql = "SELECT brand_adv_id,ranking FROM brand_adv_spend_index WHERE brand_adv_flag = ".$brand_adv_flag." AND brand_adv_id IN (".$ranking_ids.") AND type ='".$type."' AND from_date = '".$this->previous_week['sd']."' AND to_date = '".$this->previous_week['ed']."'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $brand_previous_ranking_ = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if($brand_adv_flag == 1){
               $retrieveName = "SELECT brand_name,brand_id as brand_adv_id FROM brand WHERE  brand_id IN (".$ranking_ids.")";
                $stmt = $this->db->prepare($retrieveName);
                $stmt->execute();
                $brand_names = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }else{
                $retrieveName = "SELECT company_name,adv_id as brand_adv_id FROM advertiser WHERE adv_id IN (".$ranking_ids.")";
                $stmt = $this->db->prepare($retrieveName);
                $stmt->execute();
                $adv_names = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            if(!empty($brand_names)){
                foreach($brand_names as $k => $v){
                    $names[$v['brand_adv_id']]['name'] = $v['brand_name'] ;
                }
            }
        
            if(!empty($adv_names)){
                foreach($adv_names as $k => $v){
                    $names[$v['brand_adv_id']]['name'] = $v['company_name'] ;
                }
            }
        
            $ranking_ids = implode(",",$brand_ranking_array);
            $sql = "SELECT brand_adv_id,ranking FROM brand_adv_spend_index WHERE brand_adv_flag = ".$brand_adv_flag." AND brand_adv_id IN (".$ranking_ids.") AND type ='".$type."' AND from_date = '".$this->last_week['sd']."'  AND to_date = '".$this->last_week['ed']."'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $brand_last_ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
           
            foreach($brand_previous_ranking_ as $k1 => $v1){
                foreach($brand_last_ranking as $k2 => $v2){
                    if($v1['brand_adv_id'] == $v2['brand_adv_id']){
                        if($v2['ranking'] < $v1['ranking']){
                            $brand_adv[$v1['brand_adv_id']]['diff'] = $v1['ranking'] - $v2['ranking'];
                            $brand_adv[$v1['brand_adv_id']]['type'] = 'increases';
                            $brand_adv[$v1['brand_adv_id']]['current'] = $v2['ranking'];
                            $brand_adv[$v1['brand_adv_id']]['flag'] = $brand_adv_flag;
                            $brand_adv[$v1['brand_adv_id']]['name'] =  $names[$v1['brand_adv_id']]['name'] ;
                            $brand_adv[$v1['brand_adv_id']]['form'] = $type;
                            $brand_adv[$v1['brand_adv_id']]['user_id'] = $u2;
                        } else if($v2['ranking'] > $v1['ranking']){
                            $brand_adv[$v1['brand_adv_id']]['diff'] = $v2['ranking'] - $v1['ranking'];
                            $brand_adv[$v1['brand_adv_id']]['type'] = 'decreases';
                            $brand_adv[$v1['brand_adv_id']]['current'] = $v2['ranking'];
                            $brand_adv[$v1['brand_adv_id']]['flag'] = $brand_adv_flag;
                            $brand_adv[$v1['brand_adv_id']]['name'] =  $names[$v1['brand_adv_id']]['name'] ;
                            $brand_adv[$v1['brand_adv_id']]['form'] = $type;
                            $brand_adv[$v1['brand_adv_id']]['user_id'] = $u2;
                        }
                    }
                }
            }
            
            if(!empty($rank_increases) && !empty($brand_adv)){
              foreach($rank_increases as $k => $v){
                foreach($brand_adv as $k1 => $v1){
                  if($v['id'] == $k1){
                   if($v['rank_increases'] > $v1['diff']){
                       unset($brand_adv[$k1]);
                   }
                  }
                }
              }
            }
            
            if(!empty($rank_decreases) && !empty($brand_adv)){
              foreach($rank_decreases as $k => $v){
               foreach($brand_adv as $k1 => $v1){
                 if($v['id'] == $k1){
                   if($v['rank_decreases'] < $v1['diff']){
                       unset($brand_adv[$k1]);
                   }
                  
                 }
               }
              }
            }           
          }
       
          return $brand_adv;
        }
    }

    function newRanking($newRankingArray){ 
      $html_body='';
        if(!empty($newRankingArray)){
          $html_body .= '<tr><td valign="top" style="display:block"><center style=""><br>&nbsp;<br></center></td></tr>
                <tr><td valign="top">
                  <center>
                    <div class="w320" style="width: 600px;background-color: #003344;color: #fff;line-height: normal;padding-top: 1em;padding-bottom: 1em;border-radius: 5px 5px 0 0;">Ranking Alerts</div><br>
                      <table border="0" cellpadding="0" cellspacing="0" width="600" class="w320" style="height:100%;">
                      <tbody><tr style="text-align: center;">
                        <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Type</span></td>
                        <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Brand/Advertiser</span></td>
                        <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Ranking Change</span></td>
                        <td style="font-size: 1.2em;text-align: center;"><span class="tablecolor">Current Ranking</span></td>
                      </tr>
                      <tr>
                        <td style="padding: 0.5em;"></td>
                        <td style="padding: 0.5em;"></td>
                        <td style="padding: 0.5em;"></td>
                        <td style="padding: 0.5em;"></td>
                      </tr>';
          $i = 1;
          foreach($newRankingArray as $key => $value){
               if($i % 2 == 0){$class = 'background-color: #fff;';}else{$class = 'background-color: #EFEFEF;';}
               if($value['flag'] == 1){
                   $type = 'Brand';
                   $url = "http://".HOST."/drmetrix/brandDetail/".$value['id']."/brand/browse?pdf=1";
               }else{
                   $type = 'Advertiser';
                   $url = "http://".HOST."/drmetrix/advDetail/".$value['id']."/adv/browse?pdf=1";
               }
               if($value['type'] == 'increases'){$sign = '+';}else if($value['type'] == 'decreases'){$sign = '-';}else{$sign = '';$value['type'] = "-";}
              
               
              $html_body .= '<tr style= '.$class.'>';
              $html_body.= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;"><span class="tablecolor2">'.$type.'</span></a></td>';
              $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="'.$url.'"><span class="tablecolor2">'.$value['name'].'</span></a></td>';
              $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;"><span class="tablecolor2">'.$sign.$value['diff'].'</span></a></td>';
              $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;"><span class="tablecolor2">'.$value['current'].'</span></a></td>';
               $html_body .= '</tr>';
            $i++;
          }
          $html_body.= '</tbody></table></center></td></tr>';
        }   
        return $html_body;           
    }
        
     function newBrandAdvertises($category_array){
         //new brands and new advertisers added into category or subcategory
      $html_body='';
        if(!empty($category_array)){
            $subcategory_array = array();
            $category_id = implode(",",$category_array);
            // Get all subcategories id
            $sql = "SELECT main_category FROM category WHERE category_id IN (".$category_id.")";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            $sql = "SELECT category_id FROM category WHERE main_category = '".$result[0]->main_category."'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
             //Get all newly detected brands. in subcategory id
            foreach($subcategories as $k => $v){
                 array_push($subcategory_array,$v['category_id']);
            }
                
            $this->new_brands = $this->brandsDetected($subcategory_array); 
            
            $this->existing_brands = $this->existingBrands($subcategory_array);
          
            if(!empty($this->new_brands) || !empty($this->existing_brands)){
               $html_body .= '<tr><td valign="top" style="display:block"><center style=""><br>&nbsp;<br></center></td></tr>
                            <tr>        
                              <td valign="top" style="/* background-color:#f8f8f8; *//* border-bottom:1px solid #e7e7e7; */">
                              <center>
                                <div class="w320" style="width: 600px;background-color: #003344;color: #fff;line-height: normal;padding-top: 1em;padding-bottom: 1em;border-radius: 5px 5px 0 0;">New Brand Alerts for Categories and Advertisers Tracked</div><br>
                                <table border="0" cellpadding="0" cellspacing="0" width="600" class="w320" style="height:100%;">
                                    <tbody><tr style="text-align: center;">
                                      <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Brand</span></td>
                                      <td colspan="2" style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;">
                                      <span class="tablecolor">Creative</span></td>
                                      <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Advertiser</span></td>
                                      <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Category</span></td>
                                      <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Sub-category</span></td>
                                       <td style="font-size: 1.2em;text-align: center;"><span class="tablecolor">Detected On</span></td>
                                    </tr>
                                    <tr>
                                      <td style="padding: 0.5em;"></td>
                                      <td style="padding: 0.5em;"></td>
                                      <td style="padding: 0.5em;"></td>
                                      <td style="padding: 0.5em;"></td>
                                      <td style="padding: 0.5em;"></td>
                                      <td style="padding: 0.5em;"></td>
                                    </tr>';
              $i = 1;
              foreach($this->new_brands as $key => $value){
               if($i % 2 == 0){$class = 'background-color: #fff;';}else{$class = 'background-color: #EFEFEF;';}
                $html_body .= '<tr style= '.$class.'>';
                  $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/brandDetail/'.$value->brand_id.'/brand/browse?pdf=1"><span class="tablecolor2">'.$value->brand_name.'</span></a></td>';
                  if($value->length <= 120){$l = 'short';}else{$l = 'long';}
                  $html_body .= '<td style="padding: 0.5em ;"><a style="color: #2fcaff !important;text-decoration:none !important;" href="http://'.HOST.'/drmetrix/creativeDetail/'.$value->creative_id.'/'.$l.'/browse?pdf=1"><span class="tablecolor2">'.$value->creative_name.'</span></a></td>';
                  $html_body .= '<td><a href="http://'.HOST.'/drmetrix/video/'.$value->video.'?pdf=1">
                                      <img width="60%" src="http://www.drmetrix.com/images/playbutton.png">
                                      </a>
                                    </td>';
                  $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/advDetail/'.$value->adv_id.'/adv/browse?pdf=1"><span class="tablecolor2">'.$value->company_name.'</span></a></td>';
                  $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1"><span class="tablecolor2">'.$value->main_category.'</span></a></td>';
                  $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1"><span class="tablecolor2">'.$value->main_sub_category.'</span></a></td>';
    		          $html_body .= '<td style="padding: 0.5em;"><span class="tablecolor2">'.date("m/d/Y", strtotime($value->first_detection)).'</span></td>';
                  $html_body .='</tr>';      
    		          $i++;
              }
             
            }
            
            if(!empty($this->existing_brands)){ $i = 1;
                foreach($this->$existing_brands as $key => $value){
                    if($i % 2 == 0){$class = 'background-color: #fff;';}else{$class = 'background-color: #EFEFEF;';}
                    $html_body .= '<tr style= '.$class.'>';
                      $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/brandDetail/'.$value->brand_id.'/brand/browse?pdf=1"><span class="tablecolor2">'.$value->brand_name.'</span></a></td>';
                    if($value->length <= 120){$l = 'short';}else{$l = 'long';}

                      $html_body .= '<td style="padding: 0.5em ;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/creativeDetail/'.$value->creative_id.'/'.$l.'/browse?pdf=1"><span class="tablecolor2">'.$value->creative_name.'</span></a></td>';
                      $html_body .= '<td><a href="http://'.HOST.'/drmetrix/video/'.$value->video.'?pdf=1">
                                          <img width="60%" src="http://www.drmetrix.com/images/playbutton.png">
                                          </a>
                                        </td>';
                      $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/advDetail/'.$value->adv_id.'/adv/browse?pdf=1"><span class="tablecolor2">'.$value->company_name.'</span></a></td>';
                    $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1"><span class="tablecolor2">'.$value->main_category.'</span></a></td>';
                    $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1"><span class="tablecolor2">'.$value->main_sub_category.'</span></a></td>';
                    $html_body .= '<td style="padding: 0.5em;"><span class="tablecolor2">'.date("m/d/Y", strtotime($value->first_detection)).'</span></td>';
                    $html_body .='</tr>';
		              $i++;
                }
               
            }            
            $html_body .= '</tbody></table></center></td></tr>';
            
        }
         return $html_body; 
    }
    
    
    public function  newCreatives($brand_array){
        //New creatives added into brand.
      $brandIds = implode(",",$brand_array);
      $sql = "SELECT b.brand_id,b.brand_name,cat.category_id,cat.main_category,cat.main_sub_category,a.adv_id,a.company_name,c.creative_id,c.creative_name,c.first_detection,c.length,c.video FROM brand b INNER JOIN creative c  ON b.brand_id = c.brand_id  INNER JOIN category cat ON cat.brand_id = b.brand_id RIGHT JOIN advertiser a ON a.adv_id = b.adv_id WHERE b.brand_id  IN (".$brandIds.") AND c.first_detection >= '".$this->last_week['sd']."' AND c.first_detection <= '".$this->last_week['ed']."' GROUP by b.brand_id";
    
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      $this->result = $stmt->fetchAll(PDO::FETCH_OBJ); 

      $html_body = '';        
      if(!empty($this->result)){
        $i = 1;
          $html_body = '<tr>        
                      <td valign="top" style="/* background-color:#f8f8f8; *//* border-bottom:1px solid #e7e7e7; */">
                          <center>
                            <div class="w320" style="width: 600px;background-color: #003344;color: #fff;line-height: normal;padding-top: 1em;padding-bottom: 1em;border-radius: 5px 5px 0 0;">New Creative Alerts for Brands Tracked</div><br>
                            <table border="0" cellpadding="0" cellspacing="0" width="600" class="w320" style="height:100%;">
                              <tbody><tr style="text-align: center;">
                              <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Brand</span></td>
                              <td colspan="2" style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Creative</span></td>
                              <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Advertiser</span></td>
                              <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Category</span></td>
                              <td style="border-right: 2px solid #CACACA;font-size: 1.2em;text-align: center;"><span class="tablecolor">Sub-Category</span></td>
                              <td style="font-size: 1.2em;text-align: center;"><span class="tablecolor">Detected On</span></td>
                            </tr>
                          <tr>
                              <td style="padding: 0.5em;"></td>
                              <td style="padding: 0.5em;"></td>
                              <td style="padding: 0.5em;"></td>
                              <td style="padding: 0.5em;"></td>
                              <td style="padding: 0.5em;"></td>
                          </tr>';
          foreach($this->result as $key => $value){
               if($i % 2 == 0){$class = 'background-color: #fff;';}else{$class = 'background-color: #EFEFEF;';}
              $html_body .= '<tr style= '.$class.'>';
              $html_body .= '<td style="padding: 0.5em"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/brandDetail/'.$value->brand_id.'/brand/browse?pdf=1"><span class="tablecolor2">'.$value->brand_name.'</span></a></td>';
              if($value->length <= 120){$l = 'short';}else{$l = 'long';}
              $html_body .= '<td style="padding: 0.5em;">
                                <a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/creativeDetail/'.$value->creative_id.'/'.$l.'/browse?pdf=1"><span class="tablecolor2">'.$value->creative_name.'</span>
                                </a>
                              </td>';
               $html_body .= '<td><a href="http://'.HOST.'/drmetrix/video/'.$value->video.'?pdf=1">
                                    <img width="60%" src="http://www.drmetrix.com/images/playbutton.png">
                                    </a>
                                </td>';
              $html_body .= '<td style="padding: 0.5em;">
                                <a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/advDetail/'.$value->adv_id.'/adv/browse?pdf=1">
                                <span class="tablecolor2">'.$value->company_name.'</span>
                                </a>
                              </td>';
              $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1"><span class="tablecolor2">'.$value->main_category.'</span></a></td>';
              $html_body .= '<td style="padding: 0.5em;"><a style="color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1"><span class="tablecolor2">'.$value->main_sub_category.'</span></a></td>';
               $html_body .= '<td style="padding: 0.5em;"><span class="tablecolor2">'.date("m/d/Y", strtotime($value->first_detection)).'</span></td>';
               $html_body .='</tr>';
               $i++;
          }
        $html_body .= '</tbody></table></center></td></tr>';
      }
      return $html_body;   
    } 
        
    public function brandsDetected($subcategories){
       $subcategoryIds =  implode(",",$subcategories);
       $sql = "SELECT b.brand_id,b.brand_name,cat.category_id,cat.main_category,cat.main_sub_category,a.adv_id,a.company_name,c.creative_id,c.creative_name,b.first_detection,c.length,c.video FROM brand b INNER JOIN creative c  ON b.brand_id = c.brand_id  INNER JOIN category cat ON cat.brand_id = b.brand_id RIGHT JOIN advertiser a ON a.adv_id = b.adv_id WHERE cat.category_id  IN (".$subcategoryIds.") AND b.first_detection >= '".$this->last_week['sd']."' AND b.first_detection <= '".$this->last_week['ed']."' GROUP by b.brand_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        return $result;
    }
    
    public function existingBrands($subcategories){
        $subcategoryIds =  implode(",",$subcategories);
        $sql = "SELECT b.brand_id,b.brand_name,cat.category_id,cat.main_category,cat.main_sub_category,a.adv_id,a.company_name,c.creative_id,c.creative_name,b.first_detection,c.length,c.video FROM brand b INNER JOIN creative c  ON b.brand_id = c.brand_id  INNER JOIN category cat ON cat.brand_id = b.brand_id RIGHT JOIN advertiser a ON a.adv_id = b.adv_id WHERE cat.category_id  IN (".$subcategoryIds.") AND b.first_detection <='".$this->last_week['sd']."' AND  b.first_detection>='".$this->last_week['ed']."' OR c.first_detection >= '".$this->last_week['sd']."' AND c.first_detection <= '".$this->last_week['ed']."'GROUP by b.brand_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        return $result;
    }
}