<?php
require_once dirname(__FILE__) . '/constants.php';
/**
 * Author : Ashwini Shinde
 * Date: 18 - 05- 2016
 * Purpose: All queries required will be written here
 */

//get categories
$queries['_sql_get_main_categories'] = "SELECT " . CACHE_SQL_QUERY . "  *
        FROM categories group by category_id";

//get subcategories
$queries['_sql_get_sub_categories'] = "SELECT " . CACHE_SQL_QUERY . " *
        FROM categories
        WHERE category_id = %d ORDER by sub_category";

//get current date calendar details.
$queries['_sql_get_current_calendar_details'] = "SELECT " . CACHE_SQL_QUERY . " media_qtr,
       media_month,
       media_year
FROM   media_calendar
WHERE  '" . customDate('Y-m-d') . "' BETWEEN `media_week_start` AND `media_week_end` ";

//get calendar details for given condition
$queries['_sql_get_calendar_details'] = "SELECT " . CACHE_SQL_QUERY . " %s AS calendar_id,
       media_year
FROM   media_calendar
WHERE  %s
GROUP BY %s , media_year";

//get main_category,main_sub_category
$queries['_sql_get_category_details'] = "SELECT " . CACHE_SQL_QUERY . " sub_category_id, sub_category,
       category_id
FROM   categories
WHERE  category_id IN %s ";

function __queries_display_advertiser_brands($params)
{
    extract($params);

    $sql = "SELECT " . CACHE_SQL_QUERY . " c.creative_id,
       b.brand_name,
       b.brand_id,
       b.short_active  is_brand_active,
       a.display_name advertiser_name,
       c.creative_name,
       c.class,
       c.price,
       b.brand_id  parent_id,
       c.type,
       c.length,
       c.thumbnail,
       c.is_active,
       c.response_tfn,
       c.response_url,
       c.response_sms,
       c.response_mar,
       Count(*)  airings,
       $cols
       Date_format(c.first_detection, '%m/%d/%y %l:%i %p') AS first_aired_date,
       Date_format(c.last_aired, '%m/%d/%y %l:%i %p') AS last_aired_date ,
       Sum(d.".RATE_COLUMN.")
       spend_index,
       Round(Avg(d.length))   AS asd,
       Count(DISTINCT network_code)  AS networks,
       Count(DISTINCT d.creative_id)                          creative_count,
       Count(DISTINCT cat.category_id)                        category_count,
       b.adv_id,
       cat.category
        FROM   " . AIRINGS_TABLE . " d
               JOIN creative c
                 ON d.creative_id = c.creative_id
                    AND c.spanish IN ($spanish)
                    AND $responseType
               JOIN brand b 
                 ON c.brand_id = b.brand_id 
               JOIN categories cat
               JOIN advertiser a
                 ON b.adv_id = a.adv_id
                    AND a.adv_id = $adv_id
        WHERE  c.class != 'BRAND' AND cat.sub_category_id IN(b.main_sub_category_id, b.alt_sub_category_id) $categories AND  d.start_date >= '$sd'
               AND d.start_date <= '$ed' $brand_classification
        GROUP  BY b.brand_id
        ORDER  BY airings DESC ";

    return $sql;
}

function __query_get_creative_short_duration($params)
{
    extract($params);

    $sql = "SELECT DISTINCT(length) FROM creative WHERE length != 0 AND length <= " . LENGTH . " AND class != 'BRAND' ORDER BY length;";

    return $sql;
}

function __queries_summary_display_advertiser_brands($params)
{
    extract($params);

    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);
    $program_params = getProgramParams($params);

    if (!empty($network_code)) {
        $sql = "SELECT " . CACHE_SQL_QUERY . " c.creative_id,
                b.brand_name,
                b.total_weeks,
                b.brand_id,
                adv.display_name advertiser_name,
                c.creative_name,
                c.class,
                c.price,
                b.brand_id  parent_id,
                c.type,
                c.length,
                c.thumbnail,
                c.is_active,
                c.response_tfn,
                c.response_url,
                c.response_sms,
                c.response_mar,
                Count(*)  airings,
                $cols
                Date_format(c.first_detection, '%m/%d/%y %l:%i %p') AS first_aired_date,
                Date_format(c.last_aired, '%m/%d/%y %l:%i %p') AS last_aired_date ,
                Sum(d.".RATE_COLUMN.")
                spend_index,
                Round(Avg(d.length))   AS asd,
                Count(DISTINCT d.network_code)  AS networks,
                Count(DISTINCT d.creative_id)                          creative_count,
                1 AS category_count,
                b.adv_id, b.main_sub_category_id, b.alt_sub_category_id,
                adv.need_help
                 FROM   " . AIRINGS_TABLE . " d
                        JOIN creative c
                          ON d.creative_id = c.creative_id
                             AND c.spanish IN ($spanish)
                             AND $responseType
                        JOIN brand b 
                          ON c.brand_id = b.brand_id 
                        JOIN advertiser adv 
                          ON b.adv_id = adv.adv_id 
                             AND adv.adv_id = $adv_id
                             ".$program_params['join_condition'] .$program_params['table_join_on']."
                 WHERE  1= 1 $categories AND d.start_date BETWEEN  '$sd' AND '$ed' $brand_classification
                        AND      d.network_id IN ($network_id) $new_filter_opt".$program_params['where_program'].$list_id_condition."
                 GROUP  BY b.brand_id
                 ORDER  BY $order_by ";
    } else {
        $sql = "SELECT   " . CACHE_SQL_QUERY . " c.creative_id,
                b.brand_name,
                b.total_weeks,
                b.brand_id,
                adv.display_name advertiser_name,
                c.creative_name,
                c.class,
                c.price,
                b.brand_id parent_id,
                c.type,
                c.length,
                c.thumbnail,
                c.is_active,
                c.response_tfn,
                c.response_url,
                c.response_sms,
                c.response_mar,
                SUM(airings)                                         airings,
                $cols
                Round(100*Sum(local_airings)/Sum(airings))          AS local,
                Round(100*Sum(national_airings)/Sum(airings))       AS national,
                Date_format(c.first_detection, '%m/%d/%y %l:%i %p') AS first_aired_date,
                Date_format(c.last_aired, '%m/%d/%y %l:%i %p')      AS last_aired_date ,
                Sum(d.".RATE_COLUMN.")                                            spend_index,
                Round(sum(d.asd) / sum(d.airings))                  AS asd,
                Count(DISTINCT network_code)                        AS networks,
                Count(DISTINCT d.creative_id)                          creative_count,
                1                                                   AS category_count,
                b.main_sub_category_id, b.alt_sub_category_id,
                b.adv_id,
                adv.need_help
            FROM     " . SUMMARY_AIRINGS . " d,
                     creative c,
                     brand b,
                     advertiser adv
            WHERE    d.creative_id = c.creative_id
            AND      c.spanish IN ($spanish)
            AND      $responseType
            AND      d.brand_id = b.brand_id
            $categories
            AND      b.adv_id = adv.adv_id
            AND      adv.adv_id = $adv_id
            AND      d.start_date >= '$sd'
            AND      d.start_date <= '$ed' $brand_classification $new_filter_opt $list_id_condition
            GROUP BY b.brand_id
            ORDER BY $order_by";
    }
    return $sql;
}

function __query_summary_log_value($params)
{
    $sql = 'SELECT ' . CACHE_SQL_QUERY . ' value FROM configs WHERE name="SUMMARY_TOGGLE"';
    return $sql;
}

function __query_networks_with_all_filters($params)
{
    extract($params);

    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);
    $sql1 = 'SELECT
            d.network_id
        FROM     creative c,
                airings d USE INDEX(idx_ranking3),
                 brand b,
                 advertiser adv
        WHERE    c.spanish IN (' . $spanish . ')
        AND      c.brand_id = d.brand_id
        AND      c.creative_id = d.creative_id
        '.$brand_classification.'
        AND      ' . $responseType . '
        AND      start_date >= "' . $sd . '"
        AND      start_date <= "' . $ed . '" '  . $categories . '
        AND      c.brand_id = b.brand_id
        AND      b.adv_id = adv.adv_id
        ' . $new_filter_opt .$list_id_condition. '';

        /*$sql = 'select  GROUP_CONCAT(network_id order by network_id asc) as network_codes from network
        where exists (' . $sql1. ' and network.network_id = d.network_id)';*/
        $sql = 'select group_concat(network_id) as network_codes from (' . $sql1. ') nx;';

    return $sql;
}

function __query_network_alias($params)
{
    $sql = "SELECT network_id,network_alias,network_code,dpi,live_date,diginet FROM network WHERE status = 1 ORDER BY network_alias ASC";
    return $sql;
}

function newFilter($new_filter_opt, $sd, $ed)
{
    $new_filter = '';
    if ($new_filter_opt != 'none') {
        if ($new_filter_opt == "adv") {
            $new_filter = ' AND  adv.first_detection BETWEEN "' . $sd . ' 00:00:00" AND  "' . $ed . ' 23:59:59"';
        } else if ($new_filter_opt == "brands") {
            $new_filter = ' AND  b.first_detection BETWEEN "' . $sd . ' 00:00:00" AND  "' . $ed . ' 23:59:59"';
        } else if ($new_filter_opt == "creatives") {
            $new_filter = ' AND  c.first_detection BETWEEN "' . $sd . ' 00:00:00" AND  "' . $ed . ' 23:59:59"';
        }
    }

    return $new_filter;
}


function __query_display_airings_layout($params) {
    extract($params);
   $where_refine_by = $table_join =$table = $where_program= '';
    $colName = 'tfn_num';
    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);
    $use_index      = getUseIndex($refine_filter_opt);
    if ($refine_filter_opt == '800') {
        $refine_filter_array = getRefineTextWithStringFilters($refine_filter_opt_text, $replaced);
        $where_refine_by = 'AND d.'.$refine_filter_array['colName'].'  LIKE "%' . $refine_filter_array['refine_filter_opt_text'] . '%"';
    } else if ($refine_filter_opt == 'url') {
        // $where_refine_by = 'AND d.url LIKE "%' . $refine_filter_opt_text . '%"';
        $refine_filter    = getUrlFilters($refine_filter_opt_text);
        $where_refine_by  = $refine_filter['where'];
    }

    if($refine_filter_opt == '800' || $refine_filter_opt == 'url') {
        $where_refine_by .= ' AND d.verified = 1';
    }
    if(!empty($program_ids)){
        $table          = ' , program_master p ';
        $table_join     = '  AND      d.program_id = p.program_id';
        $where_program = ' AND p.program_id IN ('.$program_ids.')';
    }

    $where_network = ($network_id != '')  ?   ' AND d.network_id = '.$network_id : '';

    $where_tab =  ($tab == 1 ) ? '  AND d.brand_id = '.$record_id :  'AND b.adv_id = '.$record_id ;

    $sql = 'SELECT   n.network_name,n.network_code,n.network_alias, n.network_id,d.create_date ,d.breaktype, d.tfn, d.url, d.promo, d.verified,  d.program, thumb, c.creative_id,d.airing_id,d.start, c.thumbnail, c.last_aired, c.master_tfn_num, c.master_url
    FROM    brand b, creative c,   advertiser adv,
                ' . AIRINGS_TABLE . ' d '.$use_index.',
                network n '.$table.'
    WHERE    c.creative_id = d.creative_id
    AND      d.network_id = n.network_id
    AND d.creative_id = c.creative_id
    AND c.brand_id = b.brand_id
    AND b.adv_id = adv.adv_id
    '.$table_join.'
         '.$responseType.'
    AND      d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"
        '. $where_refine_by . '
        AND     spanish IN ('. $spanish . ') '. $new_filter_opt.$where_flag.$brand_classification.$categories. $where_network.$where_program.$list_id_condition.'
            '.$where_tab.' AND d.creative_id = '.$creative_id.' ORDER BY '.$export_order_by .$limit_string ;
            return $sql;

    // $sql = "SELECT  n.network_name,n.network_code, create_date ,breaktype, tfn, url, promo, verified,  program, thumb, c.creative_id,a.airing_id FROM airings a , creative c, network n  WHERE a.creative_id = c.creative_id  AND a.network_code = n.network_code AND a.creative_id = ".$record_id." ORDER BY a.creative_id DESC LIMIT 0, 20 ";
} 

function __query_refine_by_800_url($params) {
    extract($params);
    $where_refine_by =  $column_program = $table_join = $table = $where_program ='';
    $where_network   = '';
    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);
    $use_index      = getUseIndex($refine_filter_opt);
    if ($refine_filter_opt == '800') {
        $refine_filter_array = getRefineTextWithStringFilters($refine_filter_opt_text, $replaced);
        $where_refine_by = 'AND ((d.'.$refine_filter_array['colName'].'  LIKE "%' . $refine_filter_array['refine_filter_opt_text'] . '%")';
        if( $GLOBALS['add_underscore_operator'] == 1) {
            $where_refine_by .= ' AND POSITION("'.$refine_filter_array['refine_filter_opt_text'].'" IN d.'.$refine_filter_array['colName'].' ) < CHAR_LENGTH(d.'.$refine_filter_array['colName'].') - LOCATE("-", REVERSE(d.'.$refine_filter_array['colName'].'))+1)';
        } else {
            $where_refine_by .= ')';
        }
        $display_column = 'GROUP_CONCAT(DISTINCT d.tfn)';
    } else if ($refine_filter_opt == 'url') {
        // $where_refine_by = 'AND d.url LIKE "%' . $refine_filter_opt_text . '%"';
        $refine_filter    = getUrlFilters($refine_filter_opt_text);
        $where_refine_by  = $refine_filter['where'];
        $display_column = 'GROUP_CONCAT(DISTINCT d.url)';
    }
    if($refine_filter_opt == '800' || $refine_filter_opt == 'url') {
        $where_refine_by .= ' AND d.verified = 1';
    }
    if($tab == 1) {
        $where_tab = ' d.brand_id ';
        $records = implode(",",(isset($_SESSION['records_brand_ids']) ? $_SESSION['records_brand_ids'] : array()));
    } else {
        $where_tab = ' adv.adv_id ';
        $records = implode(",",(isset($_SESSION['records_advertiser_ids']) ? $_SESSION['records_advertiser_ids'] : array()));
    }
    $where_records = '';
    // if(!empty($records)) {
    //     $where_records = "AND ".$where_tab." IN (".$records.")";
    // }
    if (!empty($network_code)) {
        $where_network = " AND      d.network_id IN ($network_id) ";
        $column_program = 'GROUP_CONCAT(DISTINCT (if(d.program ="", "Program unknown", d.program)), CONCAT("===", p.program_id ) SEPARATOR "|") as programs, ';
        $table          = ' , program_master  p ';
        $table_join     = '  AND if(d.program = "", "Program unknown", d.program_id) = p.program_id';
    }
   
    if(!empty($program_ids)){
        $where_program = ' AND p.program_id IN ('.$program_ids.')';
    }

    $sql = 'SELECT   ' . CACHE_SQL_QUERY . ' ' . $advOrBrandId . ' ID, GROUP_CONCAT(DISTINCT d.tfn) as  display_tfn_column, GROUP_CONCAT(DISTINCT d.url) as display_url_column,'.$column_program.'
    b.brand_id,
    b.adv_id,
    b.brand_name,
    c.creative_name,
    count(*)                        airings,
    adv.display_name                  advertiser_name,
    adv.need_help,
    c.creative_id,
    c.creative_name,
    min(d.start_date)      AS first_aired_date,
        max(d.start_date)        AS last_aired_date,
        d.network_id,
        '.$cols.'
    FROM    ' . AIRINGS_TABLE . ' d '.$use_index.', creative c,
            brand b,
            advertiser adv '.$table.'
    WHERE    c.brand_id = d.brand_id
    AND      c.creative_id = d.creative_id
    '.$table_join.'
          '.$responseType.'
    AND      d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"
    AND      c.brand_id = b.brand_id
    AND      b.adv_id = adv.adv_id
    '.$where_network. $where_records.'
        '. $where_refine_by . '
        AND     spanish IN ('. $spanish . ') '. $new_filter_opt.$where_flag.$brand_classification.$categories.$where_program.$list_id_condition.'
            GROUP BY ' . $advOrBrandId . ', d.creative_id ORDER BY airings DESC, d.program';
    return $sql;
        
}

function __queries_networks_by_id($params) {
    extract($params);
    $networkId = implode(",",$networksIds);
    $sql = "SELECT network_alias, dpi, network_id FROM network WHERE network_id IN (".$networkId.")";
    return $sql;
}

function __queries_networks_by_dayparts_mf($params) {
    extract($params);
    $where_refine_by = $new_filter_opt = '';
    $networkId = implode(",",$networksIds);
    $sql            = 'select tbl.*, n.network_alias
    from ( SELECT 
                            count(*)                        airings,
                            d.start_weekday,
                            d.network_id,
                            sum(d.'.RATE_COLUMN.')                     total_spend,
                            Count(IF(d.breaktype = "N", 1, NULL)) as national_airings,
                            Count(IF(d.breaktype = "L", 1, NULL)) as local_airings,
                            round(Count(IF(d.breaktype = "N", 1, NULL))/Count(d.airing_id)*100, 0) AS national,
                            SUM(IF(d.breaktype = "N", d.'.RATE_COLUMN.', 0)) as national_spend,
                            SUM(IF(d.breaktype = "L", d.'.RATE_COLUMN.', 0)) as local_spend,
                            round (Count(IF(d.breaktype = "L", 1, NULL))/Count(d.airing_id)*100, 0) AS local,
                            d.gen_daypart_id
                    FROM     '.AIRINGS_TABLE.' d , brand b , creative c '.$adv_table.'
                    WHERE
                        d.start_weekday <= 5
                        AND d.brand_id = b.brand_id
                        AND d.creative_id = c.creative_id '.$adv_condition.'
                        AND d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"  '.$responseType.'
                        AND spanish IN ('. $spanish . ') '. $new_filter_opt.$brand_classification.$categories.$where_refine_by.$list_id_condition.'
                        AND d.network_id  NOT IN (' . get_inactive_networks() . ')
                    GROUP BY d.network_id,d.gen_daypart_id ) tbl inner join network n on n.network_id = tbl.network_id ORDER BY  total_spend DESC';
    return $sql;
}

function __queries_networks_by_dayparts_ss($params) {
    extract($params);
    $where_refine_by = $new_filter_opt = '';
    $networkId = implode(",",$networksIds);
    $sql            = 'select tbl.*, n.network_alias
    from ( SELECT  
                            count(*)                        airings,
                            d.start_weekday,
                            d.network_id,
                            sum(d.'.RATE_COLUMN.')                     total_spend,
                            Count(IF(d.breaktype = "N", 1, NULL)) as national_airings,
                            Count(IF(d.breaktype = "L", 1, NULL)) as local_airings,
                            round(Count(IF(d.breaktype = "N", 1, NULL))/Count(d.airing_id)*100, 0) AS national,
                            SUM(IF(d.breaktype = "N", d.'.RATE_COLUMN.', 0)) as national_spend,
                            SUM(IF(d.breaktype = "L", d.'.RATE_COLUMN.', 0)) as local_spend,
                            round (Count(IF(d.breaktype = "L", 1, NULL))/Count(d.airing_id)*100, 0) AS local,
                            d.gen_daypart_id
                    FROM     '.AIRINGS_TABLE.' d , brand b , creative c '.$adv_table.'
                    WHERE
                        d.start_weekday >= 6
                        AND d.brand_id = b.brand_id
                        AND d.creative_id = c.creative_id '.$adv_condition.'
                        AND d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"  '.$responseType.'
                        AND spanish IN ('. $spanish . ') '. $new_filter_opt.$brand_classification.$categories.$where_refine_by.$list_id_condition.'
                        AND d.network_id  NOT IN (' . get_inactive_networks() . ')
                    GROUP BY d.network_id,d.gen_daypart_id ) tbl inner join network n on n.network_id = tbl.network_id ORDER BY  total_spend DESC;';
    return $sql;
}

function __queries_programs_by_spend($params) {
    extract($params);
    $where_refine_by = $new_filter_opt = '';
    $networkId = implode(",",$networksIds);
    $sql            = 'SELECT tbl.*, IF(n.network_alias IS NULL, "Multiple Networks", n.network_alias) network_alias
                    from (
                        SELECT  
                            count(*)                        airings,
                            IF(COUNT(distinct d.network_id) = 1, d.network_id,"Multiple Networks") AS network,
                            IF(d.program!="", d.program, "Program unknown") AS program,
                             d.start, d.start_date, d.start_hour, d.start_half_hour,
                            '.SPEND_COLUMN1.',
                            Count(IF(d.breaktype = "N", 1, NULL)) as national_airings,
                            Count(IF(d.breaktype = "L", 1, NULL)) as local_airings,
                            round(Count(IF(d.breaktype = "N", 1, NULL)) / Count(d.airing_id) *100 ,0) as nationalP,
                            round(Count(IF(d.breaktype = "L", 1, NULL)) / Count(d.airing_id) *100 ,0) as localP
                    FROM     '.AIRINGS_TABLE.' d , brand b , creative c '.$adv_table.'
                    WHERE
                        d.brand_id = b.brand_id
                        AND d.creative_id = c.creative_id '.$adv_condition.'
                        AND d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"  '.$responseType.'
                        AND spanish IN ('. $spanish . ') '. $new_filter_opt.$brand_classification.$categories.$where_refine_by.$list_id_condition.'
                        AND d.network_id  NOT IN (' . get_inactive_networks() . ')
                    GROUP BY d.program ) tbl
                    LEFT JOIN network n on n.network_id = tbl.network ORDER BY  total_spend DESC;';
    return $sql;
}

function __queries_networks_ranking_report($params) {
    extract($params);
    $where_refine_by = $new_filter_opt = '';
    $sql            = 'SELECT   '.CACHE_SQL_QUERY.' 
                            count(*)                        airings, 
                            d.network_id,
                            count(DISTINCT d.network_code) AS networks,
                            sum(d.'.RATE_COLUMN.')                     total_spend, 
                            Count(IF(d.breaktype = "N", 1, NULL)) as national_airings,
                            Count(IF(d.breaktype = "L", 1, NULL)) as local_airings,
                            round(Count(IF(d.breaktype = "N", 1, NULL))/Count(d.airing_id)*100, 0) AS national,
                            SUM(IF(d.breaktype = "N", d.'.RATE_COLUMN.', NULL)) as national_spend,
                            SUM(IF(d.breaktype = "L", d.'.RATE_COLUMN.', NULL)) as local_spend,
                            round (Count(IF(d.breaktype = "L", 1, NULL))/Count(d.airing_id)*100, 0) AS local
                    FROM     '.AIRINGS_TABLE.' d , brand b , creative c '.$adv_table.'
                    WHERE 
                        d.brand_id = b.brand_id 
                        AND d.creative_id = c.creative_id  '.$adv_condition.'
                        AND d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"  '.$responseType.'
                        AND spanish IN ('. $spanish . ') '. $new_filter_opt.$brand_classification.$categories.$where_refine_by.$list_id_condition.'
                        AND d.network_id  NOT IN (' . get_inactive_networks() . ')
                    GROUP BY d.network_id ORDER BY total_spend DESC';
    return $sql;
}

