<?php
// $scope = ZohoCRM.modules.contacts.all,ZohoCRM.modules.accounts.all,ZohoCRM.modules.leads.all,ZohoCRM.modules.notes.all,ZohoCRM.modules.attachments.all,ZohoCRM.modules.CustomModule2.all

//ZohoCRM.Modules.ALL
//Zohocrm.Modules.ALL

require_once dirname(__FILE__) . '/../api/config.php';
require_once dirname(__FILE__) . '/../api/constants.php';
require_once dirname(__FILE__) . '/../api/functions.php';
require_once dirname(__FILE__) . '/../api/queries.php';

set_time_limit(0);

$access_token = $_SESSION['access_token'] = ZOHO_ACCESS_TOKEN;

function zoho_api_get_access_and_refresh_token()
{
    get_zoho_constants($zoho_constants_array);
    extract($zoho_constants_array);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => ZOHO_OAUTH_TOKEN_URL . "?" . "code={$CODE}&redirect_uri={$ZOHO_APP_REDIRECT_URL}&client_id={$CLIENT_ID}&client_secret={$CLIENT_SECRET}&grant_type=authorization_code",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
    ));
    $response = curl_exec($curl);
    
    $error = curl_error($curl);
        
    curl_close($curl);
    if ($error) {
        api_exception_log('zoho_crm function.php', 'zoho_api_get_access_and_refresh_token', serialize($error));
    } else {
        $response = json_decode($response);
        
        if (!empty($response->error)) {
            api_exception_log('zoho_crm function.php', 'zoho_api_get_access_and_refresh_token response error', serialize($response->error));
            return false;
        }

        // global $access_token;
        $access_token = $response->access_token;
        $refresh_token = $response->refresh_token;
        $expires_in_sec = $response->expires_in_sec;
        $expiry_time = get_timestamp() + $response->expires_in_sec;

        // UPDATE ACCESS_TOKEN IN DB
        if (!empty($access_token)) {
            $update_access_token_in_db = "UPDATE configs SET value = '" . $access_token . "|||{{{" . $expiry_time . "}}}' WHERE name = 'ZOHO_ACCESS_TOKEN'";
            execute_sql($update_access_token_in_db);
        }

        // UPDATE REFRESH_TOKEN IN DB
        if (!empty($refresh_token)) {
            $update_refresh_token_in_db = "UPDATE configs SET value = '" . $refresh_token . "' WHERE name = 'ZOHO_REFRESH_TOKEN'";
            execute_sql($update_refresh_token_in_db);
        }

        return $response;
    }
}

function createDataArrayForZoho($user_details){
    
    if(!isset($user_details['activation_link'])){
        $user_details['activation_link'] =  '';
    }

    if($user_details['client'] == 'Yes') {
        $user_details['client'] = true;
    }else if ($user_details['client'] == 'No'){
        $user_details['client'] = false;
    }

    if($_SERVER['HTTP_HOST'] == 'localhost') {
        $activation_link = 'adsphere.drmetrix.com';
    } else {
        $activation_link = urldecode($user_details['activation_link']);
    }

    $insertFields = array(
        // "Account_Name"      => urldecode($user_details['company_name']),
        "Account_Name"      => $user_details['zoho_company_id'],
        "Email"             => addslashes($user_details['username']),
        "First_Name"        => addslashes($user_details['first_name']),
        "Last_Name"         => addslashes($user_details['last_name']),
        "Mobile"            => $user_details['mobile'],
        "ADS_User_Status"   => $user_details['status'],
        "ADS_Username"      => $user_details['username'],
        // "Secondary_Email"   => isset($user_details['secondary_email']) ? addslashes($user_details['secondary_email']) : '',
        "ADS_User_Role"     => $user_details['role'],
        "ADS_Record_ID"     => strval($user_details['ads_record_id']),
        "Company_Type"      => array($user_details['company_type']),
        "Owner"             => array('id' =>  $user_details['contact_owner']),
        // "Activation_Link_2" => $activation_link,
        "Customer"          => $user_details['client'],
        "Assistant_Admin"   => $user_details['assistant_admin'],
    );
    if($activation_link) {
        $insertFields["Activation_Link_2"] = $activation_link;
    }

    if(isset($user_details['secondary_email'])) {
        $insertFields['Secondary_Email'] = addslashes($user_details['secondary_email']);
    }

    return $insertFields;
}

