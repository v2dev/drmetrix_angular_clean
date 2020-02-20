<?php

class Slim_App_Lib_Common {
    //public $last_week;
    public $dbh;
    private function __construct() {
        
    }

    public static function sendMailWithAttachement($db,$file_name,$type,$form) {
        $address = json_encode(self::getAddress($db));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".HOST."/drmetrix/testmail/email.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        $data = array('filename' => $file_name, 'type' => $type,'form' => $form,'address' => $address);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($ch);
        if($response){
           echo "<br>Mail sent";
        }else{
           echo '<br>There is problem while sending mail';
        }
        curl_close($ch);    
    } 
     public static function getAddress($db){
        $sql = "SELECT concat(first_name,last_name) as name,email FROM user WHERE receive_report = 1 AND status='active'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        return $result;
    }
    
    public static function sendMail($db,$email,$name,$html,$subject){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".HOST."/drmetrix/alerts/daily.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        $data = array('name' => $name,'address' => $email,'html'=>$html,'subject'=>$subject);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($ch);
        if($response){
           echo "<br>Mail sent";
           $_SESSION['sent'] = 1; 
           $_SESSION['triggered_on'] = date('Y-m-d h:i:s');
        }else{
           echo '<br>There is problem while sending mail';
           $_SESSION['sent'] = 0; 
           $_SESSION['triggered_on'] = date('Y-m-d h:i:s');
        }
        
        curl_close($ch);    
    }
    