function __query_summary_ranking_report($params)
{
    $db = getConnection();
    extract($params);
    $where_refine_by = $where_network =  $where_program = $table = $table_join = $column_program = '';
    $use_index  = '';
    $new_filter_opt_header = $new_filter_opt; // $new_filter_opt overriden in below step, hence saving value of new_filter_opt to new var
    // $records        = implode(",",$_SESSION['records_ids']);
    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);
    // $use_index      = getUseIndex($refine_filter_opt);
    if($refine_apply_filter == 1) {
        if ($refine_filter_opt == '800') {
            $refine_filter_array = getRefineTextWithStringFilters($refine_filter_opt_text, $replaced);
            $where_refine_by = 'AND '.$refine_filter_array['colName'].'  LIKE "%' . $refine_filter_array['refine_filter_opt_text'] . '%"';
        } else if ($refine_filter_opt == 'url') {
            // $where_refine_by = 'AND d.url LIKE "%' . $refine_filter_opt_text . '%"';
            $refine_filter    = getUrlFilters($refine_filter_opt_text);
            $where_refine_by  = $refine_filter['where'];
        }

        if($refine_filter_opt == '800' || $refine_filter_opt == 'url') {
            $where_refine_by .= ' AND d.verified = 1';
        }
    }
    if(!empty($network_code)){
        $where_network = ' AND d.network_id IN ('.$network_id.')';
        $table          = ' , program_master  p ';
        $table_join     = '  AND if(d.program = "", "Program unknown", d.program_id) = p.program_id';
    }

    if((!empty($network_code) && empty($program_ids)) || ($apply_filter_called == 1)) {
        $column_program = 'GROUP_CONCAT(DISTINCT (if(d.program ="", "Program unknown", d.program)), CONCAT("===", p.program_id ) SEPARATOR "|") as programs, ';
    }

    if(!empty($program_ids)){
        $where_program = ' AND p.program_id IN ('.$program_ids.')';
    }
    if(empty($network_code)) {
        // $creative_count = $advOrBrandId == 'adv.adv_id' ? 'creative_count' : 'creative_count';
        $creative_or_brand_id = $advOrBrandId == 'adv.adv_id' ? 'b.brand_id' : 'd.creative_id';
        $brand_or_adv_id = $advOrBrandId == 'adv.adv_id' ? 'adv_id' : 'brand_id';

        $columns1 = '
            '.$advOrBrandId.' ID,
            COUNT(DISTINCT '.$creative_or_brand_id.') count
        ';

        $no_of_brands = $advOrBrandId == 'adv.adv_id' ? 'tc.count' : '1';
        $creative_count = $advOrBrandId == 'b.brand_id' ? 'tc.count' : '1';
        $join_tbl = $brand_or_adv_id == 'brand_id' ? 'tc.brand_id = tbl.brand_id' : 'tc.adv_id = tbl.adv_id';
        $columns2 = '
        ID,
        b.brand_id,
         b.adv_id, 
         b.brand_name,
         b.total_weeks, 
         adv.company_name, 
         adv.need_help,
         sum(airings)                     airings,
         1 as   category_count,
         round(sum(ASD) / sum(airings)) as asd,
        1 AS networks,
         sum(d.'.RATE_COLUMN.')                     spend_index,
         adv.display_name                  advertiser_name, 
         0                            AS current_week, 
         round(100*sum(local_airings)/sum(airings))                       AS local,
         round(100*sum(national_airings)/sum(airings))                    AS national,
         '.$cols;

        $sql1 = '

        insert into temptable_creative ('.$brand_or_adv_id.', count)
        SELECT   '.$columns1.'
        FROM     '.SUMMARY_AIRINGS.' d '.$use_index.',  creative c,
                 brand b, 
                 advertiser adv
        WHERE    d.brand_id = c.brand_id
        AND      d.creative_id = c.creative_id
              '.$responseType.'
        AND      d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"
        AND      c.brand_id = b.brand_id 
        AND      b.adv_id = adv.adv_id 
        AND     spanish IN ('. $spanish . ') '. $new_filter_opt.$where_refine_by.$where_flag.$brand_classification.$categories.$list_id_condition.'
                GROUP BY '.$advOrBrandId.';';
        $stmt = $db->prepare($sql1);
        $stmt->execute();

        $sql2 = 'SELECT '.$advOrBrandId.'
                '.$columns2.'
        FROM     '.SUMMARY_AIRINGS.' d '.$use_index.',  creative c,
                 brand b,
                 advertiser adv
        WHERE    d.brand_id = c.brand_id
        AND      d.creative_id = c.creative_id
              '.$responseType.'
        AND      d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"
        AND      c.brand_id = b.brand_id
        AND      b.adv_id = adv.adv_id
        AND     spanish IN ('. $spanish . ') '. $new_filter_opt.$where_refine_by.$where_flag.$brand_classification.$categories.$list_id_condition.'
                GROUP BY '.$advOrBrandId;
        $sql = "SELECT tbl.*, $no_of_brands as no_of_brands, $creative_count as creative_count  FROM ( ".$sql2.") tbl
        inner join temptable_creative  tc on $join_tbl
        ORDER BY spend_index DESC;";
    } else if(!empty($network_code)){
        $sql = 'SELECT  '.$advOrBrandId.' ID,'.$column_program.'
        b.brand_id,
         b.adv_id, 
         b.brand_name,
         b.total_weeks, 
         adv.company_name, 
         adv.need_help,
         count(*)                        airings, 
         count(DISTINCT b.brand_id)      no_of_brands, 
         count(distinct d.creative_id)   creative_count,
         1 as   category_count,
         d.network_id,
         count(DISTINCT d.network_code) AS networks,
         sum(d.'.RATE_COLUMN.')                     spend_index, 
         adv.display_name                  advertiser_name, 
         0                            AS current_week, 
         round(avg(d.length)) AS asd,
         round(Count(IF(d.breaktype = "N", 1, NULL))/Count(d.airing_id)*100, 0) AS national,
         round (Count(IF(d.breaktype = "L", 1, NULL))/Count(d.airing_id)*100, 0) AS local,
         '.$cols.' 
        FROM     '.AIRINGS_TABLE.' d '.$use_index.',  creative c,
                 brand b, 
                 advertiser adv '.$table.'
        WHERE    d.brand_id = c.brand_id
        AND      d.creative_id = c.creative_id
            '.$table_join.'
              '.$responseType.'
        AND      d.start_date BETWEEN  "'.$sd.'"  AND "'.$ed.'"
        AND      c.brand_id = b.brand_id 
        AND      b.adv_id = adv.adv_id 
        '.$where_network.'
        AND     spanish IN ('. $spanish . ') '. $new_filter_opt.$where_refine_by.$where_flag.$brand_classification.$categories.$where_program.$list_id_condition.'
                GROUP BY '.$advOrBrandId.' ORDER BY spend_index DESC  ,d.program';
    }else {
        $sql = 'SELECT    '.$advOrBrandId.' ID,'.$column_program.'
                         b.adv_id, 
                         b.brand_id,
                         b.brand_name, 
                         b.total_weeks,
                         d.network_id,
                         count(distinct d.creative_id)             creative_count,
                         1 as   category_count,
                         sum(airings) airings,
                         round(sum(ASD) / sum(airings)) as asd,
                         round(100*sum(local_airings)/sum(airings))                       AS local,
                         round(100*sum(national_airings)/sum(airings))                    AS national,
                         adv.company_name,
                         adv.need_help, 
                         count(DISTINCT b.brand_id)                no_of_brands, 
                         NULL   AS networks, 
                         sum(d.'.RATE_COLUMN.')                                  spend_index, 
                         adv.display_name                               advertiser_name, 
                         0 as current_week,
                        '.$cols.' 
                FROM     '.SUMMARY_AIRINGS.' d , advertiser adv, brand b, creative c
                WHERE    d.start_date BETWEEN "'.$sd.'"
                AND       "'.$ed.'"  '.$new_filter_opt.'
                AND d.brand_id = b.brand_id 
                AND d.creative_id = c.creative_id 
                 '.$responseType.'
                AND b.adv_id = adv.adv_id  
                AND spanish IN ('. $spanish .') '.$where_flag.$brand_classification.$categories.$where_program.$list_id_condition.'
                GROUP BY '.$advOrBrandId. ' 
                ORDER BY '.$export_order_by ;
    }
  
    return $sql;
}

$queries['_sql_get_quarter_dates'] = "SELECT " . CACHE_SQL_QUERY . " MIN(media_month_start) as min_month_date,
    MAX(media_month_end) as max_month_date
FROM   `media_calendar`
WHERE  media_month IN ( %d, %d )
       AND media_year = %d
GROUP  BY media_year ";

function __SQL_GET_CURRENT_QUARTER_DATES($params)
{
    extract($params);

    $sql = "SELECT " . CACHE_SQL_QUERY . " MIN(media_qtr_start) as min_qtr_date, MAX(media_qtr_end) as max_qtr_date
            FROM  `media_calendar` WHERE  media_month = " . $month_start . " AND media_qtr = " . $quarter . " AND media_year = " . $year . " GROUP  BY media_year ";

    return $sql;
}

function __SQL_GET_PREV_QUARTER_DATES($params){
    extract($params);

    $sql = "SELECT " . CACHE_SQL_QUERY . " MIN(media_qtr_start) as min_qtr_date, MAX(media_qtr_end) as max_qtr_date "
        . " FROM   `media_calendar`  WHERE  media_qtr = " . $prev_qtr . " AND media_year = " . $year . " GROUP  BY media_year ";

    return $sql;
}

function __SQL_GET_LAST_MEDIA_MONTH($params)
{
    extract($params);

    $sql = "SELECT MIN(media_month_start) as min_date, MAX(media_month_end) as max_date FROM media_calendar WHERE media_year = " . $prev_year . " AND media_month =" . $calendar_id;

    return $sql;
}
$queries['_SQL_GET_CURRENT_QUARTER_DATES'] = "SELECT " . CACHE_SQL_QUERY . " MIN(media_qtr_start) as min_qtr_date,
    MAX(media_qtr_end) as max_qtr_date
FROM   `media_calendar`
WHERE  media_month = %d AND media_qtr = %d
       AND media_year = %d
GROUP  BY media_year ";

function __queries_display_brand_creatives($params)
{
    extract($params);
    $program_params = getProgramParams($params);

    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);

    if (!empty($network_code)) {
        $sql = "SELECT " . CACHE_SQL_QUERY . " c.creative_id,
         b.brand_name,
         b.brand_id,
         adv.adv_id,
         c.creative_name,
         c.price,
         c.payments,
         c.class,
         c.spanish,
         b.brand_id parent_id,
         c.type,
         c.length,
         c.thumbnail,
         c.is_active,
         c.response_tfn,
         c.response_url,
         c.response_sms,
         c.response_mar,
         adv.display_name,
         adv.need_help,
         Count(*) airings , SUM(d.".RATE_COLUMN.") as spend_index ,ROUND(SUM(d.".RATE_COLUMN."), 0) AS total_spend $cols
         min(d.start)      AS first_aired_date,
         max(d.start)        AS last_aired_date,
         1 AS                        category_count,
         sum(case when (c.spanish = 1) THEN 1 ELSE 0 END)  as spanish_creative_count,
         sum(case when (c.spanish = 0) THEN 1 ELSE 0 END)  as english_creative_count,
         count(c.creative_id) as total_creative_count
        FROM   " . AIRINGS_TABLE . " d
            JOIN     creative c
                ON       d.creative_id = c.creative_id
                AND      c.spanish IN ($spanish)
                AND      $responseType
            JOIN     brand b
                ON       c.brand_id = b.brand_id
                AND      b.brand_id = $brand_id
            JOIN     advertiser adv
                ON       b.adv_id = adv.adv_id
                ".$program_params['join_condition']."
                ".$program_params['table_join_on']."
        WHERE
            d.start_date BETWEEN  '$sd'  AND '$ed'
            $brand_classification
            AND      d.network_id IN ($network_id) $new_filter_opt ".$program_params['where_program']."
        GROUP BY c.creative_id, c.creative_id
        ORDER BY $order_by";
    } else {
        $sql = "SELECT   " . CACHE_SQL_QUERY . " c.creative_id,
            b.brand_name,
            b.brand_id,
            adv.adv_id,
            c.creative_name,
            c.price,
            c.payments,
            c.class,
            c.spanish,
            b.brand_id parent_id,
            c.type,
            c.length,
            c.thumbnail,
            c.is_active,
            c.response_tfn,
            c.response_url,
            c.response_sms,
            c.response_mar,
            adv.display_name,
            adv.need_help,
            SUM(airings)                                       airings,
            SUM(".RATE_COLUMN.") as spend_index,
            ROUND(SUM(".RATE_COLUMN."), 0) AS total_spend,
            Round(100*Sum(local_airings)/Sum(airings), 0)         AS local,
            Round(100*Sum(national_airings)/Sum(airings), 0)      AS national,
            1 AS                        category_count,
            c.first_detection AS first_aired_date,
            c.last_aired AS last_aired_date,
            sum(case when (c.spanish = 1) THEN 1 ELSE 0 END)  as spanish_creative_count,
            sum(case when (c.spanish = 0) THEN 1 ELSE 0 END)  as english_creative_count,
            count(c.creative_id) as total_creative_count
    FROM     " . SUMMARY_AIRINGS . " d,
             creative c,
             brand b,
             advertiser adv
    WHERE    d.creative_id = c.creative_id
    AND      c.spanish IN ($spanish)
    AND      $responseType
    AND      d.brand_id = b.brand_id
    AND      b.brand_id = $brand_id
    AND      b.adv_id = adv.adv_id
    AND      d.start_date >= '$sd 00:00:00'
    AND      d.start_date <= '$ed 23:59:59' $brand_classification $new_filter_opt
    GROUP BY c.creative_id
    ORDER BY $order_by";
    }
    return $sql;
}

function __queries_get_last_aired_date($params)
{
    extract($params);

    $sql = "SELECT creative_id, MAX(start) as last_aired_date, network_code
    FROM   airings
    WHERE
        creative_id IN ($creative_ids) and network_id = '$network_id'
    GROUP BY creative_id, network_id";

    return $sql;
}

function __queries_get_last_aired_date_with_breaktype($params)
{
    extract($params);

    if ($breaktype == 'A') {
        $breaktype_condition = ' 1 = 1 ';
    } elseif ($breaktype == 'N') {
        $breaktype_condition = ' breaktype = "N" ';
    } elseif ($breaktype == 'D') {
        $breaktype_condition = ' breaktype = "L" ';
    }

    $sql = "SELECT creative_id, MAX(start) as last_aired_date, network_code
    FROM   airings
    WHERE
        creative_id IN ($creative_ids) and network_id = '$network_id' AND $breaktype_condition
    GROUP BY creative_id, network_id";

    return $sql;
}

function __query_rosday_trend_graph($params)
{
    extract($params);
    
    $new_filter_opt = newFilter($new_filter_opt,$start_date,$end_date);
    // $program_params = getProgramParams($params);
    // if($checked_network_graph_opt == 'airings') {
        $col = "    Count(IF(d.breaktype = 'L', 1, NULL)) AS sum_loc,
        Count(IF(d.breaktype = 'N', 1, NULL)) AS sum_nat, " ;
    // } else {
        $col_spend = ' ROUND(SUM(IF(d.breaktype = "L", d.'.RATE_COLUMN.', 0)), 0) AS sum_loc_spend,
        ROUND(SUM(IF(d.breaktype = "N",d.'.RATE_COLUMN.', 0)), 0) as sum_nat_spend,';
    // }
    $sql = "SELECT ".CACHE_SQL_QUERY." start_week, 
                date_end, 
                Sum(sum_nat) AS sum_nat, 
                Sum(sum_loc) AS sum_loc, 
                SUM(sum_nat_spend) AS sum_nat_spend,
                SUM(sum_loc_spend) AS sum_loc_spend,
                media_week_start, 
                media_week_end, 
                media_year, 
                media_week 
         FROM   (SELECT d.start_week,
                        Unix_timestamp(media_week_end) * 1000 AS date_end, 
                        ".$col."
                        ".$col_spend."
                        media_week_start, 
                        media_week_end, 
                        m.media_year, 
                        m.media_week 
                 FROM   ".AIRINGS_TABLE." d
                        INNER JOIN creative c 
                                ON c.creative_id = d.creative_id
                                   AND $responseType
                                   AND c.spanish IN ($spanish)
                                   " . NULL_ALL_CONDITION . "
                                   AND d.network_id = $nw_id
                        INNER JOIN brand b
                                ON b.brand_id = c.brand_id
                        INNER JOIN advertiser adv ON adv.adv_id = b.adv_id

                        INNER JOIN media_calendar m
                                ON m.media_week = d.start_week
                                   AND $where_condition
                                   $brand_classification
                                   $new_filter_opt
                                   AND m.media_year = d.start_year
                 GROUP  BY media_week,
                           media_year
                 UNION ALL
                 SELECT media_week                            AS calendar_id,
                        Unix_timestamp(media_week_end) * 1000 AS date_end,
                        0                                     AS sum_nat,
                        0                                     AS sum_loc,
                        0                                    AS sum_nat_spend,
                        0                                    AS sum_loc_spend,
                        media_week_start,
                        media_week_end,
                        media_year,
                        media_week
                 FROM   media_calendar
                 WHERE  media_week_end <= '$max_date'
                        AND media_week_start >= '$first_detection_date' ) temp
         GROUP  BY media_week,
                   media_year
         ORDER  BY `temp`.`media_week_start` ASC ";
    return $sql;
}


//get trend graph for a creative daypart
function __queries_airings_trend_sql($params)
{
    extract($params);

    $new_filter_opt = newFilter($new_filter_opt,$sd,$ed);
    // $program_params = getProgramParams($params);

        $col = "    Count(IF(d.breaktype = 'L', 1, NULL)) AS sum_loc,
        Count(IF(d.breaktype = 'N', 1, NULL)) AS sum_nat, " ;
        $col_spend = 'ROUND(SUM(IF(d.breaktype = "L", d.'.RATE_COLUMN.', 0)), 0) AS sum_loc_spend ,
        ROUND(SUM(IF(d.breaktype = "N", d.'.RATE_COLUMN.', 0)) , 0) as sum_nat_spend,';

   $sql = "SELECT ".CACHE_SQL_QUERY." start_week, 
                date_end, 
                Sum(sum_nat) AS sum_nat, 
                Sum(sum_loc) AS sum_loc, 
                SUM(sum_nat_spend) AS sum_nat_spend,
                SUM(sum_loc_spend) AS sum_loc_spend,
                media_week_start, 
                media_week_end, 
                media_year, 
                media_week 
         FROM   (SELECT d.start_week,
                        Unix_timestamp(media_week_end) * 1000 AS date_end, 
                        ".$col.$col_spend."
                        media_week_start, 
                        media_week_end, 
                        m.media_year, 
                        m.media_week 
                 FROM   ".AIRINGS_TABLE." d
                        INNER JOIN creative c 
                                ON c.creative_id = d.creative_id
                                   AND  $responseType 
                                   AND c.spanish IN ($spanish)
                                   " . NULL_ALL_CONDITION . "
                                   AND  daypart  = '$day_short $rosTime'
                                   AND d.network_id = '$network_id'
                        INNER JOIN brand b
                                ON b.brand_id = c.brand_id
                        INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
                                
                        INNER JOIN media_calendar m
                                ON m.media_week = d.start_week
                                   AND $where_condition
                                   $brand_classification
                                   $new_filter_opt
                                   AND m.media_year = d.start_year
                                   
                 GROUP  BY media_week,
                           media_year
                 UNION ALL
                 SELECT media_week                            AS calendar_id,
                        Unix_timestamp(media_week_end) * 1000 AS date_end,
                        0                                     AS sum_nat,
                        0                                     AS sum_loc,
                        0 AS sum_loc_spend,
                        0 AS sum_nat_spend,
                        media_week_start,
                        media_week_end,
                        media_year,
                        media_week
                 FROM   media_calendar
                 WHERE  media_week_end <= '$max_date'
                        AND media_week_start >= '$first_detection_date' ) temp
         GROUP  BY media_week,
                   media_year
         ORDER  BY `temp`.`media_week_start` ASC ";
    return $sql;
}

function __query_get_airings($params)
{
    extract($params);
    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);
    // $program_params = getProgramParams($params);

    if ($classification > 5) {
//        $where_length               = 'a.length > '.LENGTH;
        //        $where_creative_length      = 'c.length > '.LENGTH;
        $daypart = ' 1= 1';

    } else {
//        $where_length           = ' a.length <= '.LENGTH;
        //        $where_creative_length  = 'c.length <= '.LENGTH;
        $daypart = " d.daypart = '" . $day_short . ' ' . $start_time . '-' . $end_time . "'";
    }
    
      
    if($creative_id!= ''){
        $where_condition = 'c.creative_id = '.$creative_id;
    }else{
        $where_condition = ' c.brand_id = '.$brand_id;
    }
    
    $breaktype = ($breaktype == 'D') ? 'L' : $breaktype;
    
 
    $sql = "SELECT ".CACHE_SQL_QUERY." d.airing_id,
           c.creative_name, 
           d.length,
           c.creative_id, 
           d.start,
           d.program,
           d.".RATE_COLUMN." as total_spend,
          dayname(d.start)  AS dow,DATE_FORMAT(d.start, '%H:%i') as  start_time , d.daypart,
          DATE_FORMAT(d.start, '%a') as short_day
    FROM       brand b
    INNER JOIN creative c 
    ON b.brand_id = c.brand_id AND  $responseType  AND  spanish IN ($spanish)
    INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
    INNER JOIN " . AIRINGS_TABLE . " d
    ON       c.creative_id = d.creative_id  AND  spanish IN ($spanish)
    " . PREVIOUSDATE . "
    AND  d.start_date >= '$start_date' AND start_date <= '$end_date' " . NULL_START_HOUR . " " . NULL_START_WEEKDAY . "
    AND  $daypart
    AND  d.breaktype = '$breaktype'
    AND  $where_condition $brand_classification $new_filter_opt
    INNER JOIN network n
    ON    d.network_id=n.network_id AND n.status = 1 AND n.network_id = $network_id 
    GROUP BY d.airing_id
    ORDER BY $export_order_by";
    return $sql;
}

$queries['_sql_get_calendar_details_for_quarter'] = "SELECT " . CACHE_SQL_QUERY . " GROUP_CONCAT(media_week) AS calendar_id, media_year FROM media_calendar WHERE media_qtr = %s and media_year = %s ";

$queries['_sql_get_calendar_details_for_ytd'] = "SELECT " . CACHE_SQL_QUERY . " GROUP_CONCAT(media_week) AS calendar_id, media_year FROM media_calendar WHERE media_year = %s ";

//insert search text into search log to idenfity alt text.
$queries['_sql_insert_search_entry'] = "INSERT INTO search_log (tab, search_text, user_id, results_count, time)
VALUES ('%s', '%s' ,'%s', %d, NOW())";

$queries['_sql_display_categories'] = "SELECT " . CACHE_SQL_QUERY . " main_sub_category_id,
       alt_sub_category_id,
       cat.*
FROM   brand b
       INNER JOIN categories cat
               ON b.main_sub_category_id = cat.sub_category_id
                   OR b.alt_sub_category_id = cat.sub_category_id
WHERE  brand_id = '%s'  order by FIELD (cat.sub_category_id, main_sub_category_id, alt_sub_category_id)";

$queries['_sql_display_filters'] = "SELECT " . CACHE_SQL_QUERY . " media_date_range,
       classification,
       response_type,
       excel_for,
       language,
       network_ids,
       dow,
       hod
FROM   excel_exports
WHERE  id = %s ";

function __query_get_daypart_x_axis($params)
{
    extract($params);
    // $program_params = getProgramParams($params);
    $new_filter_opt = newFilter($new_filter_opt,$start_date,$end_date);
    $col = "Count(IF(d.breaktype = 'L', 1, NULL)) AS loc_airings, Count(IF(d.breaktype = 'N', 1, NULL)) AS nat_airings ";
    $sql_get_daypart_x_axis = "SELECT ".CACHE_SQL_QUERY." d.length,
                Substring(d.daypart, 1, 7)              AS rosDay,
                Substring(d.daypart, 8)                 AS rosTime,
                ".$col.",
                ".SPEND_COLUMN."
         FROM   ".AIRINGS_TABLE." d
                INNER JOIN creative c 
                        ON c.creative_id = d.creative_id
                           AND   $responseType 
                           AND c.spanish IN ($spanish)
                           AND d.start_date >= '$start_date'
                           AND d.start_date <= '$end_date'
                           AND $where_cond
                           AND d.network_id = '$nw_id'
                INNER JOIN brand b
                        ON b.brand_id = c.brand_id
                        $brand_classification
                INNER JOIN advertiser adv ON adv.adv_id = b.adv_id

         WHERE  d.daypart != ''
                AND d.daypart != '------- -'
                AND d.daypart != '------- --:--:--'
                $new_filter_opt
         GROUP  BY rosday,
                   rostime
         ORDER  BY rostime,
                   rosday ";
    return $sql_get_daypart_x_axis;
}