function createCompanyDataArrayForZoho($company_details) {
    $insertFields = array(
//         "Account_Name"      => urldecode($company_details['company_name']),
        "Company_Type"      => array($company_details['company_type']),
        "Company_Size"      => $company_details['company_size'],
        "Annual_Rev"        => $company_details['company_revenue'],
        "Company_Status"    => $company_details['status'],
        "Max_Users_Allowed" => $company_details['users_limit'],
        "ADS_Record_ID"     => strval($company_details['ads_record_id']),
        // "Network_Tab"       => isset($company_details['network_tab']) ? $company_details['network_tab'] : false,
        // "EULA_Overriden"    => isset($company_details['eula_overriden']) ? $company_details['eula_overriden'] : false,
        // "Adsphere_Acct_ID"  => isset($company_details['ads_acct_id']) ? $company_details['ads_acct_id'] : '',
        // "ADS_Authenticated" => isset($company_details['ads_authenticated']) ? $company_details['ads_authenticated'] : false,
        // "Authy_Authenticated" =>isset($company_details['authy_authenticated']) ? $company_details['authy_authenticated'] : false,
        // "ADS_Verified_Date"  => isset($company_details['ads_verified_date']) ? $company_details['ads_verified_date'] : '',
        "Client"            => $company_details['client'],
        "Owner"             => array('id' =>  $company_details['account_owner_zoho_id'])
    );
    if( isset($company_details['company_name']) ) {
        $insertFields['Account_Name'] = urldecode($company_details['company_name']);
    }

    if( isset($company_details['id']) ) {
        $insertFields['id'] = $company_details['id'];
    }

    if(isset($company_details['network_tab'])) {
        $insertFields['Network_Tab'] = $company_details['network_tab'];
    }

    if(isset($company_details['eula_overriden'])) {
        $insertFields['EULA_Overriden'] = $company_details['eula_overriden'];
    }

    if(isset($company_details['ads_authenticated'])) {
        $insertFields['ADS_Authenticated'] = $company_details['ads_authenticated'];
    }
    if(isset($company_details['authy_authenticated'])) {
        $insertFields['Authy_Authenticated'] = $company_details['authy_authenticated'];
    }

    if(isset($company_details['ads_acct_id'])) {
        $insertFields['Adsphere_Acct_ID'] = $company_details['ads_acct_id'];
    }

    if(isset($company_details['ads_verified_date'])) {
        $insertFields['ADS_Verified_Date'] = $company_details['ads_verified_date'];
    }

    return $insertFields;
}

function getMailchimpRequestParams()
{
    $_zmu_detail = $_REQUEST;
    // $_mailchimp_request_detail= array(
    //     'OWNER'=> 'Joseph Gray',
    //     'LNAME' => 'R',
    //     'ZOHOID' => '1904805000011818348',
    //     'REPORTTYPE' => 'Retailer',
    //     'CRMTYPE' => 'Contacts',
    //     'EMAIL' => 'ashwini.rewatkar@v2solutions.com',
    //     'UTMCODE' => '423423bqa',
    //     'FNAME'=> 'Ashwini',
    //     'COMPANY' => 'V2',
    //     'SUBSCRIPTION_STATUS' => 'subscribed'
    // );
    return $_zmu_detail;
}

function createRecordInZoho($module, $fields)
{
    $module = $module;
    $query_string = '';

    $fields = array(
        "data" => array(
            $fields,
        ));

    $response = call_zoho_api($module, $query_string, "POST", json_encode($fields));
    return json_decode($response);
}

function searchUserInZoho($module, $query_string)
{
    $response = call_zoho_api($module, $query_string, "GET");
    return json_decode($response);
}


function deleteBulkRecordsInZoho($module, $zoho_constants_array) {

}

function getRecordByIdInZoho($module, $account_id)
{
    $module = $module.'/'.$account_id;
    $query_string = '';
    $response = call_zoho_api($module, $query_string, "GET");
    return json_decode($response);
}

function getRelatedRecordsByModuleId($module, $record_id, $relatedModule) {
    $module = $module. '/'.$record_id.'/' . $relatedModule;
    $query_string = '';
    $response = call_zoho_api($module, $query_string, "GET");
    return json_decode($response);
}

