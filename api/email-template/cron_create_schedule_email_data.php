<?php
/*if(php_sapi_name() != 'cli') {
    echo 'Script cannot be exeuted from GUI';
    exit;
}*/
//Cron is in used, runs at 6 PM for frequnecy daily and on monday 6 PM for frequency weeklt
require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../queries.php';
require_once dirname(__FILE__) . '/../functions.php';
require_once dirname(__FILE__) . '/../PHPMailer/class.phpmailer.php';
require_once dirname(__FILE__) . '/./schedule_email_cron_template.php';

ignore_user_abort();
set_time_limit(0);

if(php_sapi_name() == 'cli') {
    if (!isset($argv[1])) {
        echo 'frequency not set correctly';
        exit;
    }
    
    parse_str($argv[1], $params);
    $frequency  = $params['frequency'];
} else {
//     echo 'Script cannot be exeuted from GUI';
//     exit;
    $frequency  = $_GET['frequency'];
}

$users      = get_all_scheduled_email_alert_subscribers($frequency);

foreach ($users as $user) {
    $username       = $user['username'];
    $user_name      = $user['first_name']. ' '. $user['last_name'];
    $user_id        = $user['user_id'];
    $user_email     = $user['email'];
    $query_string   = $user['query_string'];
    // $primary_tab    = $user['primary_tab'];
    $postdata = array();
    $postdata['network_tab'] = 1;
    // $filter_text    = get_filter_text($query_string);
    // $filter_text    = str_replace('XxX', '&', $filter_text['display_text']);
    $query_string = process_dates_for_filter($query_string, 1); // $scheduled_email=1
    parse_str($query_string, $postdata);
    extract($postdata);

    $sd = formatDate($postdata['sd']);
    $ed = formatDate($postdata['ed']);
    // $date_range_string = " - $sd thru $ed";
    $date_range_string = "$sd thru $ed";
    // echo '['.$refine_apply_filter.']'.'['.$new_filter_opt.']'.'['.$network_id.']'.'['.$query_string.']';
    // print_r($postdata); exit;
    // $excelExportOrRefineExport = false;
    $excelExportOrRefineExport = isset($refine_apply_filter) && $refine_apply_filter;
    /*$postdata = array(
        user_id => $user_id,
        export_refine_records => $user['export_refine_records'],
    );*/
    $postdata['user_id']    = $user_id;
    $postdata['username']   = $username;

    //$val = $requestData['val'];
    //$sd =  $requestData['sd'];
    //$ed =  $requestData['ed'];
    //$c  = urldecode($requestData['c']);
    //$tab = $requestData['type'];
    //$tab = $primary_tab == 'brand' ? 1 : 2;
    //$cat = rtrim($requestData['cat'],",");
    //$cat = rtrim($cat,"all,");
    //$catIn = '('.$cat.')';
    //$uncheckedCatIn = '('.rtrim($requestData['unchecked_category'],",").')';

    /*if ($tab == 1) {
        $last_week_data_html    = 'last_week_data_html_brand';
        $last_week_export_data  = 'last_week_export_data_brand';
    } else {
        $last_week_data_html    = 'last_week_data_html_advertiser';
        $last_week_export_data  = 'last_week_export_data_advertiser';
    }

    //$response                   = $get_rankings_from_cache['data'];
    $clause                     = "start_date = $sd AND end_date = $ed";
    $params['clause']           = $clause;
    $params['component']        = $last_week_export_data;
    $cached_excel_result        = get_query_result('__query_get_cached_data', $params);
    $export_refine_records      = json_decode($cached_excel_result[0]['result']);*/
    $counter = 0;
    $filter_results = explode('&', $query_string);
    // $filter_results.push('network_tab=1');
    array_push($filter_results,'network_tab=1');
    // $filter_results['network_tab'] = 1;
    // print_r($filter_results);
    foreach($filter_results as $keyVal){
        if( strpos($keyVal, 'responseType=') !== false ) {
            // echo $keyVal;
            $responseType = explode('=', $keyVal);
            // print_r($responseType);
            $responseTypeOrg = $responseType[1];
            $responseType[1] = '(' . $responseType[1] . ')';
            $filter_results[$counter] = $responseType[0] . '=' . $responseType[1];
            $responseType = $responseTypeOrg;
            break;
        }
        $counter++;
    }
    // $filter_results['responseType'] = '('.$filter_results['responseType'].')';
    // print_r($filter_results); exit;
    $url = 'http://'.HOST.'/drmetrix/api/'.($user['page'] == 'network'?'display_airings_brands_with_networks':($excelExportOrRefineExport?'apply_refine_filters':'filter_results'));
    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_POST => 1,
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 100,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => implode('&', $filter_results),
    ));
    $response = curl_exec($curl);
    // print_r($curl); Resource id #10
    $error = curl_error($curl);
    // print_r($error);

    curl_close($curl);
    if ($error) {
        api_exception_log('cront_create_shcedule_email_data.php', 'schedule_email getData()', serialize($error));
    } else {
        $result = json_decode($response);
        $error = json_last_error();
        if($user['page'] == 'network' || $excelExportOrRefineExport) {
            $export_refine_records = $result->rows;
        } else {
            $export_refine_records = $result->exp_data;
        }
        
        // print_r($export_refine_records);
    }