function __query_network_airings_hod_bar_graph($params_network_airings_hod_bar)
{
    extract($params_network_airings_hod_bar);

    $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';

    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);

    if ($network != 'all_networks') {
        $nw_condition = "  AND a.network_id IN ($network_id)";
    }

    if ($days != 'all_day') {
        $day_condition = "  AND start_weekday IN ($days)";
    }

    if ($hours != 'all_hour') {
        $hour_condition = "  AND start_hour IN ($hours)";
    }

    if ($dayparts != 'all_dayparts') {
        $daypart_condition = "  AND gen_daypart_id IN ($dayparts)";
    }

    if ($area == 'brand' || $area == 'adv') {
        $id_condition = "c.brand_id = $id";
    } else {
        $id_condition = "c.creative_id = $id";
    }

    if (!empty($breaktype)) {
        if ($breaktype == "L") {
            $airings = ' Count(IF(a.breaktype = "L", 1, NULL)) airings_count';
        } elseif ($breaktype == "N") {
            $airings = ' Count(IF(a.breaktype = "N", 1, NULL)) airings_count';
        } else {
            $airings = ' count(*) AS airings_count ';
        }
    } else {
        $airings = ' count(*) AS airings_count ';
    }

    $sql = "SELECT " . CACHE_SQL_QUERY . " start_year,
       start_hour,
       breaktype,
       Count(IF(a.breaktype = 'L', 1, NULL)) local_airings_count,
       Count(IF(a.breaktype = 'N', 1, NULL)) national_airings_count,
       $airings
    FROM  " . AIRINGS_TABLE . " a
       INNER JOIN creative  c
               ON c.creative_id = a.creative_id
        INNER JOIN brand b
               ON c.brand_id = b.brand_id
        INNER JOIN network n
               ON n.network_id = a.network_id /*AND n.status = 1*/ AND n.network_id not in (".get_inactive_networks().")
        INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
                  AND start_year IS NOT NULL
                  AND start_hour IS NOT NULL
                  AND start_week IS NOT NULL
                  AND start >= '$start_date 00:00:00' AND start <= '$end_date 23:59:59' " . NULL_ALL_CONDITION . "
                  $day_condition
                  $hour_condition
                  $daypart_condition
                  $nw_condition
                  $brand_classification
                  $new_filter_opt
      AND $responseType
      AND c.spanish IN ($spanish)
      AND $id_condition
            GROUP  BY start_hour, breaktype";
    return $sql;
}

function __query_network_airings_dayparts_bar_graph($params_network_airings_hod_bar)
{
    extract($params_network_airings_hod_bar);

    $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';

    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);

    if ($network != 'all_networks') {
        $nw_condition = "  AND a.network_id IN ($network_ids)";
    }

    if ($days != 'all_day') {
        $day_condition = "  AND start_weekday IN ($days)";
    }

    if ($hours != 'all_hour') {
        $hour_condition = "  AND start_hour IN ($hours)";
    }

    if ($dayparts != 'all_dayparts') {
        $daypart_condition = "  AND gen_daypart_id IN ($dayparts)";
    }

    if ($area == 'brand' || $area == 'adv') {
        $id_condition = "c.brand_id = $id";
    } else {
        $id_condition = "c.creative_id = $id";
    }

    if (!empty($breaktype)) {
        if ($breaktype == "L") {
            $airings = ' Count(IF(a.breaktype = "L", 1, NULL)) airings_count';
        } elseif ($breaktype == "N") {
            $airings = ' Count(IF(a.breaktype = "N", 1, NULL)) airings_count';
        } else {
            $airings = ' count(*) AS airings_count ';
        }
    } else {
        $airings = ' count(*) AS airings_count ';
    }

    $sql = "SELECT " . CACHE_SQL_QUERY . " start_year,
       start_hour,
       gen_daypart_id,
       breaktype,
       Count(IF(a.breaktype = 'L', 1, NULL)) local_airings_count,
       Count(IF(a.breaktype = 'N', 1, NULL)) national_airings_count,
       $airings
    FROM  " . AIRINGS_TABLE . " a
       INNER JOIN creative  c
               ON c.creative_id = a.creative_id
        INNER JOIN brand b
               ON c.brand_id = b.brand_id
        INNER JOIN network n
               ON n.network_id = a.network_id /*AND n.status = 1*/ AND n.network_id not in (".get_inactive_networks().")
        INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
                  AND start_year IS NOT NULL
                  AND start_hour IS NOT NULL
                  AND start_week IS NOT NULL
                  AND start >= '$start_date 00:00:00' AND start <= '$end_date 23:59:59' " . NULL_ALL_CONDITION . "
                  $day_condition
                  $hour_condition
                  $daypart_condition
                  $nw_condition
                  $brand_classification
                  $new_filter_opt
      AND $responseType
      AND c.spanish IN ($spanish)
      AND $id_condition
            GROUP  BY gen_daypart_id, breaktype";
    
     return $sql;
}

function __query_programs($params) {
    extract($params);
    $db = getConnection();
    $stmt = $db->prepare('SET GLOBAL group_concat_max_len = 1000000;');
    //  show($sql);
    $stmt->execute();
    $id_condition = '';
    if($id != '') {
        if($area == 'brand' || $area == 'adv') {
            $id_condition = " AND d.brand_id = $id";
        }else{
            $id_condition = " AND d.creative_id = $id";
        }
    }

    $program_params = getProgramParams($params);
    $sql = "SELECT GROUP_CONCAT(DISTINCT (if(d.program ='', 'Program unknown', d.program)), CONCAT('===', p.program_id ) SEPARATOR '|') as programs, d.network_id from airings d
            INNER JOIN creative c ON d.creative_id = c.creative_id
            INNER JOIN brand b ON d.brand_id = b.brand_id
            INNER JOIN  program_master p on d.program_id = p.program_id
            WHERE d.network_id IN (".$network_id.")
            AND d.start_date >= '$start_date' AND d.start_date <= '$end_date'
            $brand_classification
            AND $responseType
            AND c.spanish IN ($spanish)
           $id_condition ORDER by d.program";
    return $sql;
}

/*
 * Display bar graph for a brand or creative
 */
function __query_network_airings_graph($params){
    extract($params);
    
    $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';
    
    $new_filter_opt = newFilter($new_filter_opt,$start_date,$end_date);
    $program_params = getProgramParams($params);

    if($network != 'all_networks'){
        $nw_condition = "  AND d.network_id IN ($network_ids)";
    }
    
    if($days != 'all_day'){
        $day_condition = "  AND d.start_weekday IN ($days)";
    }
    
    if($hours != 'all_hour'){
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }
    if($dayparts != 'all_dayparts'){
        $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }
    
    if($area == 'brand' || $area == 'adv'){
        $id_condition = "c.brand_id = $id";
    }else{
        $id_condition = "c.creative_id = $id";
    }
    if($active_tab == 'dow') {
        $col = 'd.start_weekday';
    } else if ($active_tab == 'hod') {
       $col = 'd.start_hour';
    } else {
       $col = 'd.gen_daypart_id';
    }
   if (!empty($breaktype)) {
       if ($breaktype == "D") {
           $airings            = ' Count(IF(d.breaktype = "L", 1, NULL)) airings_count';
       } elseif ($breaktype == "N") {
           $airings            = ' Count(IF(d.breaktype = "N", 1, NULL)) airings_count';
       } else {
           $airings            = ' count(*) AS airings_count ';
       }
   } else {
       $airings            = ' count(*) AS airings_count ';
   }
    
    $sql = "SELECT ".CACHE_SQL_QUERY." d.start_year,
     ".$col.",
      d.breaktype,
      Count(IF(d.breaktype = 'L', 1, NULL)) local_airings_count,
      Count(IF(d.breaktype = 'N', 1, NULL)) national_airings_count,nw.dpi,
      $airings,
        SUM(d.".RATE_COLUMN.") as total_spend,
        ROUND(SUM(IF(d.breaktype = 'N', ".AIRINGS_RATE_COLUMN.", 0)) , 0)  as national_spend,
        ROUND(SUM(IF(d.breaktype = 'L',  ".AIRINGS_RATE_COLUMN.", 0)) , 0)  as local_spend
   FROM    ".AIRINGS_TABLE." d
      INNER JOIN creative  c
              ON c.creative_id = d.creative_id
              AND d.network_id not in (".get_inactive_networks().")
      INNER JOIN brand b
              ON c.brand_id = b.brand_id 
       INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
       INNER JOIN network nw ON nw.network_id = d.network_id
       ".$program_params['join_condition']. $program_params['table_join_on']."
                 AND d.start_date >= '$start_date' AND d.start_date <= '$end_date' ".NULL_ALL_CONDITION."
                 $day_condition  
                 $hour_condition 
                 $daypart_condition 
                 $nw_condition
                 $brand_classification
                 $new_filter_opt
     AND $responseType
     AND c.spanish IN ($spanish)

     AND $id_condition   AND d.network_id NOT IN (" . get_inactive_networks() . ")  ".$program_params['where_program']."
           GROUP  BY ".$col.", d.breaktype ";
    return $sql;
}


function __query_network_spend_trend_graph($params) {
    extract($params);
    $program_params = getProgramParams($params);
    $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';
      
      $new_filter_opt = newFilter($new_filter_opt,$sd,$ed);
      
     if($network != 'all_networks'){
         $nw_condition = "  AND d.network_id IN ($network_id)";
     }
     
     if($days != 'all_day'){
         $day_condition = "  AND d.start_weekday IN ($days)";
     }
     
     if($hours != 'all_hour'){
         $hour_condition = "  AND d.start_hour IN ($hours)";
     }
     if($dayparts != 'all_dayparts'){
         $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
     }
     
     if($area == 'brand' || $area == 'adv'){
         $id_condition = "c.brand_id = $id";
     }else{
         $id_condition = "c.creative_id = $id";
     }

     if (!empty($breaktype)) {
        if ($breaktype == "D") {
            $airings            = ' Count(IF(d.breaktype = "L", 1, NULL)) airings_count';
        } elseif ($breaktype == "N") {
            $airings            = ' Count(IF(d.breaktype = "N", 1, NULL)) airings_count';
        } else {
            $airings            = ' count(*) AS airings_count ';
        }
    } else {
        $airings            = ' count(*) AS airings_count ';
    }
     
     
     
    $sql =  "SELECT ".CACHE_SQL_QUERY." d.start_year, 
          d.start_week, 
          d.breaktype,
          m.media_week_start,
          $airings,
         ".TOTAL_SPEND."
    FROM    ".AIRINGS_TABLE." d
    inner join media_calendar m on d.start_year = m.media_year and d.start_week=m.media_week
            AND d.network_id not in (".get_inactive_networks().")
          INNER JOIN creative c
                  ON c.creative_id = d.creative_id 
            INNER JOIN brand b
               ON c.brand_id = b.brand_id 
            INNER JOIN advertiser adv ON adv.adv_id = b.adv_id

            ".$program_params['join_condition'] .$program_params['table_join_on']." 
                  AND d.start_date >= '$max_start_date' AND d.start_date <= '$max_end_date' ".NULL_ALL_CONDITION."
                  $day_condition
                  $hour_condition
                  $daypart_condition
                  $nw_condition
                  $brand_classification
                  $new_filter_opt
                  AND $responseType
                  AND c.spanish IN ($spanish)
                  AND $id_condition   ".$program_params['where_program']."

               GROUP  BY d.start_year, d.start_week, d.breaktype";
    return $sql;
}


function __query_network_airings_trend_graph($params_network_airings_dow_trend){
     extract($params_network_airings_dow_trend);
     $program_params = getProgramParams($params_network_airings_dow_trend);
     
      $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';
      
      $new_filter_opt = newFilter($new_filter_opt,$sd,$ed);
      
     if($network != 'all_networks'){
         $nw_condition = "  AND d.network_id  IN ($network_ids)";
     }
     
     if($days != 'all_day'){
         $day_condition = "  AND d.start_weekday IN ($days)";
     }
     
     if($hours != 'all_hour'){
         $hour_condition = "  AND d.start_hour IN ($hours)";
     }

     if($dayparts != 'all_dayparts'){
         $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
     }
     
     if($area == 'brand' || $area == 'adv'){
         $id_condition = "c.brand_id = $id";
     }else{
         $id_condition = "c.creative_id = $id";
     }
     
     
    $sql =  "SELECT ".CACHE_SQL_QUERY." d.start_year,
          d.start_week,
          d.breaktype,
          count(*) AS airings_count,
          ".TOTAL_SPEND."
    FROM    " . AIRINGS_TABLE . " d
        INNER JOIN creative c
                  ON c.creative_id = d.creative_id
                  AND d.network_id not in (".get_inactive_networks().")
            INNER JOIN brand b
               ON c.brand_id = b.brand_id
            INNER JOIN advertiser adv ON adv.adv_id = b.adv_id

            ".$program_params['join_condition'] .$program_params['table_join_on']."
                  AND d.start_year IS NOT NULL
                  AND d.start_hour IS NOT NULL
                  AND d.start_week IS NOT NULL
                  AND d.start_date >= '$max_start_date' AND d.start_date <= '$max_end_date' " . NULL_ALL_CONDITION . "
                  $day_condition
                  $hour_condition
                  $daypart_condition
                  $nw_condition
                  $brand_classification
                  $new_filter_opt
                  AND $responseType
                  AND c.spanish IN ($spanish)

                  AND $id_condition AND d.network_id NOT IN (" . get_inactive_networks() . ")  ".$program_params['where_program']."
               GROUP  BY start_year, start_week, breaktype";
    return $sql;
}

function __query_networks_with_live_date()
{
    $sql = "SELECT " . CACHE_SQL_QUERY . " network_code,
       network_alias, dpi, diginet,
       live_date
    FROM network WHERE status = 1 AND hidden = 0
    ORDER BY network_alias ";
    return $sql;
}

function __query_network_spend_index_graph($params){
   
    extract($params);
    $program_params = getProgramParams($params);
     
    $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';
    
    $new_filter_opt = newFilter($new_filter_opt,$start_date,$end_date);
    
    if($network != 'all_networks'){
        $nw_condition = "  AND d.network_id  IN ($network_ids)";
    }
    
    if($days != 'all_day'){
        $day_condition = "  AND d.start_weekday IN ($days)";
    }
    
    if($hours != 'all_hour'){
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }
    if($dayparts != 'all_dayparts'){
        $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    } 
    
    if($area == 'brand' || $area == 'adv'){
        $id_condition = "c.brand_id = $id";
    }else{
        $id_condition = "c.creative_id = $id";
    }
    if($active_tab == 'dow') {
        $col = 'd.start_weekday';
    } else if ($active_tab == 'hod') {
       $col = 'd.start_hour';
    } else {
       $col = 'd.gen_daypart_id';
    }
//     if (!empty($breaktype)) {
//        if ($breaktype == "L") {
//            $airings            = ' SUM(IF(d.breaktype = "L", '.RATE_COLUMN.', 0)) AS local_airings_count';
//        } elseif ($breaktype == "N") {
//            $airings            = ' SUM(IF(d.breaktype = "N", '.RATE_COLUMN.', 0)) as national_airings_count';            
//        } else {
//            $airings            = ' SUM(d.'.RATE_COLUMN.') as airings_count';
//        }
//    } else {
//        $airings            = ' SUM(d.'.RATE_COLUMN.') as airings_count ';
//    }
    
    $sql = "SELECT ".CACHE_SQL_QUERY." d.start_year, 
      ".$col.",
      d.breaktype,n.dpi,
     ".SPEND_COLUMN.",
      Count(*) airings_count,
      Count(IF(d.breaktype = 'L', 1, NULL)) local_airings_count,
      Count(IF(d.breaktype = 'N', 1, NULL)) national_airings_count
   FROM  ".AIRINGS_TABLE." d
      INNER JOIN creative  c
              ON c.creative_id = d.creative_id 
       INNER JOIN brand b
              ON c.brand_id = b.brand_id
       INNER JOIN network n
            ON n.network_id = d.network_id
       INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
       ".$program_params['join_condition'] .$program_params['table_join_on']." 
                 AND d.start_date >= '$start_date' AND d.start_date <= '$end_date' ".NULL_ALL_CONDITION."   
                 $day_condition
                 $hour_condition 
                 $daypart_condition 
                 $nw_condition
                 $brand_classification
                 $new_filter_opt
                 ".$program_params['where_program']."
     AND $responseType
     AND c.spanish IN ($spanish)
     /*AND n.status = 1*/
     AND d.network_id not in (".get_inactive_networks().")
     AND $id_condition AND d.network_id NOT IN (" . get_inactive_networks() . ") 
           GROUP  BY ".$col.", breaktype";
    return $sql;
}

function __query_network_airings_spend_index($params_network_airings_spend_index){
    extract($params_network_airings_spend_index);

    $sql = "SELECT r.media_year,
       calendar_id,
       spend_index
        FROM   `rankings` r
        INNER JOIN brand b  ON r.brand_id = b.brand_id
        INNER JOIN creative c ON b.brand_id = c.brand_id
        INNER JOIN media_calendar m ON r.media_year = m.media_year AND r.calendar_id = m.media_week
        WHERE r.`calendar_type` LIKE 'W' 
        ".$classification_condition."
           AND $responseType
       AND r.brand_id = $id  
       AND m.media_week_start >= '".$start_date."'
       AND ranking_type = '".$where_spanish."brand' group by media_year, calendar_id" ;
     return $sql;
}

function __query_excel_export_summary($params)
{
    extract($params);
    $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';

    $new_filter_opt = newFilter($new_filter_opt, $from_date_condition, $to_date_condition);
    $program_params = getProgramParams($params);

    if ($networks != 'all_networks') {
        $nw_condition = "  AND d.network_id IN (" . $network_ids . ")";
    }

    if ($days != 'all_day') {
        $day_condition = "  AND d.start_weekday IN ($days)";
    }

    if ($hours != 'all_hour') {
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }

    if ($dayparts != 'all_dayparts') {
        $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }

    $date_condition = 'd.start_date >= "' . $from_date_condition . '"
                  AND d.start_date <= "' . $to_date_condition . '"';

    if ($inc_cmw == 1) {
        $date_condition = "((" . $date_condition . ") OR ( d.start_date >= '" . $current_sd . "' AND d.start_date <= '" . $current_ed . "'))";
    }

    $daypart_condition_column = '';
    if ($report_length == 'short') {
        $daypart_condition_column = "d.daypart,";
    }

    if (!empty($breaktype)) {
        if ($breaktype == "D") {
            $breaktype_condition = ' d.breaktype = "L"';
        } elseif ($breaktype == "N") {
            $breaktype_condition = ' d.breaktype = "N"';
        } else {
            $breaktype_condition = ' 1=1';
        }
    } else {
        $breaktype_condition = ' 1=1';
    }

    $whereSql = " FROM   " . AIRINGS_TABLE . " d
                      INNER JOIN network
                              ON network.network_id = d.network_id /*AND network.status = 1*/ AND network.network_id not in (".get_inactive_networks().")
                      INNER JOIN creative c
                              ON c.creative_id = d.creative_id
                      INNER JOIN brand b
                              ON b.brand_id = c.brand_id
                      INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
                      ".$program_params['join_condition']. $program_params['table_join_on']."
                      ".$program_params['where_program']."
                      $category_condition
                      $new_filter_opt
               WHERE  $id_condition  $brand_classification AND $date_condition
                   $report_length_condition $hour_condition $daypart_condition AND d.start_hour IS NOT NULL $day_condition AND d.start_week IS NOT NULL and d.start_weekday IS NOT NULL AND d.start_hour IS NOT NULL $nw_condition AND c.spanish IN ($spanish)  $responseType_condition
                group by d.creative_id, " . $daypart_condition_column . " d.network_id,d.length ";

    if ($count == 0) {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' d.airing_id,
                      b.main_sub_category_id, b.alt_sub_category_id,
                      d.network_code,
                      ' . $start_column . ',
                      ' . $end_column . ',
                      d.length,
                      d.daypart,
                      d.breakType,
                      c.creative_name,
                      c.creative_id,
                      b.brand_name,
                      network.network_alias,
                      network.network_code,
                      1 AS                        category_count,
                      d.verified,
                      count(*) count,
                      count(if(d.breakType = "L", 1, NULL)) AS local,
                      count(if(d.breakType = "N", 1, NULL)) AS national,
                      round(Count(IF(d.breaktype = "N", 1, NULL)) / Count(d.airing_id) *100 ,2) as nationalP,
                      round(Count(IF(d.breaktype = "L", 1, NULL)) / Count(d.airing_id) *100 ,2) as localP,
                  '.SPEND_COLUMN.'
                       ' . $whereSql . ' ORDER BY total_spend DESC, c.creative_name ASC,d.length ASC';
    } else {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' COUNT(*) AS count FROM (SELECT 1 ' . $whereSql . ') temp';
    }

    return $sql;
}
//concat(airings.creative_id, airings.daypart, airings.network_code, airings.length) as concat //, concat
function __query_excel_export_airing($params)
{
    extract($params);
    $nw_condition = $day_condition = $hour_condition = $breaktype_condition = $new_daypart_condition = '';

    $new_filter_opt = newFilter($new_filter_opt, $from_date_condition, $to_date_condition);

    if($screen == 'one') {
        $program_params = getProgramParams($params);
    } else {
        $program_params['table'] = '';
        $program_params['table_join'] = '';
        $program_params['join_condition'] = '';
        $program_params['where_program'] = '';
        $program_params['table_join_on'] = '';
    }

    if ($networks != 'all_networks') {
        $nw_condition = "  AND d.network_id IN ($network_ids)";
    }

    if ($days != 'all_day') {
        $day_condition = "  AND d.start_weekday IN ($days)";
    }

    if ($hours != 'all_hour') {
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }

    if ($dayparts != 'all_dayparts') {
        $new_daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }

    $colName = ($day_type == 'broadcast') ? 'start' : 'start_date';
    $date_condition = 'd.'.$colName.' >= "' . $from_date_condition . '"
                  AND d.'.$colName.' <= "' . $to_date_condition . '"';
 
    if ($inc_cmw == 1) {
        $date_condition = "((" . $date_condition . ") OR ( d.".$colName." >= '" . $current_sd . "' AND d.".$colName." <= '" . $current_ed . "'))";
    }

    $daypart_condition = '';
    if ($rosDayTime != '') {
        $daypart_condition = " AND d.daypart = '" . $rosDayTime . "'";
    }

    if ($breaktype != '') {
        $breaktype_condition = " AND d.breaktype = '" . $breaktype . "'";
    }

    $whereSql = " FROM   " . AIRINGS_TABLE . " d

                      INNER JOIN network
                             ON network.network_id = d.network_id /*AND network.status = 1*/ AND network.network_id not in (".get_inactive_networks().")
                      INNER JOIN creative c
                              ON c.creative_id = d.creative_id
                      INNER JOIN brand b
                              ON b.brand_id = c.brand_id
                      INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
                      ".$program_params['join_condition']. $program_params['table_join_on']."
                      ".$program_params['where_program']."
                      $category_condition
                       $new_filter_opt
               WHERE   $id_condition  $brand_classification AND $date_condition $daypart_condition $breaktype_condition
                      AND c.spanish IN ($spanish)
                      $responseType_condition
                   $report_length_condition $hour_condition $new_daypart_condition and d.start_hour IS NOT NULL $day_condition AND d.start_week IS NOT NULL and d.start_weekday IS NOT NULL AND d.start_hour IS NOT NULL $nw_condition  ";

    if ($count == 0) {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' d.airing_id,
                       d.network_code,
                       ' . $start_column . ',
                       ' . $end_column . ',
                       d.length,
                       d.daypart,
                       d.gen_daypart_id,
                       d.breakType,
                       c.creative_name,
                       c.creative_id,
                       b.brand_name,
                       network.network_alias,
                       network.network_code,
                       1 AS                       category_count,
                       d.tfn, d.url, d.promo, d.program,
                       d.verified,
                       c.thumb_url,
                       ROUND(d.'.RATE_COLUMN.', 0) rate,
                       '.$dow.' as dow,
                       concat(d.creative_id, d.daypart, d.network_code, d.length) as concat ' . $whereSql . ' GROUP BY d.airing_id ORDER BY d.start DESC';
    } else {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' COUNT(*) AS count FROM (SELECT 1 ' . $whereSql . ') temp';
    }
    return $sql;
}

