<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';

set_time_limit(0);
ignore_user_abort();
addNewCategories();
closeConnection();

function addNewCategories()
{
    $categories = $_GET['categories'];

    $new_categories = explode(",", $categories);
    if (!empty($new_categories)) {
        //check for alert type 'category' for all users
        getUsersTrackingForCategory($new_categories);
    }
}

function getUsersTrackingForCategory($new_categories)
{
    $flag           = 0;
    $db             = getConnection();
    $get_result     = get_query_result('__query_get_all_tracking_alert_subscribers_all_users', '', 'FETCH_ASSOC');
    $get_categories = get_query_result('__query_get_all_categories', '', 'FETCH_ASSOC');

    foreach ($get_result as $key => $value) {
        $param['user_id']       = $value['user_id'];
        $user_tracking_result   = get_query_result('__query_get_tracking_data_each_user', $param, 'FETCH_OBJ');
        $newCategoryCount       = count($get_categories) - count($new_categories);

        if (count($user_tracking_result) == $newCategoryCount) {
            //insert data into tracking table
            addTrackingData($user_tracking_result[0], $new_categories);
            $flag = 1;
        }
    }
    if ($flag == 1) {
        echo 'New categories added successfully in Tracking and Alerts';
    } else {
        echo 'New categories already exists in Tracking and Alerts';
    }

}

function addTrackingData($track_value, $new_categories)
{
    $db = getConnection();
    foreach ($new_categories as $cat_key => $cat_value) {
        $insert_sql         = 'INSERT INTO tracking_and_alerts (user_id, alert_type, type_id, track_elements, classification, frequency, created_date) VALUES (' . $track_value->user_id . ', "category",  "' . $cat_value . '","' . $track_value->track_elements . '", "' . $track_value->classification . '", "' . $track_value->frequency . '", "' . date('Y-m-d H:i:s') . '");';
        execute_sql($insert_sql);
    }

   
}