// exit;


    if($user['page'] == 'network') {
        $postdata['network_tab'] = 1;
        $postdata['export_network'] = serialize(($export_refine_records));
    } else if($excelExportOrRefineExport) { // refine_excel_export
        $postdata['export_refine_records'] = serialize(($export_refine_records));
    } else { // export_to_excel
        $postdata['export'] = serialize(($export_refine_records));
    }
    if(!isset($postdata['date_range_str'])) {
        $postdata['date_range_str'] = $date_range_string;
    }
    if(!isset($postdata['classfication_filter'])) {
        // $postdata['classfication_filter'] = $creative_duration;
        $classification = '';
        switch ($postdata['c']) {
            case 1:
                $classification = 'All Short Form (All Duration)';
                break;
            case 2:
                $classification = 'Short Form Products';
                break;
            case 3:
                $classification = 'Lead Generation';
                break;
            case 4:
                $classification = 'Brand/DR';
                break;
            case 5:
                $classification = 'AsOnTV Retail Rankings';
                break;
            case 6:
                $classification = '28.5m Creative';
                break;
            case 7:
                $classification = 'AsOnTV Retail Rankings (28.5m)';
                break;
        }
        $postdata['classfication_filter'] = $classification;
    }
    if(!isset($postdata['response_type_filter'])) {
        $postdata['response_type_filter'] = $responseType;
    }

    if( isset($postdata['network_code']) && isset($postdata['network_alias']) ) {
        $postdata['network_name'] = $postdata['network_alias'] = urldecode($postdata['network_alias']);
    } else {
        $postdata['network_name'] = 'All';
    }
    // print_R(json_encode($postdata)); exit; // "date_range_str":"","classfication_filter":"","response_type_filter":"",

    $url = 'http://'.HOST.'/drmetrix/api/'.($user['page'] == 'network' ? 'network_excel_export' : ($excelExportOrRefineExport?'refine_excel_export':'export_to_excel'));
    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_POST => 1,
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 100,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postdata,
    ));
    $response = curl_exec($curl);
    // print_r($curl);
    // print_r($response); exit;

    $error = curl_error($curl);
    // print_r($error);

    curl_close($curl);
    if ($error) {
        api_exception_log('cront_create_shcedule_email_data.php', 'schedule_email', serialize($error));
    } else {
        $result = json_decode($response);

        if(/*($user['page'] == 'network' && $result->records > 0) ||*/ $result->status == 1) {
            // success
            // $user_name      = $user['first_name']. ' '. $user['last_name'];
            // $user_id        = $user['user_id'];
            // $user_email     = $user['email'];
            $report_filter          = 'filter';
            $name_of_report_filter  = $user['name'];

            $email_content  = alert_email($user_email, $user_name, $user_id, $frequency, $report_filter, $name_of_report_filter, $result->obj->id);
            if ($email_content == '') {
                continue;
            }

            $subject    = 'Scheduled email alert ';
            $from_name  = FROM_NAME;
            $from_email = FROM_EMAIL;

            $to_name    = $user_name;
            $to_email   = $user_email;

            $body     = $email_content;
            $body     = wordwrap($body, 50);

            $mail     = new PHPMailer(); //New instance, with exceptions enabled
            $mail->IsSendmail();  // tell the class to use Sendmail
            $mail->SetFrom($from_email, $from_name);
            //$mail->addCustomHeader('MIME-Version: 1.0');
            //$mail->addCustomHeader('Content-Type: text/html; charset=ISO-8859-1');
            $mail->AddAddress($to_email, $to_name);
            $mail->Subject  = $subject;
            $mail->MsgHTML($body);
            $mail->MsgHTML(wordwrap($body, 50));
            $mail->IsHTML(true); // send as HTML

            $tracking_email_recipients_array = explode(",", TRACKING_EMAIL_RECIPIENTS);

            foreach ($tracking_email_recipients_array as $email_address) {
                $email_address = trim($email_address);
                $mail->addBCC($email_address, $email_address);
            }

            //echo $body;
            if ($mail->Send()) {
                echo 'email sent';
            } else {
                echo 'error while sending an email';
            }
            // sleep(5);
        }
    }

}