function __query_fetch_excel_export_data($params)
{
    extract($params);
    $sql = "SELECT id, file_path  from excel_exports WHERE id = " . $id;
    return $sql;
}

function __query_update_filename_excel_export($params)
{
    extract($params_update_report);
    $sql = "UPDATE excel_exports SET file_path = '" . addslashes($file_path) . "' WHERE id = " . $id;
    return $sql;
}

function __query_update_for_my_report($params_update_report)
{
    extract($params_update_report);
    $sql = "UPDATE excel_exports SET file_path = '" . addslashes($file_path) . "' WHERE id = " . $id;
    return $sql;
}

function __query_update_for_my_filters($params_for_my_filter)
{
    extract($params_for_my_filter);
    $sql = "UPDATE user_filters SET name = '" . $name . "' WHERE id = " . $id;
    return $sql;
}

function __query_get_all_report_data()
{
    $sql = "SELECT id,file_path from excel_exports WHERE user_id = " . $_SESSION['user_id'] . " AND environment_id = '".ENVIRONMENT_ID."' AND status <> 'deleted';";
    return $sql;
}

function __query_get_all_filter_data($params)
{
    extract($params);

    $sql = "SELECT * from user_filters WHERE user_id = " . $_SESSION['user_id'] . " AND page = '" . $page . "' AND status <> 'deleted'";
    return $sql;
}

function __query_get_report_data_for_id($params_for_my_report)
{
    extract($params_for_my_report);

    if (!empty($id)) {
        $sql = "SELECT id,file_path,no_of_records from excel_exports WHERE id = " . $id;
    } else {
        $sql = "SELECT id,file_path,no_of_records from excel_exports WHERE file_path LIKE '% " . $file_name . "%' AND environment_id = '".ENVIRONMENT_ID."';";
    }

    return $sql;
}

function __query_excel_export_for_my_report($params_excel_export_for_my_report)
{
    extract($params_excel_export_for_my_report);
    $_order_by = '';
    $where = '';
    if (isset($order_by)) {
        $_order_by = $order_by;
    }

    if (isset($id)) {
        $where = ' AND ex.id <> ' . $id;
    }

    $sql = "SELECT ex.id,
            exs.parent_id,
            ex.email,
            ex.user_id,
            ex.report_type,
            ex.excel_for,
            ex.excel_for_id,
            ex.category_ids,
            ex.network_ids,
            ex.requested_on,
            DATE_ADD(ex.`requested_on`, INTERVAL ".EXCEL_FILE_EXPIRY_DAYS." DAY) as valid_till,
            DATE_ADD(if(ex.`shared_date` is null, now(), ex.`shared_date`), INTERVAL ".EXCEL_FILE_EXPIRY_DAYS." DAY) as shared_valid_till,
            ex.file_path,
            ex.filesize,
            ex.header_text,
            ex.email_alert,
            ex.media_date_range,
            ex.status,
            concat(exu.first_name, ' ', exu.last_name) as full_name,
            ex.shared_by,
            ex.progress,
            ex.shared_date
            FROM 
            excel_exports exs RIGHT JOIN 
            excel_exports ex ON ex.id = exs.parent_id AND exs.user_id = ".$userId."
            LEFT JOIN user exu  ON ex.shared_by = exu.user_id
            # INNER JOIN user u ON ex.user_id = u.user_id
            WHERE ex.user_id = " . $userId . " AND ex.status != 'deleted' AND CURDATE() <= DATE_ADD(ex.`requested_on`, INTERVAL ".EXCEL_FILE_EXPIRY_DAYS." DAY)  AND ex.environment_id = '".ENVIRONMENT_ID."' " . $where . $_order_by;
    return $sql;
}

function __query_excel_export_summary_network($params)
{
    extract($params);
    $nw_condition = $day_condition = $hour_condition = $new_daypart_condition = '';
    $program_params = getProgramParams($params);

    if ($networks != 'all_networks') {
        $nw_condition = "  AND d.network_id IN ($network_ids)";
    }

    if ($days != 'all_day') {
        $day_condition = "  AND d.start_weekday IN ($days)";
    }

    if ($hours != 'all_hour') {
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }

    if ($dayparts != 'all_dayparts') {
        $new_daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }

    $date_condition = 'd.start_date >= "' . $from_date_condition . '"
                  AND d.start_date <= "' . $to_date_condition . '"';

    if ($inc_cmw == 1) {
        $date_condition = "((" . $date_condition . ") OR ( d.start_date >= '" . $current_sd . "' AND d.start_date <= '" . $current_ed . "'))";
    }

    $daypart_condition = '';
    if ($report_length == 'short') {
        $daypart_condition = "d.daypart,";
    }

    if (!empty($breaktype)) {
        if ($breaktype == "D") {
            $airings = ' Count(IF(d.breaktype = "L", 1, NULL)) count';
            $creatives_count = ' Count(DISTINCT(IF(d.breaktype = "L", d.creative_id, NULL))) ccount';
        } elseif ($breaktype == "N") {
            $airings = ' Count(IF(d.breaktype = "N", 1, NULL)) count';
            $creatives_count = ' Count(DISTINCT(IF(d.breaktype = "N", d.creative_id, NULL))) ccount';
        } else {
            $airings = ' count(d.airing_id) count';
            $creatives_count = ' COUNT(DISTINCT(d.creative_id)) as ccount';
        }
    } else {
        $airings = ' count(d.airing_id) count';
        $creatives_count = ' COUNT(DISTINCT(d.creative_id)) as ccount';
    }

    $whereSql = ' FROM   ' . AIRINGS_TABLE . ' d
                      INNER JOIN network
                              ON network.network_id = d.network_id /*AND network.status = 1*/ AND network.network_id not in ('.get_inactive_networks().')
                      INNER JOIN creative c
                              ON c.creative_id = d.creative_id
                      INNER JOIN brand b
                              ON b.brand_id = c.brand_id
                      ' . $category_condition . '
                      '.$program_params["join_condition"]. $program_params["table_join_on"].'
                      '.$program_params["where_program"].'
               WHERE  ' . $id_condition . ' AND ' . $date_condition . $brand_classification . '
                   ' . $report_length_condition . '  ' . $hour_condition . $day_condition . $new_daypart_condition . $nw_condition . $responseType_condition . NULL_ALL_CONDITION . ' AND spanish IN (' . $spanish . ')
                group by ' . $daypart_condition . ' d.network_id, d.length having ccount <> 0 ';

    if ($count == 0) {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' network.network_alias,
                       network.network_code,
                       1 AS   category_count,
                       '.$creatives_count.',
                      '.SPEND_COLUMN.',
                       d.daypart,
                       d.breakType,
                       d.length,
                       '.$airings.',
                       count(if(d.breakType = "L", 1, NULL)) AS local,
                      count(if(d.breakType = "N", 1, NULL)) AS national,
                      round(Count(IF(d.breaktype = "N", 1, NULL)) / Count(d.airing_id) *100 ,2) as nationalP,
                      round(Count(IF(d.breaktype = "L", 1, NULL)) / Count(d.airing_id) *100 ,2) as localP
                         ' . $whereSql . ' ORDER BY total_spend DESC ';
    } else {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' COUNT(*) AS count FROM (SELECT 1 ' . $whereSql . ') temp';
    }
    return $sql;
}

function __query_get_current_media_week()
{
    $current_date = date('Y-m-d');

    $current_media_week = "SELECT " . CACHE_SQL_QUERY . " pk, media_week, media_week_start,media_week_end FROM media_calendar WHERE media_week_start <= '" . $current_date . "' AND  media_week_end >= '" . $current_date . "' GROUP BY media_week";

    return $current_media_week;
}

/*
 * TO fetch brand name, company name for a creative on Rosdayparts page.
 */
function __query_get_creative_details($params)
{
    extract($params);

    $sql = "SELECT " . CACHE_SQL_QUERY . "      b.brand_id,
                        b.adv_id,
                        b.brand_name,
                        a.display_name as company_name,
                        c.length,
                        c.creative_id,
                        c.creative_name
            FROM       brand b
            INNER JOIN creative c
            ON         c.brand_id = b.brand_id
            INNER JOIN advertiser a
            ON         a.adv_id = b.adv_id
            AND        c.spanish IN (" . $spanish . ")
            AND        c.creative_id = " . $creative_id;

    return $sql;
}

/*
 * TO fetch brand name, company name for a network on Rosdayparts page.
 */
function __query_get_network_details($params)
{
    extract($params);

    $sql = "SELECT " . CACHE_SQL_QUERY . " b.brand_id,
       b.adv_id,
       b.brand_name,
       adv.display_name   as company_name, n.dpi
FROM   " . AIRINGS_TABLE . " a
       INNER JOIN creative c
               ON a.creative_id = c.creative_id
                  AND start >= '$start_date 00:00:00'
                  AND start <= '$end_date 23:59:59'
                  AND spanish IN ($spanish)
                  AND c.brand_id = $brand_id
                  AND c.class != 'BRAND'
       INNER JOIN brand b
               ON b.brand_id = c.brand_id
       INNER JOIN advertiser adv
               ON adv.adv_id = b.adv_id
       INNER JOIN network n
               ON n.network_id = a.network_id
                  AND n.status = 1
                  AND n.network_id = $nw_id
GROUP  BY a.network_id";
    return $sql;
}

/*
 * Get all the networks for selected brand or creative
 */
function __query_display_networks_list($params)
{
    extract($params);
    $db = getConnection();
    $stmt = $db->prepare('SET GLOBAL group_concat_max_len = 10000000;');
    $stmt->execute();
    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);
    $program_params = getProgramParams($params);
    if ($tab == 'brand' || $tab == 'adv') {
        $brand_creative_id_column = "c.brand_id = $id ";
    } else {
        $brand_creative_id_column = "c.creative_id = $id ";
    }

    if(isset($custome_filter)){
        $manual_filter = $custome_filter;
    } else {
        $manual_filter = '';
    }
    $sql_fetch_networks = "SELECT " . CACHE_SQL_QUERY . " n.network_name, n.network_code,n.network_id, n.network_alias,n.dpi 
     FROM   " . AIRINGS_TABLE . " d
            INNER JOIN network n
                    ON d.network_id = n.network_id /*AND n.status = 1*/ AND n.network_id not in (".get_inactive_networks().")
            INNER JOIN creative c
                    ON d.creative_id = c.creative_id
                    ".$program_params['join_condition'] . $program_params['table_join_on']."
                       AND $brand_creative_id_column
                       AND c.spanish IN ($spanish) AND c.class != 'BRAND' $manual_filter
                       AND d.start_date >= '$start_date' AND d.start_date <= '$end_date' " . NULL_ALL_CONDITION . "
            INNER JOIN brand b ON b.brand_id = c.brand_id
            INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
     $join_category  $where_category $cr_type $new_filter_opt ".$program_params['where_program']."
     $network_filter
          GROUP  BY d.network_id
          ORDER BY $order_by ,d.program";
    return $sql_fetch_networks;

}

/*
 * Get all the programs for networks for selected brand or creative
 */
function __query_display_network_programs_list($params)
{
    extract($params);
    $db = getConnection();
    $stmt = $db->prepare('SET GLOBAL group_concat_max_len = 10000000;');
    $stmt->execute();
    $program_params = getProgramParams($params);
    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);

    if ($tab == 'brand' || $tab == 'adv') {
        $brand_creative_id_column = "c.brand_id = $id ";
    } else {
        $brand_creative_id_column = "c.creative_id = $id ";
    }

    if(isset($custome_filter)){
        $manual_filter = $custome_filter;
    } else {
        $manual_filter = '';
    }

    $sql_fetch_networks = "SELECT " . CACHE_SQL_QUERY . "
    n.network_id,  if(d.program ='', 'Program unknown', d.program) as program, p.program_id,n.network_name, n.network_code,n.network_id, n.network_alias,n.dpi 
     FROM   " . AIRINGS_TABLE . " d
            INNER JOIN network n
                    ON d.network_id = n.network_id /*AND n.status = 1*/ AND n.network_id not in (".get_inactive_networks().")
            INNER JOIN  program_master p
                    ON if(d.program = '', 'Program unknown', d.program_id) = p.program_id
            INNER JOIN creative c
                    ON d.creative_id = c.creative_id
                       AND $brand_creative_id_column
                       AND c.spanish IN ($spanish) AND c.class != 'BRAND' $manual_filter
                       AND d.start_date >= '$start_date' AND d.start_date <= '$end_date' " . NULL_ALL_CONDITION . "
            INNER JOIN brand b ON b.brand_id = c.brand_id
            INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
     $join_category  $where_category $cr_type $new_filter_opt ".$program_params['where_program']."
     $network_filter 
          GROUP  BY d.network_id, p.program_id
          ORDER BY $order_by ,d.program";
     return $sql_fetch_networks;

}

function __query_checked_programs_list($params) {
    extract($params);
    $db = getConnection();

    $sql = "SELECT 
    IF(d.program_name !='', d.program_name, 'Program unknown') AS program, d.program_id
     FROM   program_master d
          WHERE  program_id IN ($program_ids)";
     return $sql;
}

/*
 * Display bar graph for a brand or creative
 */

function __query_network_airings_dow_bar_graph($params_network_airings_dow_bar)
{
    extract($params_network_airings_dow_bar);

    $nw_condition = $day_condition = $hour_condition = $daypart_condition = '';

    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);

    if ($network != 'all_networks') {
       $nw_condition = "  AND a.network_id IN ($network_ids)";
    }

    if ($days != 'all_day') {
        $day_condition = "  AND start_weekday IN ($days)";
    }

    if ($hours != 'all_hour') {
        $hour_condition = "  AND start_hour IN ($hours)";
    }

    if ($dayparts != 'all_dayparts') {
        $daypart_condition = "  AND gen_daypart_id IN ($dayparts)";
    }

    if ($area == 'brand' || $area == 'adv') {
        $id_condition = "c.brand_id = $id";
    } else {
        $id_condition = "c.creative_id = $id";
    }

    if (!empty($breaktype)) {
        if ($breaktype == "L") {
            $airings = ' Count(IF(a.breaktype = "L", 1, NULL)) airings_count';
        } elseif ($breaktype == "N") {
            $airings = ' Count(IF(a.breaktype = "N", 1, NULL)) airings_count';
        } else {
            $airings = ' count(*) AS airings_count ';
        }
    } else {
        $airings = ' count(*) AS airings_count ';
    }

    $sql = "SELECT " . CACHE_SQL_QUERY . " start_year,
       start_weekday,
       breaktype,
       nw.dpi,
       Count(IF(a.breaktype = 'L', 1, NULL)) local_airings_count,
       Count(IF(a.breaktype = 'N', 1, NULL)) national_airings_count,
       $airings
    FROM    " . AIRINGS_TABLE . " a
       INNER JOIN creative  c
               ON c.creative_id = a.creative_id
       INNER JOIN brand b
               ON c.brand_id = b.brand_id
        INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
        INNER JOIN network nw ON nw.network_id = a.network_id
                  AND start_date >= '$start_date 00:00:00' AND start_date <= '$end_date 23:59:59' " . NULL_ALL_CONDITION . "
                  $day_condition
                  $hour_condition
                  $daypart_condition
                  $nw_condition
                  $brand_classification
                  $new_filter_opt
      AND $responseType
      AND c.spanish IN ($spanish)
      /*AND nw.status = 1*/ AND nw.network_id not in (".get_inactive_networks().")
      AND $id_condition
            GROUP  BY start_weekday, breaktype ";
    return $sql;
}

/*
 * Get first detection date of network for brand or creative.
 */

function __queries_get_brand_first_detection($params)
{
    extract($params);

    $nw_condition = '';

    if ($network_string != 'all_networks') {
        $nw_condition = " AND network_start.network_id IN (" . $network_id . ") AND";
    } else {
        $minDetectionDateCondition = " WHERE " . $minDetectionDateCondition;
    }

    $sql = "SELECT " . CACHE_SQL_QUERY . " MIN(first_detection) as first_detection_date
    FROM network_start
    INNER JOIN network ON network.network_id = network_start.network_id
    " . $nw_condition . "
    " . $minDetectionDateCondition . "  and first_detection > '" . LIFETIME_START_DATE . "' group By " . $groupBy;
    //show($sql, 1); exit;
    return $sql;
}

/*
 * Display networks for a brand
 */
function __queries_display_brand_networks($params)
{
    extract($params);
    $program_params = getProgramParams($params);
    $nw_condition  = $day_condition = $hour_condition = $daypart_condition = '';

    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);
    if (!empty($network_id) && $network != "''" && $network != 'all_networks') {
        if(is_array($network_id)) {
            $network_id = implode(",",$network_id);
        }
        $nw_condition = "  AND d.network_id IN ($network_id)";
    }

    if($days != 'all_day' && $days != ''){
        $day_condition = "  AND d.start_weekday IN ($days)";
    }
    if($hours != 'all_hour' && $hours != ''){
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }
    if($dayparts != 'all_dayparts' && $dayparts != ''){
        $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }

    if ($tab == 'brand') {
        $brand_creative_id_column = " c.brand_id = $id ";
        $creative_network_log_condition = '';
        $creative_last_airing_details = '';
    } else {
        $brand_creative_id_column = " c.creative_id = $id";
        $creative_network_log_condition = '';
        $creative_last_airing_details = '';
    }

    if (!empty($breaktype)) {
        if ($breaktype == "D") {
            $airings = ' Count(IF(d.breaktype = "L", 1, NULL)) airings';
            $creatives_count = ' Count(DISTINCT(IF(d.breaktype = "L", d.creative_id, NULL))) creatives_count';
            $asd = ' IF(d.breaktype = "L", sum(d.length) / count(d.airing_id), 0) asd';
        } elseif ($breaktype == "N") {
            $airings = ' Count(IF(d.breaktype = "N", 1, NULL)) airings';
            $creatives_count = ' Count(DISTINCT(IF(d.breaktype = "N", d.creative_id, NULL))) creatives_count';
            $asd = ' IF(d.breaktype = "N", sum(d.length) / count(d.airing_id), 0) asd';
        } else {
            $airings = ' count(d.airing_id) airings';
            $creatives_count = ' Count(DISTINCT( d.creative_id )) creatives_count';
            $asd = ' sum(d.length) / count(d.airing_id) AS asd';
        }
    } else {
        $airings = ' count(d.airing_id) airings';
        $creatives_count = ' Count(DISTINCT( d.creative_id )) creatives_count';
        $asd = ' sum(d.length) / count(d.airing_id) AS asd';
    }

    // $sql_fetch_brand_creative_networks = "SELECT " . CACHE_SQL_QUERY . " n.network_name, n.network_code,n.network_id, n.network_alias,n.dpi, $id as ID, count(DISTINCT d.program) as program_count,
    //         $airings, $creative_last_airing_details
    //         $creatives_count,
    //         count(d.airing_id) total_airings,
    //         Count(IF(d.breaktype = 'N', 1, NULL))    AS nat,
    //         Count(IF(d.breaktype = 'L', 1, NULL))    AS loc,
    //         round(Count(IF(d.breaktype = 'N', 1, NULL)) / Count(d.airing_id) *100 ,2) as nationalP,
    //         round(Count(IF(d.breaktype = 'L', 1, NULL)) / Count(d.airing_id) *100 ,2) as localP,
    //         ".SPEND_COLUMN1.",".
    //         $asd. ",
    //         thumbnail 
    //  FROM   ".AIRINGS_TABLE." d
    //         INNER JOIN network n
    //                 ON d.network_id = n.network_id /*AND n.status = 1*/ AND d.network_id not in (".get_inactive_networks().")
    //         INNER JOIN creative c
    //                 ON d.creative_id = c.creative_id
    //                    AND $brand_creative_id_column
    //                    AND c.spanish IN ($spanish)
    //                    $day_condition
    //                    $hour_condition
    //                    $daypart_condition
    //                    $nw_condition
    //                    AND d.start_date >= '$start_date' AND d.start_date <= '$end_date' " . NULL_ALL_CONDITION . "
    //         INNER JOIN brand b ON b.brand_id = c.brand_id
    //         ".$program_params['join_condition'] . $program_params['table_join_on']."
    //         $creative_network_log_condition
    //         $brand_classification $new_filter_opt
    //  AND   $responseType   ".$program_params['where_program']."
    //  GROUP  BY d.network_id
    //       ORDER BY $order_by";

          $sql_fetch_brand_creative_networks = "SELECT 
          n.network_name,
          n.network_code,
          n.network_id,
          n.network_alias,
          n.dpi, tbl.*
      from( SELECT $id as ID, COUNT(DISTINCT d.program) AS program_count,
            COUNT(d.airing_id) airings,
            COUNT(DISTINCT (d.creative_id)) creatives_count,
            COUNT(d.airing_id) total_airings,
            COUNT(IF(d.breaktype = 'N', 1, NULL)) AS nat,
            COUNT(IF(d.breaktype = 'L', 1, NULL)) AS loc,
            ROUND(COUNT(IF(d.breaktype = 'N', 1, NULL)) / COUNT(d.airing_id) * 100,
                    0) AS nationalP,
            ROUND(COUNT(IF(d.breaktype = 'L', 1, NULL)) / COUNT(d.airing_id) * 100,
                    0) AS localP,
            SUM(IF(d.breaktype = 'N', d.rate, 0)) AS nat_spend,
            SUM(IF(d.breaktype = 'L', d.rate, 0)) AS loc_spend,
            SUM(d.rate) AS total_spend,
            SUM(d.length) / COUNT(d.airing_id) AS asd,
            thumbnail, d.network_id  
            FROM  ".AIRINGS_TABLE." d use index (idx_ranking3)
                INNER JOIN
            creative c ON d.creative_id = c.creative_id
            ".$program_params['join_condition'] . $program_params['table_join_on']."
            AND $brand_creative_id_column
            AND c.spanish IN ($spanish)
            $day_condition
            $hour_condition
            $daypart_condition
            $nw_condition
            AND d.start_date >= '$start_date' AND d.start_date <= '$end_date' " . NULL_ALL_CONDITION . "
        INNER JOIN brand b ON b.brand_id = c.brand_id
      
        $creative_network_log_condition
        $brand_classification $new_filter_opt
        AND   $responseType   ".$program_params['where_program'] ." GROUP BY d.network_id) tbl INNER JOIN
        network n ON tbl.network_id = n.network_id
        ORDER BY $order_by";
    return $sql_fetch_brand_creative_networks;
}

