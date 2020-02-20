<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/queries.php';

ignore_user_abort();

set_time_limit(0);
checkSession();
getLastLogin(1);

// $url = 'http://example.com/example.zip';
// $url = 'http://dev.drmetrix.com/drmetrix/creative_videos/581/51821769/Got_Directions_To_The_Nightclub-30.mp4';

parse_str(base64_decode($_SERVER['QUERY_STRING']), $params);
$adid = $params['adid'];
$user_id = $params['user_id'];
$page = $params['page'];
$ch = curl_init();
$video_api_server = getVideoStreamingUrl();
$url = $video_api_server . "api/v1/ads/$adid";
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
curl_setopt($ch, CURLOPT_TIMEOUT_MS, 15000);
$server_output = curl_exec($ch);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
curl_close($ch);
if ($curl_errno > 0) {
    APITimOut($url);
} else {
    $json_a = json_decode($server_output, true);
}

$creative_info = getCreativeInfoByAiring($adid);

if($page == 'Video') {
    $download_url = $json_a['download_url'];
} else {
    $download_url = $json_a['ocr_image_url'];
}

if (empty($download_url)) {
    header('Location: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].'/drmetrix');
    exit;
}

//$download_url = 'http://dev.drmetrix.com/drmetrix/creative_videos/581/51821769/Got_Directions_To_The_Nightclub-30.mp4';
$info = pathinfo($download_url);
$name = $info['filename'];
$ext = $info['extension'];
$creative_info[0]->video_extension = $ext;
$creative_info[0]->user_id = $user_id;
$creative_info[0]->page = $page;
// $file_name  = download_video_on_local($download_url , $creative_info);
createFolderForDownloads($creative_info);

if($page == 'Thumbnail') {
    $video_folder_path = dirname(__FILE__) . '/../'.THUMBNAIL_FOLDER.'/' . $user_id . '/' . $creative_info[0]->creative_id . '/';
} else {
    $video_folder_path = dirname(__FILE__) . '/../'. VIDEO_FOLDER . '/' . $user_id . '/' . $creative_info[0]->creative_id . '/';
}

$brand_name     = clean($creative_info[0]->brand_name);
$network_alias  = clean($creative_info[0]->network_alias);
$start_date     = $creative_info[0]->start;
$length         = clean($creative_info[0]->length);

$start_date_array = date_parse($start_date);

if ($start_date_array['hour'] >= 12) {
    $start_date_array['hour_minute'] = prepend_leading_characters($start_date_array['hour'] % 12) . ':' . prepend_leading_characters($start_date_array['minute']) . 'PM';
} else {
    $start_date_array['hour_minute'] = prepend_leading_characters($start_date_array['hour']) . ':' . prepend_leading_characters($start_date_array['minute']) . 'AM';
}

$start_date     = prepend_leading_characters($start_date_array['month']) . '-' . prepend_leading_characters($start_date_array['day']) . '-' . $start_date_array['year'] . ' ' . $start_date_array['hour_minute'];

$file_name      = "{$brand_name}__{$length}_sec__{$network_alias}__({$start_date})";


$file_download_url = $video_folder_path . $file_name . ".$ext";
$file = basename($file_download_url);

/** Check video download limit for user for Video only **/
if($page == 'Video') {
    $params['created_date']     = customDate('Y-m-d H:i:s');
    $company_result  = get_query_result('__query_check_company_video_downloads_limit', $params, 'FETCH_OBJ');

    if(!empty($company_result)) {
        $company_video_downloads_limit = $company_result[0]->video_download_limit;
    }

    $user_result  = get_query_result('__query_check_count_video_downloads', $params, 'FETCH_OBJ');
    if(!empty($user_result)) {
        $user_video_downloads_limit = $user_result[0]->count_video_downloads;
    }

    if($user_video_downloads_limit >= $company_video_downloads_limit){
        echo '<p align="center">Monthly download limit exceeded. Email <i style="color: black;">support@drmetrix.com</i> for more info.</p>';exit;
    }
}

$fp = fopen($file_download_url, 'w');
$ch = curl_init($download_url);
curl_setopt($ch, CURLOPT_FILE, $fp);

$data = curl_exec($ch);
curl_close($ch);
fclose($fp);

//show($file_download_url, 1);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($file_download_url));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_download_url));
ob_clean();
flush();
readfile($file_download_url);
unlink($file_download_url);
rmdir($video_folder_path);

if($page == 'Video') {
    $sql  = __query_add__user_video_downloads($params);
    execute_sql($sql);
}
exit;

