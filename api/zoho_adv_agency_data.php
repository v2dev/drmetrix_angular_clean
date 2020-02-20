<?php
//Cron is in used, runs at 2 PM
//Cron process
/**
 * 1) Set Upload Logo to true for account who is having ADS ACCT ID set in ZOHO
 * 2) Run cron, then all temp files related to advertisers and agency reloaded and isnerted to final tables from temp tables.
 * 3) If Upload logo is true then , logo is saved to respective folder eg www/html/qa/account_logo respectively with server name 
 * 4) After logo saved to directory, upload logo set to false.
 */
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/../zoho_crm/functions.php';
if(php_sapi_name() == 'cli') {
    if (!isset($argv[1])) {
      echo 'prod not set correctly';
      exit;
    }
    parse_str($argv[1], $params);
    $execute_queries_on_production  = $params['prod'];
} else {
    $execute_queries_on_production  = $_GET['prod'];
    echo 'Script cannot be exeuted from GUI';
    exit;
}

$to = 'pravin.sapkal@v2solutions.com';
$message = 'Advertiser Detail Cron started on ' . date("Ymd H:i:s");
$api_to_limit = 200;
$index_array_contact = $index_array_account = array();
ignore_user_abort();
$subject = 'Advertiser Detail Cron started on ' . date("Y-m-d H:i:s");
custom_email($to, $subject, $message);
echo "Start-->" . date('h:i:s A') . PHP_EOL;
for ($i = 1; $i <= ZOHO_ACCOUNT_COUNT; $i = $i + $api_to_limit) {
    $index_array_account[$i] = $i + ($api_to_limit - 1);
}
getAllAdvAgencyData($index_array_account);
echo "End-> Adv - Agency- Data-->" . date('h:i:s A') . PHP_EOL;
$max_zoho_account_count = 200000;
for ($i = 1; $i <= $max_zoho_account_count; $i = $i + $api_to_limit) {
    $index_array[$i] = $i + ($api_to_limit - 1);
}
getAllAgency($index_array);
echo "End--> Adv - Agency- Mapping-->" . date('h:i:s A') . PHP_EOL;
for ($i = 1; $i <= ZOHO_CONTACT_COUNT; $i = $i + $api_to_limit) {
    $index_array_contact[$i] = $i + ($api_to_limit - 1);
}
getAllContacts($index_array_contact);
echo "End-> Contacts- Data-->" . date('h:i:s A') . PHP_EOL;
echo "End-> ".date('h:i:s A').PHP_EOL;
echo "done";
$subject = 'Advertiser Detail Cron ended on '. date("Y-m-d H:i:s");

  
if ($execute_queries_on_production == 1) {
    echo 'prod';
    final_insert('drmetrix_production');
    echo "End-> On Production -->".date('h:i:s A').PHP_EOL;
} else{
    echo 'staging';
    final_insert('drmetrix_staging');
    echo "End-> On drmetrix_staging -->".date('h:i:s A').PHP_EOL;
}