function __query_get_programs_view_for_network($params) { // Summary export cron
    extract($params);
    $day_condition = $hour_condition = $daypart_condition = $nw_condition = '';
    $program_params = getProgramParams($params);
    // $nw_condition = "  AND d.network_id IN ($network_id)";
    if (!empty($network_id)) {
        if(is_array($network_id)) {
            $network_id = implode(",",$network_id);
        }
        $nw_condition = "  AND d.network_id IN ($network_id)";
    }
    if($days != 'all_day' && $days != ''){
        $day_condition = "  AND d.start_weekday IN ($days)";
    }
    if($hours != 'all_hour' && $hours != ''){
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }
    if($dayparts != 'all_dayparts' && $dayparts != ''){
        $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }

    if(isset($day_short) && $day_short != '' && $c_dir <= 5) {
        $daypart = " AND d.daypart = '" . $day_short . ' ' . $start_time . '-' . $end_time . "'";
    }
    if($area == 'brand') {
        $brand_creative_id_column = " c.brand_id = $excel_for_id ";
        $colName = 'b.brand_name as name, ';
    } else {
        $brand_creative_id_column = " c.creative_id = $excel_for_id ";
        $colName = 'c.creative_name as name, b.brand_name as brand_name,';
    }

    $date_condition = 'd.start_date >= "' . $from_date_condition . '"
    AND d.start_date <= "' . $to_date_condition . '"';

    if ($inc_cmw == 1) {
        $date_condition = "((" . $date_condition . ") OR ( d.start_date >= '" . $current_sd . "' AND d.start_date <= '" . $current_ed . "'))";
    }
    // $brand_creative_id_column = " c.brand_id = $brand_id ";
    $airings = ' count(d.airing_id) total_airings';
    $spendAirings = isset($spendAirings) ? $spendAirings : 'total_spend';

    $sql = "SELECT " . CACHE_SQL_QUERY . " network_alias, d.program, $colName
            Count(IF(d.breaktype = 'N', 1, NULL)) national_airings , Count(IF(d.breaktype = 'L', 1, NULL)) local_airings ,
            count(d.airing_id) total_airings,
            round(Count(IF(d.breaktype = 'N', 1, NULL)) / Count(d.airing_id) *100 ,0) as nationalP,
            round(Count(IF(d.breaktype = 'L', 1, NULL)) / Count(d.airing_id) *100 ,0) as localP,
            ".SPEND_COLUMN1."
     FROM   ".AIRINGS_TABLE." d
            INNER JOIN creative c
                    ON d.creative_id = c.creative_id
                       AND $brand_creative_id_column
                       AND c.spanish IN ($spanish)
                       $day_condition
                       $hour_condition
                       $daypart_condition
                       $nw_condition
                        AND   $date_condition ".  NULL_ALL_CONDITION . "
            INNER JOIN network n ON d.network_id = n.network_id
            INNER JOIN brand b ON b.brand_id = c.brand_id
            ".$program_params['join_condition'] . $program_params['table_join_on']."
            INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
            $brand_classification
     AND   $responseType  ".$program_params['where_program']."  AND d.network_id  NOT IN (" . get_inactive_networks() . ")
     group by d.network_id, d.program
     ORDER BY  $spendAirings desc, d.program  asc";
    return $sql;
}

function __query_get_programs_view($params)
{
    extract($params);
    $program_params = getProgramParams($params);
    $day_condition = $hour_condition = $daypart_condition = '';
    $nw_condition = "  AND d.network_id IN ($network_id)";
    if(!isset($breaktype)) {
        $breaktype = '';
    }
    if($days != 'all_day'){
        $day_condition = "  AND d.start_weekday IN ($days)";
    }
    if($hours != 'all_hour'){
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }
    if($dayparts != 'all_dayparts'){
        $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }
    if($area == 'brand') {
        $brand_creative_id_column = " c.brand_id = $id ";
        $colName = 'b.brand_name as name, ';
    } else {
        $brand_creative_id_column = " c.creative_id = $id ";
        $colName = 'c.creative_name as name,b.brand_name as brand_name, ';
    }
    $airings = ' count(d.airing_id) total_airings';
    $daypart = ' AND  1= 1';
      
    if(isset($day_short) && $day_short != '' && $c_dir <= 5) {
        $daypart = " AND d.daypart = '" . $day_short . ' ' . $start_time . '-' . $end_time . "'";
    }

    $sql_fetch_brand_creative_networks = "SELECT " . CACHE_SQL_QUERY . " d.program, d.airing_id, d.start, d.start_date, d.start_hour, d.start_half_hour,
            Count(IF(d.breaktype = 'N', 1, NULL)) national_airings , Count(IF(d.breaktype = 'L', 1, NULL)) local_airings ,
            count(d.airing_id) total_airings,
            round(Count(IF(d.breaktype = 'N', 1, NULL)) / Count(d.airing_id) *100 ,0) as nationalP,
            round(Count(IF(d.breaktype = 'L', 1, NULL)) / Count(d.airing_id) *100 ,0) as localP,
            ".$colName."
            ".SPEND_COLUMN1."
     FROM   ".AIRINGS_TABLE." d
            INNER JOIN creative c
                    ON d.creative_id = c.creative_id
                       AND $brand_creative_id_column
                       AND c.spanish IN ($spanish)
                       $day_condition
                       $hour_condition
                       $daypart_condition
                       $daypart  $breaktype
                       $nw_condition
                       AND d.start_date >= '$sd' AND d.start_date <= '$ed' " . NULL_ALL_CONDITION . "
            INNER JOIN brand b ON b.brand_id = c.brand_id
            ".$program_params['join_condition'] . $program_params['table_join_on']."
            INNER JOIN advertiser adv ON adv.adv_id = b.adv_id
            $brand_classification
     AND   $responseType  ".$program_params['where_program']." group by d.program
     ORDER BY total_spend desc, d.program  asc";
    return $sql_fetch_brand_creative_networks;
}

/*
 * Display creatives for a network
 */
function __queries_display_network_creatives_sql($params)
{
    extract($params);
    $day_condition = $hour_condition = $daypart_condition = '';
//    if($classification > 5){
    //        $length = ' c.length > '. LENGTH ;
    //    }else{
    //        $length = ' c.length <= '.LENGTH ;
    //    }
    $new_filter_opt = newFilter($new_filter_opt, $start_date, $end_date);
    $program_params = getProgramParams($params);
    if($days != 'all_day' && $days != ''){
        $day_condition = "  AND d.start_weekday IN ($days)";
    }
    if($hours != 'all_hour' && $hours != ''){
        $hour_condition = "  AND d.start_hour IN ($hours)";
    }
    if($dayparts != 'all_dayparts' && $dayparts != ''){
        $daypart_condition = "  AND d.gen_daypart_id IN ($dayparts)";
    }
    if (!empty($breaktype)) {
        if ($breaktype == "D") {
            $airings = ' Count(IF(d.breaktype = "L", 1, NULL)) airings';
            //$first_aired_date       = ' MIN(IF(a.breaktype = "L", a.start, NULL)) first_aired_date';
            $last_aired_date = ' MAX(IF(d.breaktype = "L", d.start, NULL)) last_aired_date';
        } elseif ($breaktype == "N") {
            $airings = ' Count(IF(d.breaktype = "N", 1, NULL)) airings';
            //$first_aired_date       = ' MIN(IF(a.breaktype = "N", a.start, NULL)) first_aired_date';
            $last_aired_date = ' MAX(IF(d.breaktype = "N", d.start, NULL)) last_aired_date';
        } else {
            $airings = ' Count(d.airing_id) airings';
            //$first_aired_date       = ' MIN(a.start) first_aired_date';
            $last_aired_date = ' MAX(d.start_date) last_aired_date';
        }
    } else {
        $airings = ' Count(d.airing_id) airings';
        //$first_aired_date       = ' MIN(a.start) first_aired_date';
        $last_aired_date       = ' MAX(d.start_date) last_aired_date';
    }
    
    $sql_fetch_network_creatives = "SELECT ".CACHE_SQL_QUERY." 
                    c.creative_id, 
                    c.spanish,
                    b.brand_name, 
                    c.brand_id, 
                    d.network_code,
                    n.network_id,
                    creative_name, 
                    class, 
                    type, 
                    c.length, 
                    c.response_tfn, 
                    c.response_url, 
                    c.response_sms, 
                    c.response_mar, 
                    thumbnail, 
                    Count(d.airing_id) total_airings,
                    ".SPEND_COLUMN.",
                    $airings, 
                    Count(IF(d.breaktype = 'N', 1, NULL)) AS nat,
                    Count(IF(d.breaktype = 'L', 1, NULL)) AS loc,
                    c.last_aired,
                    c.first_detection AS first_aired_date,
                    n.dpi,
                    count(DISTINCT d.program) as program_count,
                    $last_aired_date 
         FROM       ".AIRINGS_TABLE." d
         INNER JOIN network n
                    ON d.network_code = n.network_code /*AND n.status = 1*/ AND n.network_id not in (".get_inactive_networks().")
         INNER JOIN creative c 
         ON         d.creative_id = c.creative_id
         AND        $responseType
         AND        spanish IN ($spanish)
         AND        d.start_date >= '$start_date'
         AND        d.start_date <= '$end_date'" . NULL_ALL_CONDITION . "
         $day_condition
         $hour_condition
         $daypart_condition
         INNER JOIN brand b
         ON         c.brand_id = b.brand_id $brand_classification
         ".$program_params['join_condition']. $program_params['table_join_on']."
         WHERE        $tab_condition
         AND        d.network_id =  $network_id
         $new_filter_opt
         ".$program_params['where_program']."
         GROUP BY   c.creative_id
         ORDER BY   $order_by";
    return $sql_fetch_network_creatives;
}

//get all creatives for all networks for given date range.
function __query_get_creative_id_all_networks($params)
{
    extract($params);

    $sql = "SELECT   sql_no_cache DISTINCT(creative_id)
            FROM     airings_master
            WHERE    start_date BETWEEN '$sd' AND '$ed'";

    return $sql;
}

function __query_get_airings_brands_networks($params)
{
    extract($params);
    $and_adv = '';
    if (empty($creative_ids_check)) {
        $creative_ids_check = '';
    }
    if(isset($subgird) && $subgird == 'adv_brand'){
        $and_adv = "AND adv.adv_id ='".$adv_id."'";
    }
    if ($advOrBrandName == ' c.creative_name ') {
        $advOrBrandName = ' concat(c.creative_name, " - ", c.length, "sec") ';
    }
    if (!empty($breaktype)) {
        if ($breaktype == "L") {
            $airings = ' Count(IF(d.breaktype = "L", 1, NULL)) airings';
            $projected_score = ' SUM(IF(d.breaktype = "L", d.'.RATE_COLUMN.', NULL)) projected_score';
            $breaktype_condition = ' breaktype = "L" ';
        } elseif ($breaktype == "N") {
            $airings = ' Count(IF(d.breaktype = "N", 1, NULL)) airings';
            $projected_score = ' SUM(IF(d.breaktype = "N", d.'.RATE_COLUMN.', NULL)) projected_score';
            $breaktype_condition = ' breaktype = "N" ';
        } else {
            $airings = ' Count(*) airings';
            $projected_score = ' SUM(d.'.RATE_COLUMN.') as projected_score';
            $breaktype_condition = ' 1=1 ';
        }
    } else {
        $airings = ' Count(*) airings';
        $projected_score = ' SUM(d.'.RATE_COLUMN.') as projected_score';
        $breaktype_condition = ' 1=1 ';
    }
    
    if($tab == 0) {
        $sql = 'SELECT  '.$advOrBrandId.' id, n.network_alias as network_code, d.network_code as _network_code,n.network_id, n.dpi,
         GROUP_CONCAT(DISTINCT('.$brandOrCreative.')) as creatives,
         '.$projected_score.',
         b.brand_id as _brand_id, 
         b.brand_name as _brand_name, 
         adv.adv_id as _adv_id, 
         adv.display_name as _advertiser_name, 
         adv.need_help,
         b.adv_id,
         ' . $advOrBrandName . ' name,
         ' . $airings . ',
         count(DISTINCT b.brand_id)      no_of_brands,
         count(distinct d.creative_id)   creative_count,
         1 AS                        category_count,
         c.creative_name,
         c.thumbnail,
         c.spanish
        FROM     creative c,
                 ' . AIRINGS_TABLE . ' d USE INDEX(idx_ranking3),
                 brand b,
                 advertiser adv,
                 network n
        WHERE    c.brand_id = d.brand_id
        AND      n.network_id = d.network_id
        AND      c.creative_id = d.creative_id
        AND      ' . $responseType . '
        AND      start_date BETWEEN  "' . $sd . '"  AND  "' . $ed . '"
        AND      c.brand_id = b.brand_id
        AND      b.adv_id = adv.adv_id
        AND n.network_id IN ('.$network_id.')
        AND ' . $breaktype_condition . ' 
        AND      c.spanish IN (' . $spanish . ') ' . $where_flag . $brand_classification . $categories . '  ' . $and_adv . $creative_ids_check . '
        GROUP BY ' . $advOrBrandId . ', d.network_id ';
    } else {
        $creative_col = '  d.creatives, ';
        $subquery_creative_col = 'GROUP_CONCAT(DISTINCT ('.$brandOrCreative.')) AS creatives ,';
        $group_by = 'brand_id';
        if(!empty($creative_ids_check)) {
            $creative_col = ' GROUP_CONCAT(DISTINCT (c.creative_id)) AS creatives ,';
            $subquery_creative_col = '';
            $group_by = 'creative_id';
        }
            $sql = 'SELECT  '.$advOrBrandId.' id, n.network_alias as network_code, n.network_code as _network_code,n.network_id, n.dpi,
        '.$creative_col.'
            d.projected_score,
            b.brand_id as _brand_id, 
            b.brand_name as _brand_name, 
            "" as creative_name,
            adv.adv_id as _adv_id, 
            adv.display_name as _advertiser_name, 
            adv.need_help,
            b.adv_id,
            ' . $advOrBrandName . ' name,
            d.airings,
            count(DISTINCT b.brand_id)      no_of_brands,
            d.creative_count,
            1 AS                        category_count,
            c.thumbnail,
            c.spanish
            from network n 
            INNER join
        (  
                SELECT  
                network_id,creative_id,
                SUM(rate) AS projected_score,
            '.$subquery_creative_col.'
                brand_id,
                rate	,
                COUNT(*) airings,
                COUNT(DISTINCT creative_id) creative_count
                from 
                airings c WHERE        start_date BETWEEN  "' . $sd . '"  AND  "' . $ed . '"   AND network_id IN ('.$network_id.') AND ' . $breaktype_condition . $airings_length .'
                group by 
                '.$group_by.',network_id 
        ) d ON n.network_id = d.network_id 
        INNER JOIN creative c on c.creative_id = d.creative_id
        INNER JOIN brand b on b.brand_id = d.brand_id 
        INNER JOIN advertiser adv on adv.adv_id = b.adv_id
        WHERE  
            n.network_id IN ('.$network_id.')
        AND      ' . $responseType . '
        AND      c.spanish IN (' . $spanish . ') ' . $where_flag . $brand_classification . $categories . '  ' . $and_adv . $creative_ids_check . '
        GROUP BY ' . $advOrBrandId . ', d.network_id ';
    }
    return $sql;
}


function __query_get_airings_for_creative_brands_networks($params)
{
    extract($params);

    if (check_base64_encoded($creative_ids)) {
        $creative_ids = base64_decode($creative_ids);
    } else {
        $creative_ids = $creative_ids;
    }
    $params['creative_ids_check'] = ' AND c.creative_id IN (' . $creative_ids . ') ';
    $params['advOrBrandName'] = ' c.creative_name ';
    $params['breaktype'] = $breaktype;
    $sql = __query_get_airings_brands_networks($params) . ', c.creative_id ';
   
    return $sql;
}

function __query_get_projected_spend_for_networks($params)
{
    extract($params);

    $sql = 'SELECT   ' . CACHE_SQL_QUERY . '
         SUM(d.'.RATE_COLUMN.') as rate, 1 AS category_count
        FROM     creative c,
                 ' . AIRINGS_TABLE . ' d ,
                 brand b,
                 advertiser adv,
                 network n
        WHERE    c.brand_id = d.brand_id
        AND      n.network_id  = d.network_id 
        /*AND      n.status = 1*/ AND n.network_id not in ('.get_inactive_networks().')
        AND      c.creative_id = d.creative_id
        AND      ' . $responseType . '
        AND      start_date BETWEEN  "' . $sd . '"  AND  "' . $ed . '"
        AND      c.brand_id = b.brand_id
        AND      b.adv_id = adv.adv_id
        AND      n.network_alias IN ("' . $network . '")
        AND      c.spanish IN (' . $spanish . ') ' . $where_flag . $brand_classification . $categories . ' group by b.brand_id';
    return $sql;
}

function __query_get_brands_for_creative_advertiser_networks($params)
{
    extract($params);

    if (check_base64_encoded($brands_ids)) {
        $brands_ids = base64_decode($brands_ids);
    } else {
        $brands_ids = $brands_ids;
    }

    $params['creative_ids_check'] = ' AND b.brand_id IN (' . $brands_ids . ') ';
    $params['advOrBrandName'] = ' b.brand_name ';
    $sql = __query_get_airings_brands_networks($params) . ', c.brand_id ';

    return $sql;
}

function __query_get_category_detail($params)
{
    extract($params);

    $sql = "SELECT category, sub_category FROM categories Where sub_category_id ='" . $category_id . "'";

    return $sql;
}

function __query_get_all_networks($params)
{
    $sql = "SELECT `network_id`, `network_code`, `network_name`, `network_alias`, `live_date`, `dpi` , diginet FROM `network` WHERE status = '1' and hidden = 0 ORDER BY network_alias ASC";

    return $sql;
}

function __query_get_video_id($params)
{
    $refine_where_clause = '';
    if(isset($params['refine_where'])) {
        $refine_where_clause = $params['refine_where'];
    }
    extract($params);
    // $sql = "SELECT a.airing_id, a.start_date, n.network_alias, a.start,a.creative_id, a.brand_id, b.brand_name, c.creative_name, c.length  FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_code = a.network_code WHERE a.`creative_id` = '" . $creative_id . "' " . $network_filter . " AND a.start_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 9 DAY) and CURDATE() ORDER BY a.`airing_id` DESC limit 1";

    $sql = "SELECT a.airing_id, a.start_date,a.broadcast_start, n.network_alias, a.start,a.creative_id, a.brand_id, b.brand_name, c.creative_name, c.length
            FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_id = a.network_id
            WHERE  a.`creative_id` = '" . $creative_id . "' ". $refine_where_clause. "  $network_filter   AND a.start_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 9 DAY) and CURDATE() AND c.class != 'BRAND' ORDER BY a.`airing_id` DESC limit 1";
    return $sql;
}

function __query_get_video_id_for_other_network($params) {
    extract($params);
    // $sql = "SELECT a.airing_id, a.start_date, n.network_alias, a.start,a.creative_id, a.brand_id, b.brand_name, c.creative_name, c.length  FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_code = a.network_code WHERE a.`creative_id` = '" . $creative_id . "' " . $network_filter . " AND a.start_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 9 DAY) and CURDATE() ORDER BY a.`airing_id` DESC limit 1";
    $sql = "SELECT a.airing_id, a.start_date,a.broadcast_start, n.network_alias, a.start,a.creative_id, a.brand_id, b.brand_name, c.creative_name, c.length
            FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_id = a.network_id
            WHERE a.`creative_id` = '" . $creative_id ."' " . $network_filter . " AND a.start_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 9 DAY) and CURDATE() AND c.class != 'BRAND' ORDER BY a.`airing_id` DESC limit 1";
    return $sql;
}

function __query_get_user_info_by_id($params)
{
    extract($params);
    $sql = "SELECT user_id, username, role, first_name,last_name, company_id, zoho_contact_id,assistant_admin, concat(first_name, ' ',last_name) as full_name FROM user WHERE user_id = '" . $user_id . "'";
    return $sql;
}

function __query_check_admin_user($params)
{
    extract($params);
    $sql = "SELECT admin_id FROM admin_user WHERE user_id='" . $user_id . "'";
    return $sql;
}

function __query_get_video_id_by_creative($params)
{
    $refine_where_clause = '';
    if(isset($params['refine_where'])) {
        $refine_where_clause = $params['refine_where'];
    }
    extract($params);
    #$sql = "SELECT a.airing_id, a.start_date, n.network_alias, a.start,a.creative_id , a.brand_id, b.brand_name, c.creative_name, c.length FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_code = a.network_code WHERE a.`airing_id` = '" . $creative_id . "' ";
    $sql = "SELECT a.airing_id, a.start_date,a.broadcast_start, n.network_alias, a.start,a.creative_id , a.brand_id, b.brand_name, c.creative_name, c.length
            FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_id = a.network_id
            WHERE a.`airing_id` = '" . $creative_id . "'  ". $refine_where_clause. " $where AND c.class != 'BRAND';";
    return $sql;
}

function __query_get_info_org_airing($params)
{
    extract($params);
   # $sql = "SELECT a.airing_id, a.start_date, n.network_alias, a.start,a.creative_id , a.brand_id, b.brand_name, c.creative_name, c.length FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_code = a.network_code WHERE airing_id = '" . $airing_id . "'";
   $sql = "SELECT a.airing_id, a.start_date,a.broadcast_start, n.network_alias, a.start,a.creative_id , a.brand_id, b.brand_name, c.creative_name, c.length
            FROM `airings` as a INNER JOIN brand b on b.brand_id = a.brand_id INNER JOIN creative c on c.creative_id = a.creative_id INNER JOIN network n on n.network_id = a.network_id
            WHERE airing_id = '" . $airing_id . "' AND c.class != 'BRAND';";
    return $sql;
}

function __query_insert_user_rate_feedback($params) {
    extract($params);
    $noteCol = '';
    if(isset($note)) {
        $noteCol = ', note="' . addslashes($note) .'";';
    }
    $sql = "INSERT INTO user_rate_feedback SET user_id='" . $user_id . "', network_id='" . $network_id . "', company_id='" . $company_id . "', rate='" . $rate."' ". $noteCol;
    return $sql;
}

function __qurey_insert_user_filters($params)
{
    extract($params);
    $where_criteria_ids = '';
    if($list_id != '') {
        $where_criteria_ids = ', criteria_id = "'.$list_ids.'", list_type ="'.$list_type.'", list_id ="'.$list_id.'"';
    }
    $sql = "INSERT INTO user_filters SET user_id='" . $user_id . "', page='" . $page . "', primary_tab='" . $primary_tab . "', secondary_tab='" . $secondary_tab . "', query_string='" . $filter_data . "',criteria ='" . $criteria . "', name='" . $filter_name . "' , created_date='" . $created_date . "', programs = '".$programs."', frequency = '" . $frequency . "'".$where_criteria_ids. ", schedule_email = $schedule_email ;";
    return $sql;
}

function __qurey_insert_user_list($params) {
    extract($params);
    $sql = "INSERT INTO  users_list SET user_id='" . $user_id . "',primary_tab='" . $primary_tab . "',criteria_id ='" . $criteria . "', name='" . $list_name . "' , created_date='" . $created_date . "'";
    return $sql;
}

function __qurey_update_user_filters($params)
{
    extract($params);
    $where_criteria_ids = '';
    if($list_id != '') {
        $where_criteria_ids = ', list_id ="'.$list_id.'"';
    }
    $sql = "UPDATE user_filters SET user_id='" . $user_id . "', page='" . $page . "', primary_tab='" . $primary_tab . "', secondary_tab='" . $secondary_tab . "', query_string='" . $filter_data . "',criteria ='" . $criteria . "', name='" . $filter_name . "' , created_date='" . $created_date . "', schedule_email = $schedule_email , programs = '".$programs."' ".$where_criteria_ids." WHERE id=".$filter_id;
    return $sql;
}

function __qurey_update_user_list($params) {
    $db = getConnection();
    extract($params);
    if(isset($current_edit_list) && $current_edit_list != '') {
        $sql = "UPDATE users_list set status = 'deleted' WHERE id = ".$current_edit_list;
        $stmt = $db->prepare($sql);
        $stmt->execute();
    } 
    $sql = "UPDATE users_list SET user_id='" . $user_id . "', primary_tab='" . $primary_tab . "',  criteria_id ='" . $criteria . "', name='" . $list_name . "' WHERE id=".$list_id;
    return $sql;
}