function downloadRelatedRecords($module, $record_id, $relatedModule, $relatedModuleId) {
    $module = $module. '/'.$record_id.'/' . $relatedModule .'/'. $relatedModuleId;
    $query_string = '';
    $response = call_zoho_api($module, $query_string, "GET");
    return $response;
}

function createNotesForModule($module, $fields) {
    $module = $module;
    $query_string = '';

    $fields = array(
        "data" => array(
            $fields,
        ));

    $response = call_zoho_api($module, $query_string, "POST", json_encode($fields));
    return json_decode($response);
}

// $fields = array(
//     "ADS_template_Resend"      => true
// );
// $response = updateRecordInZoho('Contacts','2033612000000214229' ,$fields );
// show($response);

function updateRecordInZoho($module, $zoho_id, $fields)
{
    $module = $module . '/' . $zoho_id;
    $query_string = '';

    $fields = array(
        "data" => array(
            $fields,
        ));
    $response = call_zoho_api($module, $query_string, "PUT", json_encode($fields));
    return json_decode($response);
}

function deleteRecordFromZoho($module, $zoho_id)
{
    $module = $module . '/' . $zoho_id;
    $query_string = '';

    $response = call_zoho_api($module, $query_string, "DELETE");
    return json_decode($response);
}

// $fields = 
//     array(
//         array(
//             'id' => '2033612000000329292',
//             'ADS_User_Role' => '',
//         ), 
//         array(
//             'id' => '2033612000000326353',
//             'ADS_User_Role' => ''
//         )
//     );

// updateBulkRecordsInZoho('Contacts', $fields);
function updateBulkRecordsInZoho($module, $fields )
{
    // $query_string = '';

    // $recordsCount = count($fields);
    // $i            = 0;

    // for($i = 0; $i < ($recordsCount);) {
    //     $output = array_slice($fields, $i, BULK_UPDATE_LIMIT);

    //     $fields_updated = array(
    //         "data" => $output,
    //         "trigger" => []
    //         );
   
    //         $i = $i + BULK_UPDATE_LIMIT;

    //         $response = call_zoho_api($module, $query_string, "PUT", json_encode($fields_updated));
    // }

    // return json_decode($response);

    $query_string = '';
    $fields = array(
        "data" => $fields,
        "trigger" => []
    );
   
    $response = call_zoho_api($module, $query_string, "PUT", json_encode($fields));
    return json_decode($response);
}


function call_zoho_api($module, $query_string, $type, $data = false)
{
    $access_token = return_valid_token();
    $curl = curl_init();
    // show(get_zoho_url($module, $query_string));
    curl_setopt_array($curl, array(
      
        CURLOPT_URL => get_zoho_url($module, $query_string),
        // CURLOPT_URL => ZOHO_API_URL . "{$module}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $type,
        CURLOPT_HTTPHEADER => array(
            "authorization: Zoho-oauthtoken " . $access_token,
            "cache-control: no-cache",
        ),
    ));

    if ($data) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    $response = curl_exec($curl);
   
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {    
            $filename = basename($_SERVER['PHP_SELF']);
            api_exception_log($filename, 'Erron in call zoho api ', serialize($error));
        return false;
    } else {
        return $response;
    }
}

function get_zoho_url($module, $query_string)
{
    return ZOHO_API_URL . "{$module}?{$query_string}";
}

function zoho_get_all_accounts()
{
    $page = 0;
    do {
        $page++;

        $module = 'Accounts';
        $query_string = "page={$page}&sort_by=Email&sort_order=asc";
        $response = call_zoho_api($module, $query_string, 'GET');

        $response = json_decode($response);
        $more_records = $response->info->more_records;
        return $response;
      
        $more_records = $response->info->more_records;
      
    } while ($more_records == 1);

    $update_type = 'UPDATE test_zoho_account_information SET type = "company" WHERE ads_record_id IS NOT NULL';
    execute_sql($update_type);

    $update_type = 'UPDATE test_zoho_account_information SET type = "advertiser" WHERE ads_acct_id IS NOT NULL';
    execute_sql($update_type);

    $update_type = 'UPDATE test_zoho_account_information SET type = "company+advertiser" WHERE ads_acct_id IS NOT NULL AND ads_record_id IS NOT NULL';
    execute_sql($update_type);

    return $accounts;
}

