<?php

/**
 * Author : Ashwini Shinde
 * Date: 15-01-2015
 * Purpose: Email will sent for daily alerts.
 */
set_time_limit(0);
ini_set('max_execution_time', 0);

class Slim_App_Crons_DailyAlerts {
    private $db = NULL;
    
    public function __construct() {
      $this->db = Slim_App_Lib_Db::getInstance()->dbh;
      $this->count = 0;
      $this->date = date('Y-m-d',strtotime("-1 days"));
      $this->date_display = date('M d, Y',strtotime($this->date));
      $this->html = '';
      $this->user = array();
      $this->new_brands = $this->result = $this->existing_brands = array();
    }
    
    public function retrieveData(){
      $sql = "SELECT * FROM tracking WHERE status='active' AND notification_type LIKE '%email%' AND frequency='daily'";
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_OBJ);
      $this->html = '';
      $this->createEmailTemplate($result);
        
    }
    
    public function createEmailTemplate($result_tracking){
      $email = array();
      $fetchUser = "SELECT user_id FROM tracking where status = 'active' group by user_id ";
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
        }
           
        if(!empty($brand_array) || !empty($category_array)){
           $this->html.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
              <html xmlns="http://www.w3.org/1999/xhtml">
              <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Daily Alert Report</title>
                <style type="text/css">
                    main *{font-size: 12pt;}
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
                <table align="center" cellpadding="0" cellspacing="0" width="600" height="100%">
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
                                <td valign="middle" class="mobile-block mobile-no-padding-bottom mobile-center" width="270" style="background:#1f1f1f;padding: 0px 0px 0px 12px;font-size: 1em;color: #fff!important;line-height:normal">
                                    <p style="padding:0;margin:0">Don&rsquo;t forget to subscribe to our Blog</p>
                                </td>
                                <td style="background:#1f1f1f;padding:10px 15px 10px 10px;color:#ffffff" width="270" class="mobile-block mobile-center" valign="top">
                                  <a href="https://drmetrix.wordpress.com/">
                                  <table border="0" cellpadding="0" cellspacing="0" class="mobile-center-block" align="right">
                                    <tbody><tr> 
                                    <td style="background:#00BEFF;-moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;font-family:Helvetica,Arial,sans-serif;margin:0;padding:10px 15px 10px 15px" bgcolor="#00BEFF"><a href="#" style="background:#00BEFF;border:1px none rgba(0,0,0,0.2);border-radius:5px;color:#fff;font-size:14px;font-weight:bold;text-decoration:none;padding: 0;margin: 0" target="_blank">Sign up now!</a></td>
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
                                  <h1>Dear '.$name.'</h1>
                                  <br>Here is a daily alert update for '.$this->date_display.'<br>
                                </td>                                  
                              </tr></tbody>
                            </table>
                          </center>
                        </td>
                      </tr>
                      <tr><td valign="top" style="display:block"><center style=""><br>&nbsp;<br></center></td></tr>';
       if(!empty($category_array)){ $this->html.= $this->newBrandAdvertises($category_array);}

       if(!empty($brand_array)){ $this->html .= $this->newCreatives($brand_array);}

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
        
 //echo $this->html;
//$this->sendDailyReport($email,$name);
          if(!empty($this->new_brands) || !empty($this->existing_brands) || !empty($this->result)){ 
                echo $this->html;
                $this->sendDailyReport($email,$name); 
            	$this->addEntry($email,$u2,$cat);
             	$this->update($category_array,$u2);
            	$this->update($brand_array,$u2);
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
    function addEntry($email,$user_id,$category){
        $arr = explode(",", $category);
        foreach($arr as $k => $v){
              $sql = "INSERT INTO configuration (username,user_id,type,category,triggered_on,frequency,send_mail) VALUES ('".$email."','".$user_id."','daily alert','".$v."','".$_SESSION['triggered_on']."','daily','".$_SESSION['sent']."')";
               $stmt = $this->db->prepare($sql);
               $stmt->execute();
        }
      
    }
    
    function sendDailyReport($email,$name){
        $subject = 'Daily Tracking Alert Report';
        Slim_App_Lib_Common::sendMail($this->db,$email,$name,$this->html,$subject);
    }
   
    
    public function newBrandAdvertises($category_array){
         //new brands and new advertisers added into category or subcategory
        $html_body = '';
        if(!empty($category_array)){
            $subcategory_array = $category_names = array();
            $category_id = implode(",",$category_array);
            // Get all subcategories id
            $sql = "SELECT main_category FROM category WHERE category_id IN (".$category_id.")";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach($result as $r1 => $r2){
                $category_names[]  = "'".$r2->main_category."'";
            }

            if(!empty($category_names)){
              $category_names = implode(",",$category_names);
            }

           $sql = "SELECT category_id FROM category WHERE main_category IN (".$category_names.")";
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
               $html_body .='<tr>
        
                          <td valign="top" style="/* background-color:#f8f8f8; *//* border-bottom:1px solid #e7e7e7; */">
                              <center>
                                <div class="w320" style="width: 600px;background-color: #003344;color: #fff;line-height: normal;-moz-border-radius: 5px 5px 0 0; -webkit-border-radius: 5px 5px 0 0; border-radius: 5px 5px 0 0;"><br>&nbsp;</br>New Brand Alerts for Categories and Advertisers Tracked<br>&nbsp;<!--/div--></div><br>
                                <table border="0" cellpadding="0" cellspacing="0" width="600" class="w320" style="height:100%;">
                                              <tbody><tr>
                                      <td style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Brand</td>
    <td style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Advertiser</td>
    <td colspan="2" style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Creative</td>
    <td style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Category</td>
    <td style="font-size: 16px;text-align: center;">Sub-category</td>
                                  </tr>
                                <tr>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                          </tr>';
            }
           
          
            if(!empty($this->new_brands)){
                $i = 1;
                foreach($this->new_brands as $key => $value){
                    if($i % 2 == 0){$class = 'background-color: #fff;';}else{$class = 'background-color: #EFEFEF;';}

                    $html_body .= '<tr style= '.$class.'>';
                      $html_body .= '<td style="padding: 0.5em;font-size:13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix/brandDetail/'.$value->brand_id.'/brand/browse?pdf=1">'.$value->brand_name.'</a></td>';

                    if($value->length <= 120){$l = 'short';}else{$l = 'long';}

                      $html_body .= '<td style="padding: 0.5em;font-size:13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix/advDetail/'.$value->adv_id.'/adv/browse?pdf=1">'.$value->company_name.'</a></td>';

                      $html_body .= '<td style="padding: 0.5em ;font-size:13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix/creativeDetail/'.$value->creative_id.'/'.$l.'/browse?pdf=1">'.$value->creative_name.'</a></td>';
                      $html_body .= '<td><a href="http://'.HOST.'/drmetrix/video/'.$value->video.'?pdf=1">
                                          <img width="60%" src="http://www.drmetrix.com/images/playbutton.png">
                                        </a>
                                      </td>';                      
                      $html_body .= '<td style="padding: 0.5em;font-size:13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix?pdf=1">'.$value->main_category.'</a></td>';
                      $html_body .= '<td style="padding: 0.5em;font-size:13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix?pdf=1">'.$value->main_sub_category.'</a></td>';
                    $html_body .='</tr>';
		                $i++;                   
                }                
            }           
            
            if(!empty($this->existing_brands)){
                $i = 1;
                foreach($this->existing_brands as $key => $value){
                    if($i % 2 == 0){$class = 'background-color: #fff;';}else{$class = 'background-color: #EFEFEF;';}
                      $html_body .= '<tr style= '.$class.'>';
                        $html_body .= '<td style="padding: 0.5em;font-size: 13px;"><a style="font-size: 13px;color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/brandDetail/'.$value->brand_id.'/brand/browse?pdf=1">'.$value->brand_name.'</a></td>';
                      if($value->length <= 120){$l = 'short';}else{$l = 'long';}

                        $html_body .= '<td style="padding: 0.5em;font-size: 13px;"><a style="font-size: 13px;color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/advDetail/'.$value->adv_id.'/adv/browse?pdf=1">'.$value->company_name.'</a></td>';

                       $html_body .= '<td style="padding: 0.5em ;font-size: 13px;"><a style="font-size: 13px;color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix/creativeDetail/'.$value->creative_id.'/'.$l.'/browse?pdf=1">'.$value->creative_name.'</a></td>';
                        $html_body .= '<td><a href="http://'.HOST.'/drmetrix/video/'.$value->video.'?pdf=1">
                                              <img width="60%" src="http://www.drmetrix.com/images/playbutton.png">
                                            </a>
                                        </td>';                        
                        $html_body .= '<td style="padding: 0.5em;font-size: 13px;"><a style="font-size: 13px;color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1">'.$value->main_category.'</a></td>';
                        $html_body .= '<td style="padding: 0.5em;font-size: 13px;"><a style="font-size: 13px;color: #2fcaff !important; text-decoration: none !important;" href="http://'.HOST.'/drmetrix?pdf=1">'.$value->main_sub_category.'</a></td>';
                     $html_body .='</tr>';
		              $i++;                    
                }               
            }  
            if(!empty($this->new_brands) || !empty($this->existing_brands)){
              $html_body .='</tbody></table> </center></td></tr>';
            }
        }
        return $html_body;
    }
    
    public function  newCreatives($brand_array){
        //New creatives added into brand.
          $html_body='';
          $brandIds = implode(",",$brand_array);
            $sql = "SELECT b.brand_id,b.brand_name,cat.category_id,cat.main_category,cat.main_sub_category,a.adv_id,a.company_name,c.creative_id,c.creative_name,c.first_detection,c.length,c.video FROM brand b INNER JOIN creative c  ON b.brand_id = c.brand_id  INNER JOIN category cat ON cat.brand_id = b.brand_id RIGHT JOIN advertiser a ON a.adv_id = b.adv_id WHERE b.brand_id  IN (".$brandIds.") AND c.first_detection LIKE '%".$this->date."%' GROUP by b.brand_id";
           $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $this->result = $stmt->fetchAll(PDO::FETCH_OBJ);
          
              
        if(!empty($this->result)){
            $html_body .= '<tr>
                          <td valign="top" style="display:block"><center style=""><br>&nbsp;<br></center></td>
                        </tr>
                        <tr>        
                        <td valign="top" style="/* background-color:#f8f8f8; *//* border-bottom:1px solid #e7e7e7; */">
                          <center>
                               <div class="w320" style="
    width: 600px;
    /* height: 31px; */
    background-color: #003344;
    color: #fff;
    line-height: normal;
    -moz-border-radius: 5px 5px 0 0; -webkit-border-radius: 5px 5px 0 0; border-radius: 5px 5px 0 0;
"><br>&nbsp;</br>New Creative Alerts for Brands Tracked<br>&nbsp</div>
                              <table border="0" cellpadding="0" cellspacing="0" width="600" class="w320" style="height:100%;">
                                <tbody>
                                <tr style="text-align: center;">
                                  <td style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Brand</td>
    <td colspan="2" style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Creative</td>
    <td style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Advertiser</td>
    <td style="border-right: 2px solid #CACACA;font-size: 16px;text-align: center;">Category</td>
    <td style="font-size: 16px;text-align: center;">Sub-category</td>
                                </tr>
                                <tr>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                                  <td style="padding: 0.5em;"></td>
                                </tr>';
            $i = 1;
            foreach($this->result as $key => $value){
              if($i % 2 == 0){$class = 'background-color: #fff;';}else{$class = 'background-color: #EFEFEF;';}
                $html_body .= '<tr style= '.$class.'>';
                  $html_body .= '<td style="padding: 0.5em;font-size:13px;">
                                  <a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix/brandDetail/'.$value->brand_id.'/brand/browse?pdf=1">'.$value->brand_name.'</a>
                                </td>';
                if($value->length <= 120){$l = 'short';}else{$l = 'long';}

                  $html_body .= '<td style="padding: 0.5em;font-size:13px;">
                                  <a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix/creativeDetail/'.$value->creative_id.'/'.$l.'/browse?pdf=1">'.$value->creative_name.'</a>
                                </td>';
                  $html_body .= '<td>
                                  <a href="http://'.HOST.'/drmetrix/video/'.$value->video.'?pdf=1"><img width="60%" src="http://www.drmetrix.com/images/playbutton.png"></a>
                                </td>';
                  $html_body .= '<td style="padding: 0.5em;font-size:13px;font-size: 13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix/advDetail/'.$value->adv_id.'/adv/browse?pdf=1">'.$value->company_name.'</a></td>';
                  $html_body .= '<td style="padding: 0.5em;font-size:13px;font-size: 13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix?pdf=1">'.$value->main_category.'</a></td>';
                  $html_body .= '<td style="padding: 0.5em;font-size:13px;font-size: 13px;"><a style="color: #2fcaff !important; text-decoration: none !important;font-size: 13px;" href="http://'.HOST.'/drmetrix?pdf=1">'.$value->main_sub_category.'</a></td>';
                $html_body .='</tr>';
                $i++;
              }
           $html_body .='</tbody></table></center></td></tr>';
        }else{
            //$html_body = '<tr><td style="padding: 0.5em;text-align:center;" colspan="6">No records found.</td></tr>';
        }
        
        return $html_body;
    } 
        
    public function brandsDetected($subcategories){
       $subcategoryIds =  implode(",",$subcategories);
       $sql = "SELECT b.brand_id,b.brand_name,cat.category_id,cat.main_category,cat.main_sub_category,a.adv_id,a.company_name,c.creative_id,c.creative_name,c.first_detection,c.length,c.video FROM brand b INNER JOIN creative c  ON b.brand_id = c.brand_id  INNER JOIN category cat ON cat.brand_id = b.brand_id RIGHT JOIN advertiser a ON a.adv_id = b.adv_id WHERE cat.category_id  IN (".$subcategoryIds.") AND b.first_detection LIKE '%".$this->date."%' GROUP by b.brand_id";
       
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        return $result;
    }
    
    
    public function existingBrands($subcategories){
        $subcategoryIds =  implode(",",$subcategories);
        $sql = "SELECT b.brand_id,b.brand_name,cat.category_id,cat.main_category,cat.main_sub_category,a.adv_id,a.company_name,c.creative_id,c.creative_name,c.first_detection,c.length,c.video FROM brand b INNER JOIN creative c  ON b.brand_id = c.brand_id  INNER JOIN category cat ON cat.brand_id = b.brand_id RIGHT JOIN advertiser a ON a.adv_id = b.adv_id WHERE cat.category_id  IN (".$subcategoryIds.") AND b.first_detection != '".$this->date."' AND c.first_detection LIKE '".$this->date."' GROUP by b.brand_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        return $result;
    }
}