function __query_get_user_filter_list($params)
{
    extract($params);
    $_order_by = '';
    if (isset($order_by)) {
        $_order_by = $order_by;
    }

    $sql = "SELECT uf.*,concat(exu.first_name, ' ', exu.last_name) as full_name, u.parent_id as parent_id,
            if(instr(uf.criteria, 'Last Week')||instr(uf.criteria, 'Current Week')||instr(uf.criteria, 'Last Month')||instr(uf.criteria, 'Current Month')||instr(uf.criteria, 'Last Quarter')||instr(uf.criteria, 'Current Quarter')||instr(uf.criteria, 'Current Year - YTD')||instr(uf.criteria, 'Lifetime'), 1, 0 ) email_schedulable,
            if(instr(uf.criteria, 'Last Week'), 'weekly', if(instr(uf.criteria, 'Current Week'), 'daily', if(instr(uf.criteria, 'Last Month'), 'monthly', if(instr(uf.criteria, 'Last Quarter'), 'quarterly', '')))) email_schedulable_direct
            FROM user_filters uf  LEFT JOIN
            user_filters u ON uf.id = u.parent_id  LEFT JOIN user exu  ON uf.shared_by = exu.user_id   WHERE uf.user_id = ".$user_id." AND uf.status='active' AND uf.page='" . $tab . "' " . $_order_by;
    //$sql = "SELECT * FROM user_filters WHERE user_id = '".$user_id."' AND status='active' AND page='".$tab."' AND primary_tab='".$primary_tab."' ".$_order_by;
    return $sql;
}

function __qurey_delete_user_filters($params)
{
    extract($params);
    $sql = "UPDATE user_filters SET status = 'deleted' WHERE id IN ( " . $id . ")";
    return $sql;
}

function __query_fetch_selected_filter_list($params)
{
    extract($params);
    $sql = "SELECT * FROM user_filters WHERE user_id='" . $user_id . "' AND id='" . $selected_filter_id . "'";
    return $sql;
}

function __check_duplicate_list($params) {
    extract($params);
    $sql = "SELECT * FROM users_list  WHERE (user_id='" . $user_id . "' AND primary_tab='" . $primary_tab . "' AND name='" . $list_name . "' AND status = 'active') OR (user_id='" . $user_id . "' AND primary_tab='" . $primary_tab . "'  AND criteria_id='" . $criteria . "' AND status = 'active')";
    return $sql;
}

function __check_duplicate_list_name($params) {
    extract($params);
    $sql = "SELECT * FROM users_list WHERE user_id='" . $user_id . "' AND primary_tab='" . $primary_tab . "' AND name='" . $list_name . "' AND status = 'active'";
    return $sql;
}

function __check_duplicate_criteria_list($params) {
    extract($params);
    $sql = "SELECT * FROM users_list WHERE user_id='" . $user_id . "' AND primary_tab='" . $primary_tab . "'  AND criteria_id='" . $criteria . "'  AND status = 'active'";
    return $sql;
}

function __check_duplicate_name_filters($params)
{
    extract($params);
    $sql = "SELECT * FROM user_filters WHERE user_id='" . $user_id . "' AND page='" . $page . "' AND primary_tab='" . $primary_tab . "' AND secondary_tab='" . $secondary_tab . "'  AND name='" . $filter_name . "' AND status = 'active'";
    return $sql;
}

function __check_duplicate_filters($params) {
    extract($params);
    $sql = "SELECT * FROM user_filters WHERE (user_id='" . $user_id . "' AND page='" . $page . "' AND primary_tab='" . $primary_tab . "' AND name='" . $filter_name . "'  AND secondary_tab='" . $secondary_tab . "' AND status = 'active') OR (user_id='" . $user_id . "' AND page='" . $page . "' AND primary_tab='" . $primary_tab . "' AND secondary_tab='" . $secondary_tab . "' AND criteria='" . $criteria . "' AND status = 'active')";
    return $sql;
}

function __check_duplicate_criteria_filters($params)
{
    extract($params);
    $sql = "SELECT * FROM user_filters WHERE user_id='" . $user_id . "' AND page='" . $page . "' AND primary_tab='" . $primary_tab . "' AND secondary_tab='" . $secondary_tab . "' AND criteria='" . $criteria . "'  AND status = 'active'";
    return $sql;
}

function __get_brand_id_with_category($params = array())
{
    $sql = "SELECT brand_id, CONCAT(ifnull(`main_sub_category_id`,''),',',ifnull(`alt_sub_category_id`,'')) as cat_ids FROM `brand` GROUP by brand_id";
    return $sql;
}

function __query_summary_advpage_brands($params)
{
    extract($params);
    $set_order_by = '';
    if (isset($order_by)) {
        $set_order_by = ' GROUP BY b.brand_id ' . $order_by;
    }

    $sql = 'SELECT   ' . CACHE_SQL_QUERY . ' b.brand_id ID,
                     b.adv_id,
                     b.brand_name,
                     b.total_weeks,
                     count(distinct d.creative_id)             creative_count,
                     1 AS  category_count,
                     sum(airings) airings,
                     sum(d.'.RATE_COLUMN.')                     spend_index, 
                     round(sum(ASD) / sum(airings)) as asd,
                     round(100*sum(local_airings)/sum(airings))                       AS local,
                     round(100*sum(national_airings)/sum(airings))                    AS national,
                     adv.company_name,
                     count(DISTINCT b.brand_id)                no_of_brands,
                     count(DISTINCT network_code)   AS networks,
                     adv.display_name                               advertiser_name,
                     0 as current_week,
                     c.first_detection AS first_aired,
                     c.last_aired      AS last_aired,
                     sum(case when (c.spanish = 1) THEN 1 ELSE 0 END)  as spanish_creative_count,
                     sum(case when (c.spanish = 0) THEN 1 ELSE 0 END)  as english_creative_count,
                     count(c.creative_id) as total_creative_count,
                     c.type AS price,
                    ' . $cols . '
            FROM     ' . SUMMARY_AIRINGS . ' d , advertiser adv, brand b, creative c
            WHERE    d.start_date BETWEEN "' . $sd . ' 00:00:00"
            AND       "' . $ed . ' 23:59:59"
            AND d.brand_id = b.brand_id
            AND d.creative_id = c.creative_id
            AND b.adv_id = adv.adv_id
            ' . $brand_classification . '
            AND spanish IN (' . $spanish . ')
            AND b.adv_id = ' . $adv_id . $set_order_by;
    return $sql;
}

function __query_advertiser_lifetime_detail($params)
{
    extract($params);
    //lifetime details updated as per airings_master
    $sql = 'SELECT ' . CACHE_SQL_QUERY . ' b.adv_id adv_id,
            b.brand_id,
            sum(d.'.RATE_COLUMN.') rate
        FROM     creative c,
                 airings_master d ,
                 brand b, advertiser a
        WHERE    c.brand_id = d.brand_id
        AND      c.creative_id = d.creative_id
        AND      start_date BETWEEN  "' . LIFETIME_START_DATE . '"  AND "' . customDate('Y-m-d') . '"
        AND (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1)
        AND      c.brand_id = b.brand_id AND c.length ' . $length_condition . '
        AND c.class != "BRAND" and a.adv_id=b.adv_id and a.deleted=0
        GROUP BY b.adv_id order by rate DESC';
    return $sql;
}

function __query_advertiser_lifetime_airing_detail($params)
{
    extract($params);

    $sql = 'SELECT ' . CACHE_SQL_QUERY . ' b.adv_id adv_id,
        count(d.airing_id) as airings,
        count(distinct(b.brand_id)) as brands,
        count(distinct(c.creative_id)) as creative,
        ROUND(sum(' . RATE_COLUMN . '), 0) as rate
    FROM     creative c,
             airings d ,
             brand b
    WHERE    c.brand_id = d.brand_id
    AND      c.creative_id = d.creative_id AND d.network_id NOT IN (' . get_inactive_networks() . ')
    AND      start_date BETWEEN  "' . $sd . '"  AND "' . $ed . '"
    AND      (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1)
    AND      b.adv_id = ' . $advertiser_id . '
    AND      c.brand_id = b.brand_id  AND c.length ' . $length_condition . '
    AND      c.class != "BRAND"
    GROUP BY b.adv_id';

    return $sql;
}

function __query_advertiser_detail_from_advertiser_pages($params)
{
    extract($params);

    $sql = 'SELECT '.CACHE_SQL_QUERY.' adv_id, rank, /*ROUND(rate , 0) as rate,*/ spend_index
        FROM     advertiser_pages
        WHERE    adv_id = '.$advertiser_id .' AND type = "'.$type.'"';
    return $sql;
}

function __query_get_advertisers()
{
    return 'SELECT adv_id, company_name, display_name FROM advertiser';

}

function __queries_display_advpage_creatives($params)
{
    extract($params);

    //$new_filter_opt = newFilter($new_filter_opt,$sd,$ed);

    $sql = "SELECT   " . CACHE_SQL_QUERY . " c.creative_id,
            b.brand_name,
            b.brand_id,
            adv.adv_id,
            c.creative_name,
            c.price,
            c.payments,
            c.class,
            b.brand_id parent_id,
            c.type,
            c.length,
            c.thumbnail,
            c.is_active,
            c.response_tfn,
            c.response_url,
            c.response_sms,
            c.response_mar,
            adv.display_name,
            SUM(airings)                                       airings,
            ROUND(SUM(d.".RATE_COLUMN.") , 0) as spend,
            Round(100*Sum(local_airings)/Sum(airings), 0)         AS local,
            Round(100*Sum(national_airings)/Sum(airings), 0)      AS national,
            1 AS                    category_count,
            c.first_detection AS first_aired_date,
            c.last_aired AS last_aired_date,
            sum(case when (c.spanish = 1) THEN 1 ELSE 0 END)  as spanish_creative_count,
            sum(case when (c.spanish = 0) THEN 1 ELSE 0 END)  as english_creative_count,
            count(c.creative_id) as total_creative_count
    FROM     " . SUMMARY_AIRINGS . " d,
             creative c,
             brand b,
             advertiser adv
    WHERE    d.creative_id = c.creative_id
    AND      c.spanish IN ($spanish)
    AND      $responseType
    AND      d.brand_id = b.brand_id
    AND      c.creative_id = $creative_id
    AND      b.adv_id = adv.adv_id
    AND      d.start_date >= '$sd 00:00:00'
    AND      d.start_date <= '$ed 23:59:59' $brand_classification
    GROUP BY c.creative_id
    ORDER BY $order_by";

    return $sql;
}

function __query_get_adv_for_global_srch($params)
{

    extract($params);
    $srch_txt_re = str_replace('[', '\\\\[', str_replace(']', '\\\\]', str_replace('(', '\\\\(', str_replace(')', '\\\\)', str_replace('?', '\\\\?', str_replace('|', '\\\\|', $srch_txt)) ))));
    if ( strlen($srch_txt) > 2 && substr($srch_txt, 0, 2) == '\"' && substr($srch_txt, strlen($srch_txt)-2) == '\"' ) {
        $srch_txt_re = substr($srch_txt_re, 2, strlen($srch_txt_re)-4);
        $sql = 'SELECT adv.*, if(adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]", adv.display_name, "' . ucfirst($srch_txt_re) . '") display_name, b.brand_name,c.creative_name, sum(case when (c.length > 300) THEN 1 ELSE 0 END)  as long_form, sum(case when (c.length <= 300) THEN 1 ELSE 0 END)  as short_form,
        adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight1,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight3,
        c.master_tfn regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight5,
        c.master_vanity regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight6,
        c.master_url regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight7,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight8
        FROM    advertiser adv, brand b, creative c, advertiser_pages, airings_master am
        WHERE   adv.adv_id = b.adv_id AND advertiser_pages.adv_id = adv.adv_id AND am.creative_id = c.creative_id
        AND     c.brand_id = b.brand_id
        AND     c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND     (adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" OR adv.alt_adv_names regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]")
        AND c.class != "BRAND"
        GROUP by adv.adv_id order by weight1 desc, weight3 desc, weight5 desc, weight6 desc, weight7 desc, weight8 desc';
    } else {
        $sql = 'SELECT adv.*,b.brand_name,c.creative_name, sum(case when (c.length > 300) THEN 1 ELSE 0 END)  as long_form, sum(case when (c.length <= 300) THEN 1 ELSE 0 END)  as short_form,
        adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight01, adv.display_name LIKE "%' . $srch_txt . '%" as weight1, MATCH(adv.alt_adv_names) against ("' . $srch_txt . '" ) as weight2,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight03, b.brand_name like "%' . $srch_txt . '%" as weight3, MATCH(b.alt_brand_names) against ("' . $srch_txt . '" ) as weight4,
        c.master_tfn regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight5,
        c.master_vanity regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight6,
        c.master_url regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight7,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight08, c.creative_name like "%' . $srch_txt . '%" as weight8, MATCH(c.keywords) against ("' . $srch_txt . '" ) as weight9
        FROM    advertiser adv, brand b, creative c, advertiser_pages, airings_master am
        WHERE   adv.adv_id = b.adv_id AND advertiser_pages.adv_id = adv.adv_id AND am.creative_id = c.creative_id
        AND     c.brand_id = b.brand_id
        AND     c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND     ((adv.display_name like "%' . $srch_txt . '%" or adv.alt_adv_names like "%' . $srch_txt . '%" or MATCH(adv.alt_adv_names) against ("' . $srch_txt . '")) or
                (b.brand_name like "%' . $srch_txt . '%" or b.alt_brand_names like "%' . $srch_txt . '%" or MATCH(b.alt_brand_names) against ("' . $srch_txt . '")) or
                c.master_tfn like "' . $srch_txt . '" or c.master_vanity like "' . $srch_txt . '" or c.master_url like "' . $srch_txt . '" or
                (c.creative_name like "%' . $srch_txt . '%" or c.keywords like "%' . $srch_txt . '%" or MATCH(c.keywords) against ("' . $srch_txt . '")))
        AND c.class != "BRAND"
        GROUP by adv.adv_id order by weight01 desc, weight1 desc, weight2 desc, weight03 desc, weight3 desc, weight4 desc, weight5 desc, weight6 desc, weight7 desc, weight08 desc, weight8 desc, weight9 desc';
    }
    return $sql;
}

function __query_get_brand_for_global_srch($params)
{
    extract($params);
    $srch_txt_re = str_replace('[', '\\\\[', str_replace(']', '\\\\]', str_replace('(', '\\\\(', str_replace(')', '\\\\)', str_replace('?', '\\\\?', str_replace('|', '\\\\|', $srch_txt)) ))));
    if ( strlen($srch_txt) > 2 && substr($srch_txt, 0, 2) == '\"' && substr($srch_txt, strlen($srch_txt)-2) == '\"' ) {
        $srch_txt_re = substr($srch_txt_re, 2, strlen($srch_txt_re)-4);
        $sql = 'SELECT a.display_name as adv_name, a.adv_id,a.need_help, b.brand_id, if(b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]", b.brand_name, "' . ucfirst($srch_txt_re) . '") brand_name, sum(case when (c.length > 300) THEN 1 ELSE 0 END)  as long_form, sum(case when (c.length <= 300) THEN 1 ELSE 0 END)  as short_form,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight1,
        c.master_tfn regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight3,
        c.master_vanity regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight4,
        c.master_url regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight5,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight6,
        a.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight8
        FROM     advertiser a, brand b, creative c, advertiser_pages, airings_master am
        WHERE    a.adv_id = b.adv_id AND advertiser_pages.adv_id = a.adv_id AND am.creative_id = c.creative_id
        AND      c.brand_id = b.brand_id
        AND      c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND     (b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" OR b.alt_brand_names regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]")
        AND c.class != "BRAND"
        GROUP BY b.brand_id order by weight1 desc, weight3 desc, weight4 desc, weight5 desc, weight6 desc, weight8 desc';
    } else {
        $sql = 'SELECT a.display_name as adv_name, a.adv_id,a.need_help, b.brand_id, b.brand_name, sum(case when (c.length > 300) THEN 1 ELSE 0 END)  as long_form, sum(case when (c.length <= 300) THEN 1 ELSE 0 END)  as short_form,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight01, b.brand_name like "%' . $srch_txt . '%" as weight1,  MATCH(b.alt_brand_names) against ("' . $srch_txt . '") as weight2,
        c.master_tfn regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight3,
        c.master_vanity regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight4,
        c.master_url regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight5,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight06, c.creative_name like "%' . $srch_txt . '%" as weight6, MATCH(c.keywords) against ("' . $srch_txt . '") as weight7,
        a.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight08, a.display_name LIKE "%' . $srch_txt . '%" as weight8, MATCH(a.alt_adv_names) against ("' . $srch_txt . '") as weight9
        FROM     advertiser a, brand b, creative c, advertiser_pages, airings_master am
        WHERE    a.adv_id = b.adv_id AND advertiser_pages.adv_id = a.adv_id AND am.creative_id = c.creative_id
        AND      c.brand_id = b.brand_id
        AND      c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND     ((b.brand_name like "%' . $srch_txt . '%" or b.alt_brand_names like "%' . $srch_txt . '%" or MATCH(b.alt_brand_names) against ("' . $srch_txt . '")) or
                c.master_tfn like "' . $srch_txt . '" or c.master_vanity like "' . $srch_txt . '" or c.master_url like "' . $srch_txt . '" or
                (c.creative_name like "%' . $srch_txt . '%" or c.keywords like "%' . $srch_txt . '%" or MATCH(c.keywords) against ("' . $srch_txt . '")) or
                (a.display_name like "%' . $srch_txt . '%" or a.alt_adv_names like "%' . $srch_txt . '%" or MATCH(a.alt_adv_names) against ("' . $srch_txt . '")))
        AND c.class != "BRAND"
        GROUP BY b.brand_id order by weight01 desc, weight1 desc, weight2 desc, weight3 desc, weight4 desc, weight5 desc, weight06 desc, weight6 desc, weight7 desc, weight08 desc, weight8 desc, weight9 desc';
    }
    return $sql;
}

function __query_get_creative_for_global_srch($params)
{
    extract($params);
    $srch_txt_re = str_replace('[', '\\\\[', str_replace(']', '\\\\]', str_replace('(', '\\\\(', str_replace(')', '\\\\)', str_replace('?', '\\\\?', str_replace('|', '\\\\|', $srch_txt)) ))));
    if ( strlen($srch_txt) > 2 && substr($srch_txt, 0, 2) == '\"' && substr($srch_txt, strlen($srch_txt)-2) == '\"' ) {
        $srch_txt_re = substr($srch_txt_re, 2, strlen($srch_txt_re)-4);
        $sql = 'SELECT a.display_name as adv_name, a.adv_id,a.need_help, b.brand_id, b.brand_name, c.creative_id, if(c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]", c.creative_name, "' . ucfirst($srch_txt_re) . '") creative_name, sum(case when (c.length > 300) THEN 1 ELSE 0 END)  as long_form, sum(case when (c.length <= 300) THEN 1 ELSE 0 END)  as short_form,c.length as duration,
        c.master_tfn regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight1,
        c.master_vanity regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight2,
        c.master_url regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight3,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight4,
        a.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight6,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight8
        FROM advertiser a, brand b, creative c, advertiser_pages, airings_master am
        WHERE a.adv_id = b.adv_id AND advertiser_pages.adv_id = a.adv_id AND am.creative_id = c.creative_id
        AND b.brand_id = c.brand_id
        AND c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND (c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" OR c.keywords regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]")
        AND c.class != "BRAND"
        GROUP BY c.creative_id order by weight1 desc, weight2 desc, weight3 desc, weight4 desc, weight6 desc, weight8 desc';
    } else {
        $sql = 'SELECT a.display_name as adv_name, a.adv_id,a.need_help, b.brand_id, b.brand_name, c.creative_id, c.creative_name, sum(case when (c.length > 300) THEN 1 ELSE 0 END)  as long_form, sum(case when (c.length <= 300) THEN 1 ELSE 0 END)  as short_form,c.length as duration,
        c.master_tfn regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight1,
        c.master_vanity regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight2,
        c.master_url regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight3,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight04, c.creative_name like "%' . $srch_txt . '%" as weight4,  MATCH(c.keywords) against ("' . $srch_txt . '") as weight5,
        a.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight06, a.display_name LIKE "%' . $srch_txt . '%" as weight6, MATCH(a.alt_adv_names) against ("' . $srch_txt . '") as weight7,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight08, b.brand_name like "%' . $srch_txt . '%" as weight8,  MATCH(b.alt_brand_names) against ("' . $srch_txt . '") as weight9
        FROM advertiser a, brand b, creative c, advertiser_pages, airings_master am
        WHERE a.adv_id = b.adv_id AND advertiser_pages.adv_id = a.adv_id AND am.creative_id = c.creative_id
        AND b.brand_id = c.brand_id
        AND c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND (c.master_tfn like "' . $srch_txt . '" or c.master_vanity like "' . $srch_txt . '" or c.master_url like "' . $srch_txt . '" or
            (c.creative_name like "%' . $srch_txt . '%" or c.keywords like "%' . $srch_txt . '%" or MATCH(c.keywords) against ("' . $srch_txt . '")) or
            (a.display_name like "%' . $srch_txt . '%" or a.alt_adv_names like "%' . $srch_txt . '%" or MATCH(a.alt_adv_names) against ("' . $srch_txt . '")) or
            (b.brand_name like "%' . $srch_txt . '%" or b.alt_brand_names like "%' . $srch_txt . '%" or MATCH(b.alt_brand_names) against ("' . $srch_txt . '")))
        AND c.class != "BRAND"
        GROUP BY c.creative_id order by weight1 desc, weight2 desc, weight3 desc, weight04 desc, weight4 desc, weight5 desc, weight06 desc, weight6 desc, weight7 desc, weight08 desc, weight8 desc, weight9 desc';
    }
    return $sql;
}

function __query_adv_lifetime_global_search_detail($params)
{
    extract($params);
    $srch_txt_re = str_replace('[', '\\\\[', str_replace(']', '\\\\]', str_replace('(', '\\\\(', str_replace(')', '\\\\)', str_replace('?', '\\\\?', str_replace('|', '\\\\|', $srch_txt)) ))));
    if ( strlen($srch_txt) > 2 && substr($srch_txt, 0, 2) == '\"' && substr($srch_txt, strlen($srch_txt)-2) == '\"' ) {
        $srch_txt_re = substr($srch_txt_re, 2, strlen($srch_txt_re)-4);
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' b.adv_id adv_id,
        if(adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]", adv.display_name, "' . ucfirst($srch_txt_re) . '") as adv_name,
        adv.need_help,
        count(d.airing_id) as airings,
        count(distinct(b.brand_id)) as brands,
        count(distinct(c.creative_id)) as creative,
        Sum(d.'.RATE_COLUMN.') spend_index,
        adv.display_name,
        ' . $cols . '
    FROM     creative c,
             advertiser adv,
             airings d,
             brand b
    WHERE    c.brand_id = d.brand_id
    AND      adv.adv_id = b.adv_id
    AND      c.creative_id = d.creative_id
    AND      c.last_aired >= "' . LIFETIME_START_DATE . '"
    AND      (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1)
    AND      (adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" OR adv.alt_adv_names regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]")
    AND      c.brand_id = b.brand_id ' . $brand_classification . '
    GROUP BY b.adv_id order by adv.display_name desc';
    } else {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' b.adv_id adv_id,
        adv.display_name as adv_name,
        adv.need_help,
        count(d.airing_id) as airings,
        count(distinct(b.brand_id)) as brands,
        count(distinct(c.creative_id)) as creative,
        Sum(d.'.RATE_COLUMN.') spend_index,
        adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight01, adv.display_name LIKE "%' . $srch_txt . '%" as weight1, MATCH(adv.alt_adv_names) against ("' . $srch_txt . '") as weight2,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight03, b.brand_name like "%' . $srch_txt . '%" as weight3, MATCH(b.alt_brand_names) against ("' . $srch_txt . '") as weight4,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight05, c.creative_name like "%' . $srch_txt . '%" as weight5, MATCH(c.keywords) against ("' . $srch_txt . '") as weight6,
        ' . $cols . '
    FROM     creative c,
             advertiser adv,
             airings d,
             brand b
    WHERE    c.brand_id = d.brand_id
    AND      adv.adv_id = b.adv_id
    AND      c.creative_id = d.creative_id
    AND      c.last_aired >= "' . LIFETIME_START_DATE . '"
    AND      (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1)
    AND      ((adv.display_name like "%' . $srch_txt . '%" or adv.alt_adv_names like "%' . $srch_txt . '%" or MATCH(adv.alt_adv_names) against ("' . $srch_txt . '")) or
             (b.brand_name like "%' . $srch_txt . '%" or b.alt_brand_names like "%' . $srch_txt . '%" or MATCH(b.alt_brand_names) against ("' . $srch_txt . '")) or
             (c.master_tfn like "bra" or c.master_vanity like "bra" or c.master_url like "bra" or
             c.creative_name like "%' . $srch_txt . '%" or c.keywords like "%' . $srch_txt . '%" or MATCH(c.keywords) against ("' . $srch_txt . '")))
    AND      c.brand_id = b.brand_id ' . $brand_classification . '
    GROUP BY b.adv_id order by weight01 desc, weight1 desc, weight2 desc, weight03 desc, weight3 desc, weight4 desc, weight05 desc, weight5 desc, weight6 desc';
    }
    return $sql;
}