function zoho_get_all_contacts()
{
    $page = 0;
    do {
        $page++;

        $module = 'Contacts';
        $query_string = "page={$page}&sort_by=Email&sort_order=asc";
        $response = call_zoho_api($module, $query_string, 'GET');

        $response = json_decode($response);

        if (empty($response->info)) {
            api_exception_log('zoho_crm function.php', 'Zoho get all contacts', serialize($response));
        }

        $more_records = $response->info->more_records;

        foreach ($response->data as $key => $value) {
            $contacts[] = $value;
            $zoho_link_id = $value->id;
            // execute_sql($insert_sql);
        }
    } while ($more_records == 1);

    return $contacts;
}

function get_zoho_constants(&$zoho_constants_array = array())
{
    $zoho_constants_array['ZOHO_REFRESH_TOKEN'] = ZOHO_REFRESH_TOKEN;
    $zoho_constants_array['CLIENT_ID'] = ZOHO_CLIENT_ID;
    $zoho_constants_array['CLIENT_SECRET'] = ZOHO_CLIENT_SECRET;
    $zoho_constants_array['CODE'] = ZOHO_CODE;
    $zoho_constants_array['ZOHO_APP_REDIRECT_URL'] = ZOHO_APP_REDIRECT_URL;
    $zoho_constants_array['ZOHO_API_URL'] = ZOHO_API_URL;

    return $zoho_constants_array;
}

function zoho_api_refresh_token()
{
    get_zoho_constants($zoho_constants_array);
    extract($zoho_constants_array);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ZOHO_OAUTH_TOKEN_URL . "?" . "refresh_token={$ZOHO_REFRESH_TOKEN}&client_id={$CLIENT_ID}&client_secret={$CLIENT_SECRET}&grant_type=refresh_token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
    ));

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        api_exception_log('zoho_crm function.php', 'Zoho api refresh token', serialize($error));
    } else {
        $response = json_decode($response);
        return $response;
    }

    // return $refresh_token;
}

function get_token_from_db()
{
    // $access_token = ZOHO_ACCESS_TOKEN;
    if(!isset($_SESSION['access_token'])){
        $_SESSION['access_token'] = ZOHO_ACCESS_TOKEN;
    } 
    return $_SESSION['access_token'];
}

function get_time_value_from_token($token)
{
    preg_match("/\\{{{(.*?)\\}}}/", $token, $matches);

    if (!empty($matches[1])) {
        return $matches[1];
    } else {
        return false;
    }
}

function return_valid_token()
{
    $access_token = get_token_from_db();
    if (empty($access_token)) {
        $response = zoho_api_get_access_and_refresh_token();

        if ($response == false) {
            api_exception_log('zoho_crm function.php', 'return valid token', serialize($response));
            return;
        }

        $access_token = $response->access_token;
    }

    $token_expiration_time = get_time_value_from_token($access_token);

    if ($token_expiration_time == false) {
        api_exception_log('zoho_crm function.php', 'token_expiration_time', serialize($token_expiration_time));
        return;
    }

    $current_timestamp = get_timestamp();

    if ($token_expiration_time > $current_timestamp) {
        return explode('|||', $access_token)[0];
    } else {
        $response = zoho_api_refresh_token();
        $access_token = $response->access_token;
        $expiry_time = $current_timestamp + $response->expires_in_sec;

        // UPDATE IN DB
        if (!empty($access_token)) {
            $_SESSION['access_token'] = $access_token.'|||{{{' . $expiry_time . '}}}';
            $update_token_in_db = "UPDATE configs SET value = '" . $access_token . "|||{{{" . $expiry_time . "}}}' WHERE name = 'ZOHO_ACCESS_TOKEN'";
            execute_sql($update_token_in_db);
        }

        return $access_token;
    }
}

function get_timestamp()
{
    $old_timezone = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $timestamp = time();

    date_default_timezone_set($old_timezone);
    return $timestamp;
}

function zoho_error_log($error = '')
{
    // TO-DO
    // 'INSERT INTO zoho_api_log_error';
    // show($error);
}

function clean_zoho_content($value)
{
    return addslashes($value);
}

function quote_variable($value)
{
    if (empty($value)) {
        return 'NULL';
    } else {
        return "'" . clean_zoho_content($value) . "'";
    }
}

closeConnection();