custom_email($to, $subject, $message);
function getAllAdvAgencyData($index_array)
{
    echo 'Agency start';
    $db = getConnection();
    $sql_tbl_empty = "TRUNCATE temp_zoho_adv_and_agency_details";
    $stmt = $db->prepare($sql_tbl_empty);
    $stmt->execute();
    $page = 0;
    do {
        $page++;
        $adv_data = '';
        $module = 'Accounts';
        $query_string = "page={$page}&criteria=(Add_in_Adsphere:true)&sort_by=Account_Name&sort_order=desc";
        $response = call_zoho_api($module, $query_string, 'GET');
        $response = json_decode($response);
        $more_records = isset($response->info) ? $response->info->more_records : 0;
        if (isset($response->data)) {
            foreach ($response->data as $key => $value) {
                $record_array = array();
                /*if($value->id == '2033612000000577308'){
                    print_r($value); exit;
                }*/
                $buys_in_house = 0;
                if($value->Add_in_Adsphere){
                    $record_array['advertiser']['zoho_account_id']      = $value->id;
                    $record_array['advertiser']['account_name']         = replaceSpacialChar($value->Account_Name);
                    $record_array['advertiser']['ads_acct_id']          = $value->Adsphere_Acct_ID;
                    if($value->New_Facebook_Co) $record_array['advertiser']['facebook']             = validateUrl($value->New_Facebook_Co);
                    if($value->New_Twitter_Co) $record_array['advertiser']['twitter']              = validateUrl($value->New_Twitter_Co);
                    if($value->New_LinkedIn_Co) $record_array['advertiser']['linkedin']             = validateUrl($value->New_LinkedIn_Co);
                    if($value->New_Google_Co) $record_array['advertiser']['google_plus']          = validateUrl($value->New_Google_Co);
                    $record_array['advertiser']['phone']                = $value->Phone;
                    $record_array['advertiser']['street']               = replaceSpacialChar($value->Street);
                    $record_array['advertiser']['city']                 = replaceSpacialChar($value->City);
                    $record_array['advertiser']['state']                = replaceSpacialChar($value->States);
                    $record_array['advertiser']['zip']                  = $value->ZIP1;
                    $record_array['advertiser']['country']              = replaceSpacialChar($value->Countries);
                    $record_array['advertiser']['website']              = replaceSpacialChar($value->Website);
                    $record_array['advertiser']['add_in_ads']           = $value->Add_in_Adsphere;
                    $record_array['advertiser']['ads_dsiplay_name']     = replaceSpacialChar($value->ADS_Display_Name);
                    $record_array['advertiser']['update_logo']          = $value->Update_Logo;
                    $ads_acct_id                                        = 'NULL';
                    if ($value->Buys_In_House == 'true' || $value->Buys_In_House == '1') {
                        $buys_in_house = 1;
                    }
                }
                if (!empty($record_array['advertiser']['ads_acct_id'])) {
                    $ads_acct_id = $record_array['advertiser']['ads_acct_id'];
                    if (isset($record_array['advertiser']['update_logo']) && $record_array['advertiser']['update_logo'] == true) {
                        adv_images($record_array['advertiser']['zoho_account_id'], $ads_acct_id);
                        updateLogoFieldInZoho($record_array['advertiser']['zoho_account_id']);
                    }
                }

                if($value->Add_in_Adsphere){
                    $adv_data .= "(" . $ads_acct_id . ",'" . $record_array['advertiser']['zoho_account_id'] . "','" . serialize($record_array) . "','" . standardDateTimeFormat('Y-m-d H:i:s') . "', ".$buys_in_house.")" . ',';
                }
            }
            if (!empty($adv_data)) {
                $sql = "INSERT INTO `temp_zoho_adv_and_agency_details`(`adv_id`, `zoho_id`, `data`, `created_date`, `buys_in_house`) VALUES " . rtrim($adv_data, ',');
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
        }
    } while ($more_records == 1);
    echo 'Agency end';
    closeConnection();
}
function getAllAgency($index_array)
{
    $sql_tbl_empty = "TRUNCATE temp_zoho_adv_agency_mapping";
    execute_sql($sql_tbl_empty);
    $page   = 0;
    $i      = 0;
   
    do {
        $page++;
        $agency_data = '';
        $module = 'Associations';
        $query_string = "page={$page}&sort_by=adv_id&sort_order=desc";
        $response = call_zoho_api($module, $query_string, 'GET');
        $response = json_decode($response);
        $more_records = isset($response->info) ? $response->info->more_records : 0;
        if (isset($response->data)) {
            foreach ($response->data as $key => $value) {
                $arr_agency = array();
                if( isset($value->Advertiser_Account) && isset($value->Agency) ) {
                    $arr_agency['adv_id']           = $value->Advertiser_Account->id;
                    $arr_agency['adv_name']         = replaceSpacialChar($value->Advertiser_Account->name);
                    $arr_agency['agency_id']        = $value->Agency->id;
                    $arr_agency['agency_name']      = validateUrl($value->Agency->name);
                    if (!empty($arr_agency)) {
                        $agency_data .= "('" . $arr_agency['adv_id'] . "','" . $arr_agency['agency_id'] . "','" . standardDateTimeFormat('Y-m-d H:i:s') . "')" . ',';
                    }
                }
            }
            if (!empty($agency_data)) {
                $sql = "INSERT INTO `temp_zoho_adv_agency_mapping`(`zoho_adv_id`, `zoho_agency_id`, `created_date`) VALUES " . rtrim($agency_data, ',');
                execute_sql($sql);
            }
        }
    } while ($more_records == 1);
}
function getAllContacts($index_array)
{
    $db = getConnection();
    $sql_tbl_empty = "TRUNCATE temp_zoho_contacts";
    $stmt = $db->prepare($sql_tbl_empty);
    $stmt->execute();
    $page = 0;
   
    do {
        $page++;
        $module     = 'Contacts';
        $contact_data = '';
        $query_string = "page={$page}&criteria=(Add_in_Adsphere:true)&sort_by=Account_Name&sort_order=desc";
        $response = call_zoho_api($module, $query_string, 'GET');
        $response = json_decode($response);
        $more_records = isset($response->info) ? $response->info->more_records : 0;
        if (isset($response->data)) {
            foreach ($response->data as $key => $value) {
                $contact_arr = array();
                $contact_arr['contact_id']      = $value->id;
                if( isset($value->Account_Name) && isset($value->Account_Name->id) ){
                    $contact_arr['account_id']      = $value->Account_Name->id;
                }
                $contact_arr['first_name']      = replaceSpacialChar($value->First_Name);
                $contact_arr['last_name']       = replaceSpacialChar($value->Last_Name);
                $contact_arr['email']           = addslashes($value->Email);
                if($value->New_Facebook_Pers) $contact_arr['facebook']        = validateUrl($value->New_Facebook_Pers);
                if($value->New_Twitter_Pers) if($value->New_Twitter_Pers) $contact_arr['twitter']         = validateUrl($value->New_Twitter_Pers);
                if($value->New_LinkedIn_Pers) $contact_arr['linkedin']        = validateUrl($value->New_LinkedIn_Pers);
                if($value->New_Google_Pers) $contact_arr['google_plus']     = validateUrl($value->New_Google_Pers);
                $contact_arr['fax']             = $value->Fax;
                $contact_arr['mobile']          = $value->Phone;
                $contact_arr['street']          = $value->Street;
                $contact_arr['city']            = $value->City;
                $contact_arr['state']           = $value->States;
                $contact_arr['zip']             = $value->P;
                $contact_arr['Country']         = $value->Countries;
                $contact_arr['title']           = $value->Title;
                if($value->Web_Link_profile) $contact_arr['profile_link']    = $value->Web_Link_profile;
                if (isset($contact_arr['account_id']) && !empty($contact_arr) && $value->Add_in_Adsphere == true) {
                    $contact_data .= "('" . $contact_arr['contact_id'] . "','" . $contact_arr['account_id'] . "','" . addslashes(serialize($contact_arr)) . "','" . standardDateTimeFormat('Y-m-d H:i:s') . "')" . ",";
                }
            }
            if (!empty($contact_data)) {
                $sql = "INSERT INTO `temp_zoho_contacts`(`zoho_id`, `zoho_owner_id`, `data`, `created_date`) VALUES " . rtrim($contact_data, ',');
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
        }
    } while ($more_records == 1);
    closeConnection();
}
function final_insert($db_name)
{
    $db = getConnection();
    //----For Zoho Adv and Agency details
    $sql_tbl_empty = "TRUNCATE $db_name.zoho_adv_and_agency_details";
    $stmt = $db->prepare($sql_tbl_empty);
    $stmt->execute();
    $sql = "INSERT INTO $db_name.`zoho_adv_and_agency_details`(`id`, `adv_id`, `zoho_id`, `data`, `created_date`, `buys_in_house`) SELECT * FROM $db_name.temp_zoho_adv_and_agency_details";
    $stmt = $db->prepare($sql);
    $stmt->execute(); //----For Zoho Adv and Agency mappings
    $sql_tbl_empty = "TRUNCATE $db_name.zoho_adv_agency_mapping";
    $stmt = $db->prepare($sql_tbl_empty);
    $stmt->execute();
    $sql = "INSERT INTO $db_name.`zoho_adv_agency_mapping`(`id`, `zoho_adv_id`, `zoho_agency_id`, `created_date`) SELECT * FROM $db_name.temp_zoho_adv_agency_mapping";
    $stmt = $db->prepare($sql);
    $stmt->execute(); //----For Zoho Adv and Agency mappings
    $sql = "insert into zoho_adv_agency_mapping(zoho_adv_id, zoho_agency_id, created_date) select zoho_id, zoho_id, now() from temp_zoho_adv_and_agency_details where buys_in_house = 1;";
    $stmt = $db->prepare($sql);
    $stmt->execute(); //----For Zoho Adv and Agency mappings
    //----For Zoho Contacts
    $sql_tbl_empty = "TRUNCATE $db_name.zoho_contacts";
    $stmt = $db->prepare($sql_tbl_empty);
    $stmt->execute();
    $sql = "INSERT INTO $db_name.`zoho_contacts`(`id`, `zoho_id`, `zoho_owner_id`, `data`, `created_date`) SELECT * FROM $db_name.temp_zoho_contacts";
    $stmt = $db->prepare($sql);
    $stmt->execute(); //----For Zoho Adv and Agency mappings
    closeConnection();
}
function adv_images($adv_zoho_id, $adv_ads_id)
{
    $records = getRelatedRecordsByModuleId('Accounts', $adv_zoho_id, 'Attachments');
    if (!empty($records) && isset($records->data[0]->id)) {
        $records = downloadRelatedRecords('Accounts', $adv_zoho_id, 'Attachments', $records->data[0]->id);
        save_remote_image($records, $adv_ads_id);
    }
}
function save_remote_image($source, $id)
{
    global $execute_queries_on_production;
    if($execute_queries_on_production == 1){
        $destination = '/www/html/account_logo/';
    } else {
        $destination = '/www/html/staging/account_logo/'; 
    }
    
    $destination    = $destination . $id.'.png';
    $file           = fopen($destination, "w+");
    fputs($file, $source);
    fclose($file);
}
function validateUrl($url)
{
    $url = str_replace(array('http://', 'https://'), '', $url);
    return urlencode( ($url ? 'https://' : '') . $url);
}
function replaceSpacialChar($content_info)
{
    return str_ireplace("'", "&apos;", $content_info);
}
function updateLogoFieldInZoho($zoho_account_id)
{
    $updateFields = array(
        "Update_Logo" => false,
    );
    $updateResponse = updateRecordInZoho('Accounts', $zoho_account_id, $updateFields);
    $updateResponse = zoho_exception_log($updateResponse, 'APIManageZOHOAccount - Update_Logo');
}
//API for Advertiser images-----------------------------
//https://crm.zoho.com/crm/private/xml/Attachments/getRelatedRecords?authtoken=2af6b5f76605e95016ded9a9770eb082&newFormat=1&scope=crmapi&parentModule=Accounts&id=1904805000005956857
//https://crm.zoho.com/crm/private/xml/Accounts/downloadFile?authtoken=2af6b5f76605e95016ded9a9770eb082&scope=crmapi&id=1904805000005996254
//----------------------------------------