function __query_brand_lifetime_global_search_detail($params)
{
    extract($params);
    $srch_txt_re = str_replace('[', '\\\\[', str_replace(']', '\\\\]', str_replace('(', '\\\\(', str_replace(')', '\\\\)', str_replace('?', '\\\\?', str_replace('|', '\\\\|', $srch_txt)) ))));
    if($c > 5) {
        $where_duration = ' ba.length > '.LENGTH;
    } else {
        $where_duration = ' ba.length <= '.LENGTH;
    }
    if ( strlen($srch_txt) > 2 && substr($srch_txt, 0, 2) == '\"' && substr($srch_txt, strlen($srch_txt)-2) == '\"' ) {
        $srch_txt_re = substr($srch_txt_re, 2, strlen($srch_txt_re)-4);
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' b.brand_id brand_id,
        b.main_sub_category_id, b.alt_sub_category_id,
        if(b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]", b.brand_name, "' . ucfirst($srch_txt_re) . '") as brand_name,
        adv.display_name as adv_name,
        adv.need_help,
        Sum(ba.airings) as airings_count,
        Sum(ba.rate) as spend_index,
        adv.adv_id as adv_id,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight1,
        ' . $cols . '
    FROM    brand b,
            advertiser adv,
            creative c,
            brand_airings ba
    WHERE   adv.adv_id = b.adv_id
    AND     c.brand_id = b.brand_id
    AND     ba.creative_id = c.creative_id
    AND     ba.brand_id = b.brand_id
    AND    '.$where_duration.'
    AND     c.last_aired >= "' . LIFETIME_START_DATE . '"
    AND     (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1) AND (b.main_sub_category_id IS NOT NULL OR b.alt_sub_category_id IS NOT NULL)
    AND     (b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" OR b.alt_brand_names regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]")
    ' . $brand_classification . '
    GROUP BY b.brand_id order by weight1 desc';
    } else {
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' b.brand_id brand_id,
        b.main_sub_category_id, b.alt_sub_category_id,
        b.brand_name as brand_name,
        adv.display_name as adv_name,
        adv.need_help,
        Sum(ba.airings) as airings_count,
        Sum(ba.rate) as spend_index,
        adv.adv_id as adv_id,
        b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight01, b.brand_name like "%' . $srch_txt . '%" as weight1, MATCH(b.alt_brand_names) against ("' . $srch_txt . '") as weight2,
        c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight03, c.creative_name like "%' . $srch_txt . '%" as weight3, MATCH(c.keywords) against ("' . $srch_txt . '") as weight4,
        adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight05, adv.display_name like "%' . $srch_txt . '%" as weight5, MATCH(adv.alt_adv_names) against ("' . $srch_txt . '") as weight6,
        ' . $cols . '
    FROM    brand b,
            advertiser adv,
            creative c,
            brand_airings ba
    WHERE   adv.adv_id = b.adv_id
    AND     c.brand_id = b.brand_id
    AND     ba.creative_id = c.creative_id
    AND     ba.brand_id = b.brand_id
    AND    '.$where_duration.'
    AND     c.last_aired >= "' . LIFETIME_START_DATE . '"
    AND     (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1) AND (b.main_sub_category_id IS NOT NULL OR b.alt_sub_category_id IS NOT NULL)
    AND     ((b.brand_name like "%' . $srch_txt . '%" or b.alt_brand_names like "%' . $srch_txt . '%" or MATCH(b.alt_brand_names) against ("' . $srch_txt . '")) or
            (c.master_tfn like "' . $srch_txt . '" or c.master_vanity like "' . $srch_txt . '" or c.master_url like "' . $srch_txt . '" or
            c.creative_name like "%' . $srch_txt . '%" or c.keywords like "%' . $srch_txt . '%" or MATCH(c.keywords) against ("' . $srch_txt . '")) or
            (adv.display_name like "%' . $srch_txt . '%" or adv.alt_adv_names like "%' . $srch_txt . '%" or MATCH(adv.alt_adv_names) against ("' . $srch_txt . '")))
    ' . $brand_classification . '
    GROUP BY b.brand_id order by weight01 desc, weight1 desc, weight2 desc, weight03 desc, weight3 desc, weight4 desc, weight05 desc, weight5 desc, weight6 desc';
    }
    return $sql;
}

function __query_creative_lifetime_global_search_detail($params)
{
    extract($params);
    $srch_txt_re = str_replace('[', '\\\\[', str_replace(']', '\\\\]', str_replace('(', '\\\\(', str_replace(')', '\\\\)', str_replace('?', '\\\\?', str_replace('|', '\\\\|', $srch_txt)) ))));
    if ( strlen($srch_txt) > 2 && substr($srch_txt, 0, 2) == '\"' && substr($srch_txt, strlen($srch_txt)-2) == '\"' ) {
        $srch_txt_re = substr($srch_txt_re, 2, strlen($srch_txt_re)-4);
        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' c.creative_id creative_id,
            if(c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]", c.creative_name, "' . ucfirst($srch_txt_re) . '") as creative_name,
            b.brand_name as brand_name,
            b.brand_id as brand_id,
            adv.adv_id as adv_id,
            adv.need_help,
            c.thumbnail,
            c.length,
            c.response_tfn,
            c.response_url,
            c.response_sms,
            c.response_mar,
            adv.display_name as adv_name,
            c.creative_name,
            SUM(a.airings) as airings_count,
            SUM(a.rate) as spend_index,
            ' . $cols . '
        FROM    brand b,
                advertiser adv,
                creative c,
                airings_master a
        WHERE   adv.adv_id = b.adv_id
        AND     c.brand_id = b.brand_id
        AND     a.creative_id = c.creative_id
        AND     c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND     (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1)
        AND     (c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" OR c.keywords regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]")
        ' . $brand_classification . '
        GROUP BY c.creative_id order by c.creative_name desc';
    } else {

        $sql = 'SELECT ' . CACHE_SQL_QUERY . ' c.creative_id creative_id,
            c.creative_name as creative_name,
            b.brand_name as brand_name,
            b.brand_id as brand_id,
            adv.adv_id as adv_id,
            adv.need_help,
            c.thumbnail,
            c.length,
            c.response_tfn,
            c.response_url,
            c.response_sms,
            c.response_mar,
            adv.display_name as adv_name,
            c.master_tfn regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight01, c.master_tfn like "' . $srch_txt . '" as weight1,
            c.master_vanity regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight02, c.master_vanity like "' . $srch_txt . '" as weight2,
            c.master_url regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight03, c.master_url like "' . $srch_txt . '" as weight3,
            c.creative_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight04, c.creative_name like "%' . $srch_txt . '%" as weight4, MATCH(c.keywords) against ("' . $srch_txt . '") as weight5,
            adv.display_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight06, adv.display_name LIKE "%' . $srch_txt . '%" as weight6, MATCH(adv.alt_adv_names) against ("' . $srch_txt . '") as weight7,
            b.brand_name regexp "[[:<:]]' . $srch_txt_re . '[[:>:]]" as weight08, b.brand_name like "%' . $srch_txt . '%" as weight8, MATCH(b.alt_brand_names) against ("' . $srch_txt . '") as weight9,
            SUM(a.airings) as airings_count,
            SUM(a.rate) as spend_index,
            ' . $cols . '
        FROM    brand b,
                advertiser adv,
                creative c,
                airings_master a
        WHERE   adv.adv_id = b.adv_id
        AND     c.brand_id = b.brand_id
        AND     a.creative_id = c.creative_id
        AND     c.last_aired >= "' . LIFETIME_START_DATE . '"
        AND     (response_url=1 or response_sms=1 or response_tfn=1 or response_mar=1)
        AND     (c.master_tfn like "' . $srch_txt . '" or c.master_vanity like "' . $srch_txt . '" or c.master_url like "' . $srch_txt . '" or
                (c.creative_name like "%' . $srch_txt . '%" or c.keywords like "%' . $srch_txt . '%" or MATCH(c.keywords) against ("' . $srch_txt . '")) or
                (adv.display_name like "%' . $srch_txt . '%" or adv.alt_adv_names like "%' . $srch_txt . '%" or MATCH(adv.alt_adv_names) against ("' . $srch_txt . '")) or
                (b.brand_name like "%' . $srch_txt . '%" or b.alt_brand_names like "%' . $srch_txt . '%" or MATCH(b.alt_brand_names) against ("' . $srch_txt . '")))
        ' . $brand_classification . '
        GROUP BY c.creative_id order by weight1 desc, weight01 desc, weight02 desc, weight2 desc, weight03 desc, weight3 desc, weight04 desc, weight4 desc, weight5 desc, weight06 desc, weight6 desc, weight7 desc, weight08 desc, weight8 desc, weight9 desc';
    }
    return $sql;
}

function __query_get_tracking_details($params)
{
    extract($params);

    $sql = 'SELECT * from tracking_and_alerts
        WHERE user_id = ' . $user_id . '
        AND alert_type = "' . $alert_type . '"
        AND type_id IN (' . $type_id . ') ORDER BY created_date DESC';

    return $sql;
}

function __query_set_tracking_details($params)
{
    extract($params);

    $sql = 'INSERT INTO tracking_and_alerts (user_id,alert_type,type_id,track_elements,frequency,status,created_date,classification) VALUES (' . $user_id . ',"' . $alert_type . '",' . $type_id . ',"' . $tracked_elements . '","' . $frequency . '","' . $status . '","' . $created_date . '","' . $brand_class . '")';

    return $sql;
}

function __query_update_tracking_details($params)
{
    extract($params);

    if( isset($schedule_email) ){
        if(isset($brand_class)) {
            $brand_class = ", classification='". $brand_class."'";
        } else {
            $brand_class = '';
        }
        $sql = "UPDATE tracking_and_alerts SET frequency='" . $frequency . "', status='" . $status . "' $brand_class WHERE user_id = " . $user_id . " AND type_id = '" . $type_id . "';";
    } else {
        $sql = "UPDATE tracking_and_alerts SET track_elements='" . $tracked_elements . "',frequency='" . $frequency . "',status='" . $status . "',classification='" . $brand_class . "' WHERE user_id = " . $user_id . " AND alert_type = '" . $alert_type . "' AND type_id = '" . $type_id . "'";
    }

    return $sql;
}

function __query_get_networkid_by_code($params)
{
    extract($params);

    $sql = "SELECT network_id FROM network WHERE network_code = '" . $network_code . "'";

    return $sql;
}

function __query_get_networkid_by_alias($params)
{
    extract($params);

    $sql = "SELECT network_id FROM network WHERE network_alias = '" . $network_alias . "' ";

    return $sql;
}

function __query_get_networkid_by_name($params)
{
    extract($params);

    $sql = "SELECT network_id FROM network WHERE network_name = '" . $network_name . "' ";

    return $sql;
}

function __query_update_tracking_status($params)
{
    extract($params);

    $sql = "UPDATE tracking_and_alerts SET status='" . $status . "'
        WHERE user_id = " . $user_id . "
        AND alert_type = '" . $alert_type . "'
        AND type_id = '" . $type_id . "'";
    return $sql;
}

function __query_delete_tracking_details($params)
{
    extract($params);
    if(isset($schedule_email)) {
        if(isset($alert_type)) {
            if(isset($tracking_id)) {
                //$sql = "update user_filters set schedule_email = 0, frequency = 'none' where id in(select type_id from tracking_and_alerts where id in ('" . $tracking_id . "'));";
                $sql = "update user_filters set schedule_email = 0, frequency = 'none' where user_id = $user_id and id in ('" . $tracking_id . "');";
                $sql .= " update tracking_and_alerts set status = 'inactive', frequency = 'none' where user_id = $user_id and type_id in('" . $tracking_id . "');";
                // $sql .= " DELETE from tracking_and_alerts WHERE user_id = $user_id and id in($tracking_id);";
            } else {
                $sql = "update user_filters set schedule_email = 0, frequency = 'none' where id in($type_id);";
                $sql .= " DELETE from tracking_and_alerts WHERE user_id = $user_id and type_id in($type_id) /*and alert_type != 'filter'*/;";
            }
        }
    } else {
        $sql = "DELETE from tracking_and_alerts WHERE id = '" . $tracking_id . "' and alert_type != 'filter';";
    }
    return $sql;
}

function __query_get_tracking_alerts_data($params)
{
    extract($params);

    $sql = "SELECT
                creative.creative_id,
                creative.creative_name,
                creative.spanish,
                airings.airing_id,
                creative.adv_assigned AS creative_first_detection,
                creative.length as duration,
                creative.class as class,
                creative.type as type,
                creative.website,
                IF(creative.adv_assigned BETWEEN  '$start_date 00:00:00' AND '$end_date 23:59:59', 'NEW', 'OLD') as new_creative,
                brand.brand_id,
                brand.brand_name,
                brand.retail_report,
                brand.adv_assigned AS brand_first_detection,
                IF(brand.adv_assigned BETWEEN  '$start_date 00:00:00' AND '$end_date 23:59:59', 'NEW', 'OLD') as new_brand,
                advertiser.adv_id,
                advertiser.display_name,
                advertiser.create_date AS advertiser_first_detection,
                IF(advertiser.create_date BETWEEN  '$start_date 00:00:00' AND '$end_date 23:59:59', 'NEW', 'OLD') as new_advertiser,
                brand.main_sub_category_id,
                brand.alt_sub_category_id,
                GROUP_CONCAT(DISTINCT(airings.network_code)) as network_code,
                airings.network_code as network,
                COUNT(DISTINCT airings.airing_id) AS airings_count
            FROM creative
            INNER JOIN brand ON creative.brand_id = brand.brand_id
            INNER JOIN advertiser ON advertiser.adv_id = brand.adv_id
            INNER JOIN airings ON airings.creative_id = creative.creative_id
            $where AND creative.class != 'BRAND' AND advertiser.adv_id <> 0
            -- AND airings.start BETWEEN  '$start_date 00:00:00' AND '$end_date 23:59:59'
            GROUP BY creative.creative_id ORDER BY brand.brand_name ASC, creative.creative_name ASC, creative.length ASC";
            
    return $sql;   
}

function __query_get_tracking_data_each_user($params)
{
    extract($params);

    $sql = 'SELECT  * FROM `tracking_and_alerts`
            WHERE  alert_type = "category"
            AND user_id = "' . $user_id . '"';
    return $sql;
}

function __query_get_tracking_data_for_user($params)
{
    extract($params);

    if (isset($frequency)) {
        $frequency_clause = ' AND frequency like "%' . $frequency . '%" ';
    } else {
        $frequency_clause = '';
    }

    $sql = 'SELECT alert_type, GROUP_CONCAT(type_id) as type_ids, user.tracking_alert_subscribed FROM `tracking_and_alerts`
            INNER JOIN user ON tracking_and_alerts.user_id = user.user_id
            where alert_type != "filter" and tracking_and_alerts.status = "active"
            AND tracking_and_alerts.user_id = "' . $user_id . '"' . $frequency_clause . '
            GROUP by tracking_and_alerts.user_id, tracking_and_alerts.alert_type';
    return $sql;
}

function _sql_get_main_categories_list($params)
{
    extract($params);

    $sql = "SELECT " . CACHE_SQL_QUERY . "  *
        FROM categories group by category_id ORDER BY category, sub_category";
    return $sql;
}

function __query_get_users_list()
{

    $sql = "SELECT user_id from user WHERE status='active' order by user_id";

    return $sql;
}

function __query_get_users()
{

    $sql = "SELECT * from user WHERE status='active' order by user_id";
    return $sql;
}

function __query_get_companies()
{

    $sql = "SELECT * from company ";
    return $sql;
}

function __query_get_all_tracking_alert_subscribers()
{
    $sql = "SELECT user_id, email, first_name, last_name from user WHERE tracking_alert_subscribed = 1 and status = 'active';";
    //$sql = "SELECT user_id, email, first_name, last_name from user WHERE user_id IN (619, 633, 403)";
    return $sql;
}

function __query_get_all_scheduled_email_alert_subscribers($param)
{
    extract($param);

    if (isset($frequency)) {
        $frequency_clause = ' AND frequency like "%' . $frequency . '%" ';
    } else {
        $frequency_clause = '';
    }

    $sql = "SELECT u.user_id, email, first_name, last_name, username, name, query_string, page, primary_tab
            from user u, user_filters uf
            WHERE tracking_alert_subscribed = 1 and u.status = 'active' $frequency_clause
            and u.user_id = uf.user_id and uf.status = 'active';";
    //$sql = "SELECT user_id, email, first_name, last_name from user WHERE user_id IN (619, 633, 403)";
    return $sql;
}

function __query_get_all_tracking_alert_subscribers_all_users()
{
    $sql = "SELECT user_id, email, first_name, last_name from user";
    return $sql;
}

function __query_get_network_codes($param)
{
    extract($param);
    $sql = "SELECT GROUP_CONCAT(CONCAT('\"', network_code, '\"')) as network_codes from network WHERE network_id IN ($network_id)";

    return $sql;
}

function __query_get_all_categories()
{
    $sql = "SELECT `category_id`, `sub_category_id`, `category`, `sub_category` FROM `categories`";

    return $sql;
}

function __query_get_all_brands()
{
    $sql = "SELECT `brand_id`, `brand_name` FROM `brand`";

    return $sql;
}

function __query_get_all_advertisers()
{
    $sql = "SELECT `adv_id`, `display_name` FROM `advertiser`";

    return $sql;
}

function __query_get_category_brand_tracking_details($params)
{
    extract($params);

    $sql = 'SELECT GROUP_CONCAT(type_id) as brand FROM `tracking_and_alerts`
        WHERE status = "active"
        AND user_id = ' . $user_id . '
        AND track_elements LIKE "%brand%"
        AND alert_type = "' . $alert_type . '"
        GROUP BY user_id, alert_type';

    return $sql;
}

function __query_get_category_creative_tracking_details($params)
{
    extract($params);

    $sql = 'SELECT GROUP_CONCAT(type_id) as creative FROM `tracking_and_alerts`
        WHERE status = "active"
        AND user_id = ' . $user_id . '
        AND track_elements LIKE "%creative%"
        AND alert_type = "' . $alert_type . '"
        GROUP BY user_id, alert_type';

    return $sql;
}

function __query_get_category_frequency_and_classification_data($params)
{
    extract($params);

    $sql = 'SELECT frequency, classification FROM `tracking_and_alerts`
        WHERE status = "active"
        AND user_id = ' . $user_id . '
        AND alert_type = "' . $alert_type . '"
        GROUP BY user_id, alert_type';

    return $sql;
}

function __query_unsubscribe_user($params)
{
    extract($params);

    $sql = "UPDATE user
        SET tracking_alert_subscribed = '0'
        WHERE user_id = " . $user_id;

    return $sql;
}

function __query_delete_previous_tracking_details($params)
{
    extract($params);

    $sql = "DELETE from `tracking_and_alerts`
        WHERE user_id = " . $user_id . "
        AND alert_type = '" . $alert_type . "'";

    return $sql;
}

function __query_update_subscribe_status($params)
{
    extract($params);

    $sql = "UPDATE user
        SET tracking_alert_subscribed = '" . $subscribe_status . "'
        WHERE user_id = " . $user_id;

    return $sql;
}

function __query_get_subscribe_status($params)
{
    extract($params);

    $sql = "SELECT tracking_alert_subscribed
        FROM user
        WHERE user_id = " . $user_id;

    return $sql;
}

function _query_export_selected_brand($params)
{
    extract($params);
    $table = $table_join = $where_program = '';
    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);
    $network_filter = '';
    if (!empty($network_code)) {
        $network_filter = " AND d.network_id IN ('" . $network_id . "')";
    }
    if(!empty($program_ids)){
        $table          = ' ,program_master p';
        $table_join     = '  AND if(d.program = "", "Program unknown", d.program_id) = p.program_id';
        $where_program = ' AND p.program_id  IN ('.$program_ids.')';
    }
    
    $sql = "SELECT " . CACHE_SQL_QUERY . " c.creative_id,
         b.brand_name,
         b.brand_id,
         b.main_sub_category_id,
         b.alt_sub_category_id,
         adv.adv_id,
         c.creative_name,
         c.price,
         c.payments,
         c.class,
         b.brand_id parent_id,
         c.type,
         c.length,
         c.thumbnail,
         c.is_active,
         c.response_tfn,
         c.response_url,
         c.response_sms,
         c.response_mar,
         adv.display_name AS advertiser_name,
         adv.need_help,
         Count(*) airings , SUM(d.".RATE_COLUMN.") as projected_score $cols
         c.first_detection      AS first_aired_date,
         c.last_aired AS last_aired_date,
         sum(case when (c.spanish = 1) THEN 1 ELSE 0 END)  as spanish_creative_count,
         sum(case when (c.spanish = 0) THEN 1 ELSE 0 END)  as english_creative_count,
         count(c.creative_id) as total_creative_count
        FROM   " . AIRINGS_TABLE . " d ,
                creative c,
                brand b,
                advertiser adv $table
        WHERE    d.creative_id = c.creative_id $table_join
        AND      c.spanish IN ($spanish)
        AND      $responseType
        AND      d.brand_id = b.brand_id
        AND      b.brand_id in ($brand_ids)
        AND      b.adv_id = adv.adv_id
        AND      d.start_date >= '$sd 00:00:00'
        AND      d.start_date <= '$ed 23:59:59' $brand_classification
        $network_filter $new_filter_opt AND d.network_id  NOT IN (" . get_inactive_networks() . ") $where_program
        GROUP BY c.creative_id
        ORDER BY FIELD $order_by";
    return $sql;
}

function __query_get_media_calendar_data($params)
{
    $start_date = customDate('Y-m-d');
    if(isset($params) && !empty($params)) {
        extract($params);
        $start_date = $sd;
    }
    $sql = "SELECT media_year,media_week,media_week_start,media_week_end FROM media_calendar WHERE '" . $start_date . "' >= `media_week_start` GROUP BY media_year, media_week ORDER BY media_year DESC, media_week DESC";

    return $sql;
}

