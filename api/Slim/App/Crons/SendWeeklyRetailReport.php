<?php
set_time_limit(0);
error_reporting(E_ALL); 
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
ignore_user_abort();

require_once dirname(__FILE__) . '../../../../inc/MCAPI.class.php';

//require_once 'inc/MCAPI.class.php';

class Slim_App_Crons_SendWeeklyRetailReport{
    private $db = NULL;
    private $api_key = API_KEY;
    public $api_obj;
    
    
    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
		$this->api_obj = new MCAPI($this->api_key);
        $this->sendWeeklyReport();
    }

    function getListId($list_name){
    	//get list form Mailchimp
		$getList = $this->api_obj->lists();
		foreach ($getList['data'] as $key => $value) {
			if($value['name'] == $list_name){ //'LeisureTime Report' or 'DRMetrix Report'
				return $value['id'];
			}
		}
    }
    function sendWeeklyReport(){
    	$last_week  = Slim_App_Lib_Common::getLastMediaWeek();
    	$type = 'regular'; 	
		$opts['from_email'] = FROM_EMAIL; 
		$opts['from_name'] = FROM_NAME;		 
		$opts['tracking']=array('opens' => true, 'html_clicks' => true, 'text_clicks' => false);		 
		$opts['authenticate'] = true;
		$opts['analytics'] = array(''); //google'=>'my_google_analytics_key'	
		
		
		//template List
		$types = array('user'=>true, 'gallery'=>true);
		$template_list = $this->api_obj->templates($types);
	 
		if ($this->api_obj->errorCode){
			echo "Unable to Load Templates!";
			echo "\n\tCode=".$this->api_obj->errorCode;
			echo "\n\tMsg=".$this->api_obj->errorMessage."\n";
		} else {			
			$i=0;
			foreach($template_list['user'] as $tmpl){
			    if($tmpl['name'] == 'DRMetrix - Retailer (dynamic)'){
			    	$for_campaign[$i]['type'] = 'drmetrix';
			    	$for_campaign[$i]['template_id'] = $tmpl['id'];
			    	$for_campaign[$i]['user_type'] = 'retailer';
			    	$for_campaign[$i]['list_id'] = $this->getListId("DRMetrix Report");
			    	$for_campaign[$i]['for_subject'] = "DRM";
			    }else if($tmpl['name'] == 'DRMetrix - Marketer (dynamic)'){
			    	$for_campaign[$i]['type'] = 'drmetrix';
			    	$for_campaign[$i]['template_id'] = $tmpl['id'];
			    	$for_campaign[$i]['user_type'] = 'marketer';
			    	$for_campaign[$i]['list_id'] = $this->getListId("DRMetrix Report");
			    	$for_campaign[$i]['for_subject'] = "DRM";
			    }else if($tmpl['name'] == 'LeisureTime - Retailer (dynamic)'){
			    	$for_campaign[$i]['type'] = 'leisuretime';
			    	$for_campaign[$i]['template_id'] = $tmpl['id'];
			    	$for_campaign[$i]['user_type'] = 'retailer';
			    	$for_campaign[$i]['list_id'] = $this->getListId("LeisureTime Report");
			    	$for_campaign[$i]['for_subject'] = "LT";
			    }else if($tmpl['name'] == 'LeisureTime - Marketer (dynamic)'){
			    	$for_campaign[$i]['type'] = 'leisuretime';
			    	$for_campaign[$i]['template_id'] = $tmpl['id'];
			    	$for_campaign[$i]['user_type'] = 'marketer';
			    	$for_campaign[$i]['list_id'] = $this->getListId("LeisureTime Report");
			    	$for_campaign[$i]['for_subject'] = "LT";
			    }
			    $i++;
			}
		}

		//Marketer or Retailer DRMerix / LeisureTime Report--------------------------------------------------------------------

		foreach ($for_campaign as $key => $value) {
			$opts['list_id'] = $value['list_id'];	
			$report_links = '<a href="http://adsphere.drmetrix.com/retailreport/api/index.php/retail_report_short_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$value['user_type'].$last_week['calendar_id'].'_*|DATE:Y|*" target="_blank"> <img alt="DRMetrix&nbsp;Short&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-01.png"></a></td><td mc:edit="long_link"><a href="http://adsphere.drmetrix.com/retailreport/api/index.php/retail_report_long_form?utm_source=*|UTMCODE|*&utm_medium=email&utm_content=html&utm_campaign='.$value['user_type'].$last_week['calendar_id'].'_*|DATE:Y|*" target="_blank"><img alt="DRMetrix&nbsp;Long&nbsp;Form&nbsp;Report" width="70%" src="http://www.drmetrix.com/images/buttonpdf2-02.png"></a>';
			$content = array('html_REPORTLINKS'=>$report_links);
			$opts['template_id'] = $value['template_id'];		
			$opts['subject'] = $value['for_subject'].' Weekly '.ucfirst($value['user_type']).' - Week '.$last_week['calendar_id'];
			$opts['title'] = $value['for_subject'].' Weekly '.ucfirst($value['user_type']).' - Week '.$last_week['calendar_id'];
			$conditions = array();
			$conditions[] = array('field'=>'REPORTTYPE', 'op'=>'like', 'value'=>ucfirst($value['user_type']));
			//segmenats
			$segment_opts = array('match'=>'all', 'conditions'=>$conditions);
			$retval = $this->api_obj->campaignCreate($type, $opts, $content, $segment_opts);		 
			if ($this->api_obj->errorCode){
				echo "Unable to Create New Campaign! for ".$value['user_type']." Weekly ".ucfirst($value['user_type'])." - Week ".$last_week['calendar_id'];
				echo "\n\tCode=".$this->api_obj->errorCode;
				echo "\n\tMsg=".$this->api_obj->errorMessage."\n";
			} else {
				echo "New Campaign ID For ".$value['user_type']." Weekly ".ucfirst($value['user_type'])." - Week ".$last_week['calendar_id']." :".$retval."\n";
				$campSendStatus = $this->api_obj->campaignSendNow($retval);
				echo "<pre>"; print_r($campSendStatus); 
			}
		}		
    }
}
?>