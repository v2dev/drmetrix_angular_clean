<?php
// Cron is in used, runs every 3 hours
    //CREATE TABLE `advertiser_pages_temp` ( `id` INT NOT NULL AUTO_INCREMENT , `adv_id` INT NOT NULL, `type` ENUM('short','long') NOT NULL DEFAULT 'short' , `rank` INT NOT NULL , `rate` FLOAT(15,2) NOT NULL, `spend_index` float(5,2) , PRIMARY KEY (`id`)) ENGINE = InnoDB;
    //CREATE TABLE `advertiser_pages` ( `id` INT NOT NULL AUTO_INCREMENT , `adv_id` INT NOT NULL, `type` ENUM('short','long') NOT NULL DEFAULT 'short' , `rank` INT NOT NULL , `rate` FLOAT(15,2) NOT NULL, `spend_index` float(5,2) , PRIMARY KEY (`id`)) ENGINE = InnoDB;
    // show($data, 1);
    // return $data;
if(php_sapi_name() != 'cli') {
    echo 'Script cannot be exeuted from GUI';
    exit;
}
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/queries.php';

set_time_limit(0);
ignore_user_abort();
getAdvertiserDetail();
closeConnection();

function getAdvertiserDetail() {
    truncate_table('advertiser_pages_temp');

    insert_records_temp_table(' <= ' . LENGTH, 'short');
    insert_records_temp_table(' > ' . LENGTH, 'long');

    truncate_table('advertiser_pages');

    update_advertiser_pages_table();
}

// truncate_table('advertiser_pages_temp');
// truncate_table('advertiser_pages');
function truncate_table($table_name) {
    $db                 = getConnection();
    $delete_table_sql   = 'TRUNCATE TABLE ' . $table_name;
    $db                 = getConnection();
    $stmt               = $db->prepare($delete_table_sql);
    $stmt->execute();
}

function update_advertiser_pages_table() {
    $db                 = getConnection();
    $insert_sql         = 'INSERT INTO advertiser_pages (adv_id, type, rank, rate, spend_index) (SELECT adv_id, type, rank, rate, spend_index FROM advertiser_pages_temp);';
    $stmt               = $db->prepare($insert_sql);
    //show($delete_table_sql);
    $stmt->execute();
}

function insert_records_temp_table($length_condition, $type) {
    $db                 = getConnection();
    // $params['length_condition'] = ' <= ' . LENGTH;
    $params['length_condition'] = $length_condition;
    $result                     = get_query_result('__query_advertiser_lifetime_detail', $params);

    if (count($result) > 0) {
        foreach($result as $value){
            if(empty($spend_index)) { 
                $spend_index            = $value['rate'];
                $rank                   = 0;
                $db                     = getConnection();
            }

            $advertiser['id']                   = $value['adv_id'];
            $advertiser['rank']                 = ++$rank;
            $advertiser['rate']                 = $value['rate'];
            $advertiser['spend_index']          = ($value['rate'] / $spend_index) * 100;

            $insert_sql                         = 'INSERT INTO advertiser_pages_temp (adv_id, type, rank, rate, spend_index) VALUES ('.$value['adv_id'].', "'.$type.'", '.$rank.', '.$value['rate'].', '.$advertiser['spend_index'].');';
            $stmt                               = $db->prepare($insert_sql);
            //show($insert_sql);
            $stmt->execute();
        }
    }
}