function __query_get_network_log_view($params)
{
    extract($params);
    $program_params = getProgramParams($params);
    $language_condition = '';
    $classification_condition = '';
    $adv_condition = $brand_condition = $creative_condition = $program_condition = $daypart_condition = '';
    if (!empty($program)) {
        $program_condition = ' AND airings.program = "' . $program . '" ';
    }
    if (!empty($daypart)) {
        $daypart_condition = ' AND a.daypart like "%' . $daypart . '%" ';
    }
    if (!empty($adv_id)) {
        $adv_condition = ' AND advertiser.adv_id = ' . $adv_id;
    }
    if (!empty($brand_id)) {
        $brand_condition = ' AND b.brand_id = ' . $brand_id;
    }
    if (!empty($creative_id)) {
        $creative_condition = ' AND c.creative_id = ' . $creative_id;
    }

    $and = 'AND';
    if ($filter_result == 'all') {
        $adv_condition = '';
        $brand_condition = '';
        $creative_condition = '';
        $program_condition = '';
        $daypart_condition = '';
        $categories = '';
        $brand_classification = '';
        $responseType = '';
        $and = '';
        $language_condition = '';
    }
    $dayOfWeek = getDayOfWeek(date('Y-m-d'));
    if($dayOfWeek == 'Monday') {
        $start_date = date("Y-m-d", strtotime("last Sunday"));
    }
    $sql = "SELECT  d.start_date, d.start airings_start, d.broadcast_start as start, d.length, d.breaktype, Substring(d.daypart, 8) AS daypart,
                    d.program, d.daypart,d.airing_id,b.retail_report, b.brand_name, b.brand_id,

                    c.class, c.type, c.creative_name, c.creative_id, c.thumbnail,
                    advertiser.display_name, advertiser.adv_id
            FROM `airings` as d
            inner join creative as c on c.creative_id = d.creative_id
                $language_condition
            inner join brand as b on b.brand_id = d.brand_id $categories $brand_classification
            inner join advertiser on advertiser.adv_id = b.adv_id
            ".$program_params['join_condition'] . $program_params['table_join_on']."
            WHERE d.brand_id != 0 AND d.broadcast_start_date >= '$start_date' AND
            d.broadcast_start_date < date_add('$start_date', interval 1 day)
            " . $and . " " . $responseType . "
            AND d.network_id = $network_id
            $adv_condition $brand_condition $creative_condition $program_condition $daypart_condition ".$program_params['where_program']."
            group  by d.start, d.length, program, daypart, d.creative_id, b.brand_id, advertiser.adv_id ORDER BY d.start";
    return $sql;
}

function __query_get_media_year_only()
{

    $sql = "SELECT DISTINCT media_year FROM `media_calendar` WHERE '" . customDate('Y-m-d') . "' >= `media_week_start` ORDER BY media_year DESC";

    return $sql;
}

function __query_get_cached_data($params)
{
    extract($params);
    $sql = "SELECT result FROM cached_response where component = '{$component}' AND clause = '$clause' AND expiry_time >= '" . date('Y-m-d H:i:s') . "'";
    return $sql;
}

function get_query_result($function_name, $params = array(), $type = 'FETCH_ASSOC')
{
    $sql = $function_name($params);

    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    if ($type == 'FETCH_ASSOC') {
        $resultset = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $resultset = $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    return $resultset;
}

function transformed_sql_variable($sql)
{
    return preg_replace("/[^A-Za-z0-9]+/", "_", $sql);
}

function execute_query_get_result($sql, $type = 'FETCH_ASSOC')
{
    $transformed_sql_variable = transformed_sql_variable($sql);

    $CONSTANT_SQL_NAME = $transformed_sql_variable . $type;
    if (defined($CONSTANT_SQL_NAME)) {
        return unserialize(constant($CONSTANT_SQL_NAME));
    }

    $db = getConnection();
    $stmt = $db->prepare($sql);
    //  show($sql);
    $stmt->execute();
    if ($type == 'FETCH_ASSOC') {
        $resultset = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $resultset = $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    if (!defined($CONSTANT_SQL_NAME)) {
        define($CONSTANT_SQL_NAME, serialize($resultset));
    }

    return $resultset;
}

function execute_query($function_name, $params)
{
    $sql = $function_name($params);

    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $function_name == '__qurey_insert_user_filters' || $function_name == '__qurey_insert_user_list' ? $db : true;
}

function execute_sql($sql)
{
    $db = getConnection();
    $stmt = $db->prepare($sql);
    // show($sql);
    $stmt->execute();
    return true;
}

foreach ($queries as $var => $query) {
    define(strtoupper($var), $query);
}

function __query_get_subcategory_detail()
{
    $sql = "SELECT category, sub_category_id, sub_category FROM categories";
    return $sql;
}

function __query_get_all_category_detail()
{
    $sql = "SELECT category_id, category, GROUP_CONCAT(CONCAT(sub_category_id, '$', sub_category) separator '|') as Sub from categories group by category_id";
    return $sql;
}

function __query_get_user_alerts($params)
{
    extract($params);
    $or = ' OR ';
    $opt = 'AND NOT';
    if ($from == 'email') {
        $opt = 'AND';
    }
    $clause_alert_type = $where_clause = '';
    if (isset($brand)) {
        $clause_alert_type .= "(alert_type = 'brand' AND type_id = '" . $brand . "')" . $or;
    }
    if (isset($advertiser)) {
        $clause_alert_type .= "(alert_type = 'advertiser' AND type_id = '" . $advertiser . "')" . $or;
    }
    if (isset($network)) {
        $clause_alert_type .= "(alert_type = 'network' AND type_id IN (" . $network . "))" . $or;
    }
    if (isset($category)) {
        $clause_alert_type .= "(alert_type = 'category' AND type_id = '" . $category . "')" . $or;
    }
    $sWhereClause = '(alert_type != "filter" or schedule_email = 1) and ';
    if (isset($type_id)) {
        $clause_alert_type .= "(alert_type = 'filter' AND type_id = '" . $type_id . "')" . $or;
        $sWhereClause = '';
    }

    if (!empty($clause_alert_type)) {
        $where_clause = $opt . " (" . rtrim($clause_alert_type, $or) . ")";
    }

    $sql = "SELECT ta.id, alert_type, type_id, classification, ta.frequency, ta.status, ta.created_date, uf.criteria, if(instr(uf.criteria, 'Last Week'), 'weekly', if(instr(uf.criteria, 'Current Week'), 'daily', if(instr(uf.criteria, 'Last Month'), 'monthly', if(instr(uf.criteria, 'Last Quarter'), 'quarterly', '')))) email_schedulable_direct
            FROM tracking_and_alerts ta left join user_filters uf on alert_type = 'filter' and ta.type_id = uf.id and ta.user_id = uf.user_id
            WHERE ".$sWhereClause." ta.user_id = '" . $user_id . "' " . $where_clause . " ORDER by created_date desc";
    return $sql;
}

function __query_adv_zoho_data($params)
{
    extract($params);
    $sql = "SELECT zoho_id,data FROM zoho_adv_and_agency_details WHERE adv_id = '" . $advertiser_id . "' ";
    return $sql;
}

function __query_adv_contact_data($params)
{
    extract($params);
    $sql = "SELECT data FROM zoho_contacts WHERE zoho_owner_id = '" . $zoho_adv_id . "' ";
    return $sql;
}

function __query_adv_agency_data($params)
{
    extract($params);
    $sql = "SELECT zoho_agency_id FROM zoho_adv_agency_mapping WHERE zoho_adv_id = '" . $zoho_adv_id . "' ";
    return $sql;
}

function __query_agency_data($params)
{
    extract($params);
    $sql = "SELECT zoho_id,data FROM zoho_adv_and_agency_details WHERE zoho_id = '" . $zoho_agency_id . "' ";
    return $sql;
}

function _query_validate_info($params)
{
    extract($params);
    if ($type == 'advertiser' || $type == 'agency') {
        $sql = "SELECT zoho_id,data FROM zoho_adv_and_agency_details WHERE zoho_id = '" . $zoho_id . "' ";
    } else if ($type == 'contact') {
        $sql = "SELECT zoho_id,data FROM zoho_contacts WHERE zoho_id = '" . $zoho_id . "' ";
    }
    return $sql;
}

function __query_get_all_active_inactive_networks()
{
    $sql = "SELECT `network_id`, `network_code`, `network_name`, `network_alias`, `dpi`, `live_date`, diginet FROM `network` WHERE status = '1' AND network_code <> 'DRMETRIX' ORDER BY network_alias ASC";
    return $sql;
}

function __query_get_networks_max_broadcast_startdate($params)
{
    extract($params);
    /*$sql = "SELECT max(broadcast_start) as sdate FROM `airings` where network_code = '".$network_code."' GROUP by network_code";*/
    $sql = "SELECT live_date as sdate FROM `network` where network_id   = '" . $network_id   . "'";
    return $sql;
}

function __query_get_network_first_last_aired_date($params)
{
    extract($params);
    $sql = "SELECT first_airing, last_airing FROM creative_network_log WHERE creative_id = '" . $creative_id . "' AND network_id  ='" . $network_id  . "'";
    return $sql;
}

function __query_get_networks_first_last_aired_date($params)
{
    extract($params);
    $sql = "SELECT first_airing, last_airing, network_code FROM creative_network_log WHERE creative_id = '" . $creative_id . "' AND network_code IN ('".$network_id. "')";
    return $sql;
}

function get_inactive_networks()
{
    if (defined('INACTIVE_NETWORKS')) {
        return INACTIVE_NETWORKS;
    }
    $sql = "SELECT GROUP_CONCAT(network_id) as network_ids FROM network WHERE status = '0'";
    
    $inactive_networks_array = execute_query_get_result($sql, 'FETCH_ASSOC');

    if (count($inactive_networks_array) < 1) {
        $network_ids = 'NO_INACTIVE_NETWORK';
    } else {
        $network_ids = $inactive_networks_array[0]['network_ids'];
    }

    $inactive_networks = "'" . str_replace(',', "','", $network_ids) . "'";

    define('INACTIVE_NETWORKS', $inactive_networks);

    return $network_ids;
}

function __query_check_company_video_downloads_limit($params)
{
    extract($params);
    $sql = "SELECT video_download_limit  FROM `company` where id = '" . $_SESSION['company_id'] . "'";
    return $sql;
}

function __query_check_count_video_downloads($params)
{
    extract($params);
    $sql = "SELECT count(airing_id) as count_video_downloads FROM `user_video_downloads` where  user_id = '" . $_SESSION['user_id'] . "' AND YEAR(created_date) = YEAR(NOW()) AND MONTH(created_date) = MONTH(NOW()) group by MONTH(created_date), YEAR(created_date) ";
    return $sql;
}

function __query_add__user_video_downloads($params)
{
    extract($params);
    $sql = "INSERT INTO user_video_downloads (user_id, airing_id, created_date) VALUES ('" . $_SESSION['user_id'] . "', $adid, NOW() )";
    return $sql;
}
function __query_get_user_of_company($params)
{
    if (isset($params['check_contact']) && $params['check_contact'] == 1) {
        $where = '';
    } else {
        $where = " u.status IN ('active','inactive') and ";
    }
    extract($params);
    $sql = "SELECT u.first_name, u.last_name, u.role,u.user_id,u.username,u.status,u.zoho_contact_id,u.role, u.email FROM user u WHERE  $where user_id IN (SELECT user_id FROM admin_user  a WHERE admin_id = (SELECT user_id FROM  user where company_id = '" . $company_id . "')) OR u.company_id = ('" . $company_id . "')  ORDER BY CONCAT(u.first_name, u.last_name) ASC";
    return $sql;

}

function __query_get_active_user_of_company($params)
{
    if (isset($params['check_contact']) && $params['check_contact'] == 1) {
        $where = '';
    } else {
        $where = "u.status != 'deleted' and ";
    }
    extract($params);
    $sql = "SELECT u.user_id, u.zoho_contact_id FROM user u WHERE  $where user_id IN (SELECT user_id FROM admin_user  a WHERE admin_id = (SELECT user_id FROM  user where company_id = '" . $company_id . "')) AND status != 'pre_inactive';";
    return $sql;

}


function __query_get_inactive_user_of_company($params)
{
    extract($params);
    $sql = "SELECT u.user_id FROM user u WHERE  user_id IN (SELECT user_id FROM admin_user  a WHERE admin_id = (SELECT user_id FROM  user where company_id = '" . $company_id . "')) AND u.status='inactive';";
    return $sql;

}

function __query_delete_from_pricing($params)
{
    extract($params);
    $sql = "DELETE FROM pricing WHERE company_id IN  ($company_id)";
    return $sql;
}

function __query_delete_user_from_excel_export($params)
{
    extract($params);
    $sql = "DELETE FROM excel_exports WHERE user_id IN ($user_id)";
    return $sql;
}

function __query_delete_user_result_log($params)
{
    extract($params);
    $sql = "DELETE FROM result_log WHERE email IN ('" . $email_id . "') ";
    return $sql;
}

function __query_delete_user_tracking_alerts($params)
{
    extract($params);
    if( isset($schedule_email) ) {
        $delete_filter = " update user_filters set schedule_email = 0, frequency = 'none' where id in ($user_id)";
        if(isset($alert_type)) {
            $delete_filter = '; ' . $delete_filter;
        } else {
            $delete_filter = " and alert_type != 'filter'; " . $delete_filter;
        }
    } else {
        $delete_filter = ' and alert_type != "filter" ';
    }
    $sql = "DELETE FROM tracking_and_alerts WHERE user_id IN ($user_id) $delete_filter;";
    return $sql;
}

function __query_delete_user_from_user($params)
{
    extract($params);
    $sql = "DELETE FROM user  WHERE user_id  IN  ($user_id) ";
    return $sql;
}

function __query_delete_user_from_user_filters($params)
{
    extract($params);
    $sql = "DELETE FROM user_filters WHERE user_id IN  ($user_id) ";
    return $sql;
}

function __query_delete_user_from_userlogs($params)
{
    extract($params);
    $sql = "DELETE FROM user_logs WHERE user_id IN  ($user_id) ";
    return $sql;
}

function __query_get_user_from_admin_user($params)
{
    if (!isset($params['type'])) {$type = '';}
    extract($params);
    if ($type != '') {
        $sql = "SELECT user_id from admin_user WHERE admin_id  IN ($user_id) ";
    } else {
        $sql = "SELECT id from admin_user WHERE user_id  IN ($user_id) ";
    }
    return $sql;
}

function __query_delete_user_from_search_logs($params)
{
    extract($params);
    $sql = "DELETE FROM search_log WHERE user_id IN  ($user_id) ";
    return $sql;
}

function __query_delete_company_from_company($params)
{
    extract($params);
    $sql = "DELETE FROM company WHERE id IN  ($company_id) ";
    return $sql;
}

function __query_delete_admin_user_from_admin_user($params)
{
    extract($params);
    $sql = "DELETE FROM admin_user WHERE admin_id IN  ($admin_id) ";
    return $sql;
}

function __query_delete_user_from_admin_user($params)
{
    extract($params);
    $sql = "DELETE FROM admin_user WHERE id  IN ($id) ";
    return $sql;
}

function __query_update_user_id_in_pricing($params)
{
    extract($params);
    $sql = " UPDATE pricing SET user_id = '" . $user_id . "' WHERE company_id = '" . $company_id . "'";
    return $sql;

}

function __query_update_user_in_users($params)
{
    extract($params);
    $sql = " UPDATE user SET role = 'user' , company_id = NULL WHERE user_id = " . $admin_id;
    $sql .= "; UPDATE user SET role = 'admin' , assistant_admin = 0,  company_id ='" . $company_id . "' WHERE user_id = " . $user_id;
    return $sql;
}

function __query_swap_admin_user($params)
{
    extract($params);
    $sql = "UPDATE  admin_user
   SET admin_id=@temp:=admin_id, admin_id = user_id, user_id = @temp WHERE user_id = '" . $user_id . "'";
    return $sql;
}

function __query_update_admin_id_admin_user($params)
{
    extract($params);
    $sql = " UPDATE admin_user SET admin_id = '" . $user_id . "' WHERE admin_id = '" . $admin_id . "'";
    return $sql;
}

function __query_delete_tracking_alerts($params)
{
    extract($params);
    if ($params['delete_all'] == 'all') {
        $sql = "update user_filters set schedule_email = 0, frequency = 'none' where user_id in ($user_id);";
        if(isset($alert_type)) {
            $sql .= " DELETE FROM tracking_and_alerts WHERE user_id IN ('" . $user_id . "');";
        }
    } else {
        $sql = "update user_filters set schedule_email = 0, frequency = 'none' where id in (select type_id from tracking_and_alerts where id in ('" . $tracking_ids . "')); DELETE FROM tracking_and_alerts WHERE id IN ('" . $tracking_ids . "') and alert_type != 'filter'; update tracking_and_alerts set frequency = 'none', status = 'inactive' WHERE id IN ('" . $tracking_ids . "') and alert_type = 'filter';";
    }
    return $sql;
}

function __query_get_company_owners()
{
    $sql = "SELECT id, zoho_contact_id,email, name from account_owner WHERE status = 1 ";
    return $sql;
}

function __query_update_delete_by_admin_field($params)
{
    extract($params);
    $sql = " UPDATE user SET status = 'deleted' WHERE user_id IN ('" . $user_id . "')";
    return $sql;
}

function __query_update_user_company_id($params)
{
    extract($params);

    $update_query = "UPDATE user SET company_id =  $company_id WHERE user_id = $user_id";
    return $update_query;
}

function __query_update_pricing_company_id($params)
{
    extract($params);
    $update_query = "UPDATE pricing SET company_id =  $company_id WHERE user_id = $user_id";
    return $update_query;
}

function __query_get_domain_override($params)
{
    extract($params);

    $sql = "SELECT SUBSTRING_INDEX(email, '@', -1) as domain, c.company_name, c.id as company_id,u.zoho_contact_id, u.user_id ,count(*) as Total
      FROM user u LEFT JOIN company c ON c.id = u.company_id where u.role = 'admin'
GROUP BY domain
ORDER BY Total DESC";

// $domain_name = strchr($admin_email,'@');
    // $domain_name = trim($domain_name, '@');
    // $sql = "SELECT SUBSTRING_INDEX(email, '@', -1) as domain, c.company_name, c.id as company_id,u.zoho_contact_id, u.user_id
    // FROM user u INNER  JOIN company c ON c.id = u.company_id WHERE SUBSTRING_INDEX(email, '@', -1) LIKE '".$domain_name."'
    // ORDER BY c.id DESC LIMIT 0, 1";

// $sql = "SELECT SUBSTRING_INDEX(email, '@', -1) as domain, c.company_name, c.id as company_id,u.zoho_contact_id, u.user_id
    // FROM user u INNER  JOIN company c ON c.id = u.company_id WHERE SUBSTRING_INDEX(email, '@', -1) LIKE '".$domain_name."'
    // ORDER BY c.id DESC LIMIT 0, 1";
    // show($sql);
    return $sql;
}

function __query_get_contact_name_same_company($params)
{
    extract($params);
    // $sql        = "SELECT u.first_name, u.last_name , u.status FROM user u LEFT JOIN company c ON c.id = u.company_id WHERE c.company_name = '$company_name'";
    $sql = "SELECT u.first_name, u.last_name , u.status, u.email, u.zoho_contact_id, u.user_id FROM user u INNER JOIN admin_user au ON  u.user_id = au.user_id where u.status != 'deleted' and  au.admin_id = " . $admin_id . " ORDER by status $order_by";
    return $sql;
}

function __query_get_programs_by_network_id($params) {
    extract($params);
    $where_refine_by = '';
    $new_filter_opt = newFilter($new_filter_opt, $sd, $ed);

    if ($refine_filter_opt == '800') {
        $refine_filter_opt_text = getRefineTextWithStringFilters($refine_filter_opt_text);
        $where_refine_by = 'AND tfn_num  LIKE "%' . $refine_filter_opt_text . '%"';
    } else if ($refine_filter_opt == 'url') {
        $refine_filter    = getUrlFilters($refine_filter_opt_text);
        $where_refine_by  = $refine_filter['where'];
    }

    if($refine_filter_opt == '800' || $refine_filter_opt == 'url') {
        $where_refine_by .= ' AND d.verified = 1';
    }
    $sql = 'SELECT   '.CACHE_SQL_QUERY.' DISTINCT(program)
        FROM     '.AIRINGS_TABLE.' d ,  creative c,
                 brand b, 
                 advertiser adv
        WHERE    d.brand_id = c.brand_id
        AND      d.creative_id = c.creative_id
              '.$responseType.'
        AND      start BETWEEN  "'.$sd.' 00:00:00"  AND "'.$ed.' 23:59:59"
        AND      c.brand_id = b.brand_id 
        AND      b.adv_id = adv.adv_id 
       AND d.network_id = '.$network_id.'
        AND     spanish IN ('. $spanish . ') '. $new_filter_opt.$where_refine_by.$brand_classification.$categories.'
              ORDER BY program DESC';
    return $sql;

}

/**Start -- List related queries***/
function __query_get_user_list($params)
{
    extract($params);
    $_order_by = '';
    if (isset($order_by)) {
        $_order_by = $order_by;
    }
    $sql = "SELECT ul.*,u.parent_id as parent_id,concat(exu.first_name, ' ', exu.last_name) as full_name from users_list ul  LEFT JOIN
    users_list u ON ul.id = u.parent_id  LEFT JOIN user exu  ON ul.shared_by = exu.user_id WHERE ul.user_id = ".$user_id." AND ul.status='active' AND ul.primary_tab='" . $primary_tab . "' " . $_order_by;

    return $sql;
}

function __query_get_all_list_data($params) { 
    extract($params);
    $sql = "SELECT * FROM users_list WHERE user_id = " . $_SESSION['user_id'] . " AND primary_tab = '" . $primary_tab . "' AND status <> 'deleted'";
    return $sql;
}

function __query_get_brands_advertisers_list($params) {
    $table_name = $params['tab'] == 1 ? 'brand' : 'advertiser';
    $where = $params['tab'] == 1 ? '' : ' WHERE deleted = 0';
    $order_col_name   = $params['tab'] == 1 ? 'brand_name' : 'display_name';
    $cols   = $params['tab'] == 1 ? 'brand_id as id, brand_name as name, alt_brand_names as alt_name' : 'adv_id as id, display_name as name, alt_adv_names as alt_name';
    $sql = "SELECT ".$cols." FROM ".$table_name .$where.' ORDER BY '.$order_col_name;
    return $sql;
}

function __query_edit_brands_advertisers_list($params) {
    extract($params);
    $sql = "UPDATE users_list SET criteria_id = '".$selected_ids."' WHERE  id =".$id;
    return $sql;
}

function __query_update_filter($params) {
    extract($params);
  
    $sql = "UPDATE user_filters SET query_string = '".$newQueryString."' , criteria_id = '".$criteria_id."' WHERE  id =".$filter_id;
    return $sql;
}

function __query_get_name_of_criteria($params) {
    extract($params);
    $col_name   = ($primary_tab == 'brand') ? 'brand_name' : 'display_name';
    $table_name =  ($primary_tab == 'brand') ? 'brand' : 'advertiser';
    $where_condition = ($primary_tab == 'brand') ? ' brand_id IN ('.$criteria_id.')' : ' adv_id IN ('.$criteria_id.')';
    $sql = "SELECT GROUP_CONCAT(".$col_name.") as criteria_name FROM  ".$table_name." WHERE ".$where_condition;
    return $sql;
}

function __query_get_brands_for_adv($params) {
    extract($params);
    $sql = "SELECT GROUP_CONCAT(brand_id) as brand_ids FROM brand WHERE adv_id IN (".$applied_ids.")";
    return $sql;
}

function __query_delete_user_List($params)
{
    extract($params);
    $sql = "UPDATE users_list SET status = 'deleted' WHERE id IN ( " . $id . ")";
    return $sql;
}

function __query_update_for_my_list($params) {
    extract($params);
    $sql = "UPDATE users_list SET name = '" . $name . "' WHERE id = " . $id;
    return $sql;
}

function __query_get_adv_for_brands($params) {
    extract($params);
    $sql = "SELECT GROUP_CONCAT(adv_id) as adv_ids FROM brand WHERE brand_id IN (".$applied_ids.") AND last_aired >='".$ed."'";
    return $sql;
}

function __query_get_filter_name($params) {
    extract($params);
    $sql = "SELECT name FROM users_list WHERE id IN (".$list_id.")";
    return $sql;
}

/**End -- List related queries***/