    public static function weeks_in_year($db,$last_week){
         $sql = "SELECT media_week FROM media_calendar WHERE media_week_start = '".$last_week['sd']."' AND media_week_end = '".$last_week['ed']."'";
        $stmt = $db->prepare($sql);
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($result)){ return $result[0]['media_week'];}
            
         }
    }
    public static function weeks_in_month() {
       /* $month = date('m');
        $year = date('Y');
        $weeks = 0;
        
        for($i = 1; $i <= $month ; $i++){
            $start = mktime(0, 0, 0, $i, 1, $year);
            $end = mktime(0, 0, 0, $i, date('t', $start), $year);
            $start_week = date('W', $start);
            $end_week = date('W', $end);

            if ($end_week < $start_week) { 
                $weeksInYear = 52;
                if($year % 4 == 0) {
                     $weeksInYear = 53;
                }
              $weeks =  $weeks + ((($weeksInYear + $end_week) - $start_week) );
            }

            $weeks = $weeks + (($end_week - $start_week) );
        }
        
        return $weeks;*/
         //Get the first day of the month.
        $year = date('Y');

        $week_count = date('W', strtotime($year . '-12-31'));

        if ($week_count == '01')
        {   
            $week_count = date('W', strtotime($year . '-12-24'));
        }
        $remainingWeeks = ($week_count - date('W'));
        $weekNumber = $week_count - $remainingWeeks;
        return $weekNumber;
       }
       
       public static function randomVideos(){
            $a=array("7452270","7407493","7260779");
            $random_keys=array_rand($a);
            return $a[$random_keys];
       }
       
       //short airings
       public  static function totalAiringsAdv($db,$last_week,$adv_id){
         $sql = "SELECT b.adv_id as ID,count(a.creative_id) no_of_airings FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$last_week['sd']." 00:00:00' AND '".$last_week['ed']." 00:00:00' AND a.length <= 120 AND b.adv_id = '".$adv_id."'";
        $stmt = $db->prepare($sql);
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['no_of_airings'];
            
         }
       }
         
        public static function totalAiringsBrand($db,$last_week,$brand_id){
        $sql = "SELECT b.brand_id as ID,count(a.creative_id) no_of_airings FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$last_week['sd']." 00:00:00' AND '".$last_week['ed']." 00:00:00' AND a.length <= 120 AND b.brand_id = '".$brand_id."'";
        $stmt = $db->prepare($sql);
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['no_of_airings'];
            
         }
         
       }
       
       //total airing for a adv
        public  static function totalAiringsAdvertise($db,$last_week,$adv_id){
        $sql = "SELECT b.adv_id as ID,count(a.creative_id) no_of_airings FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$last_week['sd']." 00:00:00' AND '".$last_week['ed']." 00:00:00' AND b.adv_id = '".$adv_id."'";
        $stmt = $db->prepare($sql);
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['no_of_airings'];
            
         }
       }
       
        //total airing for a brands
        public  static function totalAiringsBrands($db,$last_week,$brand_id){
        $sql = "SELECT b.brand_id as ID,count(a.creative_id) no_of_airings FROM airings a, brand b, creative c WHERE a.creative_id = c.creative_id and b.brand_id = c.brand_id AND a.end BETWEEN '".$last_week['sd']." 00:00:00' AND '".$last_week['ed']." 00:00:00' AND b.brand_id = '".$brand_id."'";
        $stmt = $db->prepare($sql);
        //exit;
         if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['no_of_airings'];
            
         }
       }
       

       //retrieve last seven days
       public static function last_seven_days() {
           $current_week['sd'] =  date('M j, Y', strtotime('-7 days'));
           $current_week['ed'] =  date('M j, Y', strtotime('-1 days'));
           
           $current_week['db_sd'] =  date('Y-m-d', strtotime('-7 days'));
           $current_week['db_ed'] =  date('Y-m-d', strtotime('-1 days'));
           return $current_week;
       }
       

       //last media month
       public static function getLastMediaMonth(){
        $dbh = getConnection();
        
        /*if((MONTHFROMDATE == '') && (MONTHTODATE == '')){
            $prev_month = (date('m')-1);
            $year = date('Y');
            if($prev_month == 12){
                $year = $year - 1;
            }
            
             $sql = "SELECT media_month_start,media_month_end,media_month FROM media_calendar WHERE media_month = ".$prev_month." AND media_year =".$year;
             $stmt = $dbh->prepare($sql);
             $stmt->execute();
             $result = $stmt->fetchAll(PDO::FETCH_OBJ);
           
             foreach($result as $k => $v){
                 if($k == 0){
                      $sd = $v->media_month_start;
                 }
                 $ed = $v->media_month_end;
                 $calendar_id = $v->media_month;
             }
             $this_month['sd'] = $sd;
             $this_month['ed'] = $ed;
             $this_month['calendar_id'] = date("M",strtotime($ed));
             $this_month['start_date'] = date("m/d/Y",strtotime($sd));
             $this_month['end_date'] =  date("m/d/Y",strtotime($ed));
             
           // $this_month_sd = date('Y-m-d', strtotime('first day of last month'));
          //  $this_month_ed = date('Y-m-d', strtotime('last day of last month'));
          //  $this_month['sd'] = $this_month_sd;
          //  $this_month['ed'] = $this_month_ed;
            
          //  $this_month['start_date'] = date("M, Y",strtotime($this_month_sd));
          //  $this_month['end_date'] =  date("M, Y",strtotime($this_month_ed));
            
        }else{
            $this_month['sd'] = MONTHFROMDATE;
            $this_month['ed'] = MONTHTODATE;
            
            $this_month['start_date'] =  'Sept, 2015';
            $this_month['end_date'] =  'Sept, 2015';
        }
        */
        
        $date_range = getPreviousDates('month', 1, '', 'current');
         if($date_range['calendar_id'] == 12){
            $prev_year = (date('Y')-1);
            $sql = "SELECT MIN(media_month_start) as min_date,MAX(media_month_end) as max_date FROM media_calendar WHERE media_year = ".$prev_year." AND media_month =".$date_range['calendar_id'];
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            $date_range = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $date_range = $date_range[0];
        }
        //show($date_range, 1);
        $this_month['sd'] = $date_range['min_date'];
        $this_month['ed'] = $date_range['max_date'];
        $this_month['calendar_id'] = date("M",strtotime( $date_range['max_date']));
        
        $this_month['start_date'] =  date("m/d/Y",strtotime($date_range['min_date']));
        $this_month['end_date'] =  date("m/d/Y",strtotime($date_range['max_date']));
       // return $this_week;
        return $this_month;  
      }      
      //last media month

       public static function getCurrentMediaMonth(){
        $dbh = getConnection();
        
        $date_range = getPreviousDates('month', 1, '', '');
        $this_month['sd'] = $date_range['min_date'];
        $this_month['ed'] = $date_range['max_date'];
        $this_month['calendar_id'] = date("M",strtotime( $date_range['max_date']));
      }        
      //last media month
      public static function getLastMediaYear(){
        $dbh = getConnection();
             $prev_year = (date('Y')-1);             
             $sql = "SELECT media_year_start,media_year_end FROM media_calendar WHERE media_year = ".$prev_year;
             $stmt = $dbh->prepare($sql);
             $stmt->execute();
             $result = $stmt->fetchAll(PDO::FETCH_OBJ);
           
             foreach($result as $k => $v){
                 if($k == 0){
                      $sd = $v->media_year_start;
                 }
                 $ed = $v->media_year_end;
             }
             $this_year['sd'] = $sd;
             $this_year['ed'] = $ed;
             $this_year['year'] = date("Y",strtotime($ed));
             //$this_year['end_date'] =  date("M, Y",strtotime($ed));
             
           // $this_month_sd = date('Y-m-d', strtotime('first day of last month'));
          //  $this_month_ed = date('Y-m-d', strtotime('last day of last month'));
          //  $this_month['sd'] = $this_month_sd;
          //  $this_month['ed'] = $this_month_ed;
            
          //  $this_month['start_date'] = date("M, Y",strtotime($this_month_sd));
          //  $this_month['end_date'] =  date("M, Y",strtotime($this_month_ed));
        return $this_year;  
      }

      public static function getLastMediaWeek(){
        $dbh = getConnection();
        
       /* if((FROMDATE == '') && (TODATE == '')){
            $monday = strtotime("Monday last week");
           // $current_date_week = customDate('W');
            $monday = date('W', $monday)==date('W') ? $monday-7*86400 : $monday;
            $sunday = strtotime(date("Y-m-d",$monday)." +6 days");
            $this_week_sd = date("Y-m-d",$monday);
            $this_week_ed = date("Y-m-d",$sunday);
            $this_week['sd'] = $this_week_sd;
            $this_week['ed'] = $this_week_ed;
           
            
            $this_week['start_date'] =  date("m/d/Y",strtotime($this_week_sd));
            $this_week['end_date'] =  date("m/d/Y",strtotime($this_week_ed));
        }else{
            $this_week['sd'] = FROMDATE;
            $this_week['ed'] = TODATE;
            
            $this_week['start_date'] =  'Oct 10, 2015';
            $this_week['end_date'] =  'Oct 17, 2015';
        }
        
        $sql = "SELECT media_week_start,media_week_end,media_week FROM media_calendar WHERE media_week_start = '".$this_week_sd."' AND media_week_end = '".$this_week_ed."'";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $this_week['calendar_id'] = $result[0]->media_week;
        
        return $this_week;*/
        $date_range = getPreviousDates('week', 1, '', 'current');
        $this_week['sd'] = $date_range['min_date'];
        $this_week['ed'] = $date_range['max_date'];
        $this_week['calendar_id'] = $date_range['calendar_id'];

        $this_week['start_date'] =  date("m/d/Y",strtotime($date_range['min_date']));
        $this_week['end_date'] =  date("m/d/Y",strtotime($date_range['max_date']));
        return $this_week;
    }
    
    
    public static function export($header,$result) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://".HOST."/drmetrix/testmail/export.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        $data = array('header' => $header, 'result' => $result);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($ch);
        echo $respose;
        curl_close($ch);    
    }  
}
