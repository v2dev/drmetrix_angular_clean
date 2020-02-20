<?php
error_reporting(E_ALL);

define("EXCEL_PATH", '/var/www/html/drmetrix/');

require_once(dirname(__FILE__).'PHPExcel_1.8.0_doc/Classes/PHPExcel.php');
require_once(dirname(__FILE__).'PHPExcel_1.8.0_doc/Classes/PHPExcel/IOFactory.php');
require_once 'config.php';

$db = getConnection();

// Open a directory, and read its contents
if (is_dir(EXCEL_PATH)){
    if ($dh = opendir(EXCEL_PATH)){
        while (($file = readdir($dh)) !== false){
            if((strpos($file,'xls') !== FALSE) || (strpos($file,'xlsx'))){

                //assign table name, update coulmn and condition column
                if(strpos($file,'advertiser') !== FALSE){
                    $table_name         = "advertiser";
                    $update_column      = "alt_adv_names";
                    $condition_column   = "adv_id";

                } elseif (strpos($file,'brand') !== FALSE) {
                    $table_name         = "brand";
                    $update_column      = "alt_brand_names";
                    $condition_column   = "brand_id";

                } elseif (strpos($file,'creative') !== FALSE) {
                    $table_name         = "creative";
                    $update_column      = "keywords";
                    $condition_column   = "creative_id";
                }

                $inputFileName = EXCEL_PATH . $file;
                try {
                    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($inputFileName);
                } catch (Exception $e) {
                    die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . 
                        $e->getMessage());
                }

                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestDataColumn();

                for ($row = 1; $row <= $highestRow; $row++) {
                    if($row == 1) continue;     // skip header line
                    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);

                    $row_data = $rowData[0];
                    $update_sql = "UPDATE " . $table_name . " SET " . $update_column . "= '" . addslashes($row_data[2]) . "' WHERE " . $condition_column . " = " . $row_data[0];

                    //Prints out update query for each row
                    // echo '<pre>';
                    // print_r($update_sql);
                    // echo '</pre>';

                    $stmt = $db->prepare($update_sql);
                    $stmt->execute();

                }
            } else {
                echo "No excel file present!";
            }
        }
        closedir($dh);
    }
}
