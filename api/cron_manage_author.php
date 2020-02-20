<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';

$index_array = array('1'=>'200','201'=>'400','401'=>'600','601'=>'800','801'=>'1000','1001'=>'1200','1201'=>'1400','1401'=>'1600','1601'=>'1800','1801'=>'2000','2001'=>'2200','2201'=>'2400','2401'=>'2600','2601'=>'2800','2801'=>'3000','3001'=>'3200','3201'=>'3400','3401'=>'3600','3601'=>'3800','3801'=>'4000','4001'=>'4200','4201'=>'4400','4401'=>'4600','4601'=>'4800','4801'=>'5000','5001'=>'5200','5201'=>'5400','5401'=>'5600','5601'=>'5800','5801'=>'6000');
//ignore_user_abort();

$adv_list = getAllAdvAgencyData($index_array);
getAllContacts($index_array,$adv_list);

function curl_param_account($from,$to){
  $param = array();
  $param['url'] = "https://crm.zoho.com/crm/private/json/Accounts/searchRecords";
  $param['query'] = "authtoken=".ZOHO_APIKEY."&scope=crmapi&criteria=(Add in Adsphere:true)&selectColumns=Accounts(Account Name,Author)&fromIndex=".$from."&toIndex=".$to;
  return $param; 
}

function getAllAdvAgencyData($index_array){
  $i=0;
  $record_array = array();
  foreach ($index_array as $ikey => $ivalue) {
    $params = curl_param_account($ikey,$ivalue);
    $adv_list = process_curl($params['url'], $params['query']); 
    $array_adv_details = array();
    $adv_data ='';
    if(isset($adv_list['response']['result']['Accounts']['row']) || !empty($adv_list['response']['result']['Accounts']['row'])){      
      foreach ($adv_list['response']['result']['Accounts']['row'] as $key => $value) {        
        foreach ($value['FL'] as $ky => $val) {
          if($val['val'] == 'ACCOUNTID'){
              $record_array[$i]['zoho_account_id'] = $val['content'];
          }else if($val['val'] == 'Author'){
              $record_array[$i]['author'] = $val['content'];
          }
        } 
        $i++;   
      } 
    }
  }
  $adv_final = array();
  foreach ($record_array as $value) {
    if(isset($value['zoho_account_id']) && isset($value['author']))
      $adv_final[$value['zoho_account_id']] = $value['author'];
  }
  return $adv_final;
}


function curl_param_contacts($from,$to){
  $param = array();
  $param['url'] = "https://crm.zoho.com/crm/private/json/Contacts/searchRecords";
  $param['query'] = "authtoken=".ZOHO_APIKEY."&scope=crmapi&criteria=(Add in Adsphere:true)&selectColumns=Contacts(Email,Author,Account Name)&fromIndex=".$from."&toIndex=".$to;
  return $param; 
}

function getAllContacts($index_array,$adv_list){
  $contact_arr = array();    
  $i=0;       
  foreach ($index_array as $ikey => $ivalue) {
    $params = curl_param_contacts($ikey,$ivalue);
    $adv_contacts_list = process_curl($params['url'], $params['query']);
    $contact_data  = '';
    if(isset($adv_contacts_list['response']['result']['Contacts']['row']) || !empty($adv_contacts_list['response']['result']['Contacts']['row'])){    
      foreach ($adv_contacts_list['response']['result']['Contacts']['row'] as $keyC => $valueC) {
        if(isset($valueC['FL']) && !empty($valueC['FL']) && is_array($valueC['FL'])){
          foreach ($valueC['FL'] as $ky => $valc) {    
            if($valc['val'] == 'CONTACTID' ){
              $contact_arr[$i]['contact_id'] =  $valc['content'];
            }else if($valc['val'] == 'ACCOUNTID' ){
              $contact_arr[$i]['account_id'] =  $valc['content'];
            }else if($valc['val'] == 'Email'){
              $contact_arr[$i]['email'] = $valc['content'];
            }
          }
          $i++;
        }
      } 
    }
  }
  foreach ($contact_arr as $key => $value) {
    echo "<pre>".$key; print_r($value);
    if(isset($adv_list[$value['account_id']]))
      $contact_arr[$key]['author'] = $adv_list[$value['account_id']];
    else
      $contact_arr[$key]['author'] = '';
  }
  echo "<pre>"; print_r($contact_arr); exit;
  return $contact_arr;
}


function process_curl($url, $query){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  $response_info = curl_getinfo($ch);
  $response = curl_exec($ch);
  curl_close($ch);
  $result = json_decode($response, true);
  return $result;
}



