<?php
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
require_once dirname(__FILE__) . '/Spout/Autoloader/autoload.php';
/*
$style = (new StyleBuilder())
    ->setFontName('Arial')
    ->setFontSize(11)
    ->build();
*/

/*function createDir($user_id) {
    checkDirAndCreate(dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.date('Y-m-d'));
    checkDirAndCreate(dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.date('Y-m-d').'/'.date('H_i_s'));
    checkDirAndCreate(dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.date('Y-m-d').'/'.date('H_i_s').'/'.$user_id);
    
    $dir_name = dirname(__FILE__) . '/../'.LARGE_EXCEL_DOWNLOAD_PATH.date('Y-m-d').'/'.date('H_i_s').'/'.$user_id.'/';
    return $dir_name;
}

function checkDirAndCreate($path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

function transformDate($date) {
    $dateArr = explode('-', $date);
    return $dateArr[1] . '-' . $dateArr[2] . '-' . $dateArr[0];
}

function getDateDifferenceInHours() {
    $t1 = StrToTime ( '2016-04-14 11:30:00' );
    $t2 = StrToTime ( '2016-04-12 12:30:00' );
    $diff = $t1 - $t2;
    $hours = $diff / ( 60 * 60 );
}

function getFilename($file_name) {
    $arr = explode('/', $file_name);
    return $arr[count($arr) - 1];
}*/
function getDayType($daytype) {
    $display_daytype = '';
    $display_daytype = ($daytype == 'broadcast') ? ' (Broadcast Day)' : ' (Calendar Day)';
    return $display_daytype;

}

function _downloadShortFormSummaryExcel($sql, $day_type, $file_name, $user_id, $name, $date_range_str, $excel_values, $id='') {
    set_time_limit(0);

    ini_set('memory_limit','8192M');
    $db = getConnection();

    require_once(dirname(__FILE__).'/PHPExcel_1.8.0_doc/Classes/PHPExcel.php');
    
    $styleHeader = array(
        'font'  => array(
            'size'  => 16,            
            'name'  => 'Calibri'
        ),
        'alignment' => array(
            //'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
         'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
                'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                'rotation'   => 90,
                'startcolor' => array(
                    'argb' => 'B5B5B5'
                ),
                'endcolor'   => array(
                    'argb' => 'E0E0E0'
                )
            )
    );
    
    $styleSubHeader = array(
        'font'  => array(
            'color' => array('rgb' => 'FFFFFF'),
            'size'  => 11,
            'bold'  => true,
            'name'  => 'Calibri'
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '202b39')
        )
    );

    $styleArray = array(
        'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
        )
    );

    $objPHPExcel = new PHPExcel();
    $dir_name = createDir($user_id);
    $file_name = $dir_name.$file_name;
    $newFilePath        = $file_name;

    //Image Add
    $logo = IMAGE ;
    if(isset($logo)){
        $ImageDimension = getimagesize($logo);
        $imageWidth = $ImageDimension[0];
        $imageHeight = $ImageDimension[1];
    }  

    $daytype_string = getDayType($excel_values['day_type']);
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText("  ".$name." - Summary Report\n");
    $objBold = $objRichText->createTextRun("    ".$date_range_str. $daytype_string);
    $objBold->getFont()->setBold(true);

    $query = explode('===',$sql);
    $m = 0;
    if(count($query) == 3){
        //For networks
        $n = 0; $m = 1; $p = 2;
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');     
        $objDrawing->setPath($logo);  //setOffsetY has no effect
        $objDrawing->setCoordinates('A1');    
        $objDrawing->setHeight($imageHeight);
        $objDrawing->setWidth($imageWidth);
        $objDrawing->setOffsetX(20);
        $objPHPExcel->setActiveSheetIndex($n)->getCell('B1')->setValue($objRichText);    
        $objPHPExcel->setActiveSheetIndex($n)->getStyle('B1')->getAlignment()->setWrapText(true);    
        $objPHPExcel->setActiveSheetIndex($n)->getRowDimension('1')->setRowHeight(69);
        $objPHPExcel->setActiveSheetIndex($n)->mergeCells('B1:N1');
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("B1:N1")->applyFromArray($styleHeader);
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("A".(2).":N".(2))->applyFromArray($styleArray, False);
        $header_array = array('A2'=>'Station Code', 'B2'=>'Station Name', 'C2'=>'Creatives', 'D2'=>'Length', 'E2'=>'rosDay',  'F2'=>'rosTime', 'G2'=>'Total Airings','H2'=>'Total Spend ($)','I2'=>'National Airings', 'J2'=>'National %', 'K2'=>'National Spend ($)', 'L2'=>'DPI Airings','M2'=>'DPI %','N2'=>'DPI Spend ($)');
        foreach($header_array as $key => $val){
            $objPHPExcel->setActiveSheetIndex($n)->SetCellValue($key,$val);
        }
        $objPHPExcel->setActiveSheetIndex($n)->getRowDimension('2')->setRowHeight(24);
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("A2:N2")->applyFromArray($styleSubHeader);
        $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension("A")->setWidth(30);
        foreach(range('B','N') as $columnID) {
            $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        $i=3;
        $rows = getResult($query[1]); 
        $rows = reorderResultForRankingArrayForExcel($rows); 

       $column_index_array = ['G','H','I','K','L','N'];
       setColumnNumberFormat($objPHPExcel , $column_index_array);

        if (!empty($rows)) {
            //arsort($resultArr);
            $rowNum = 1;
            foreach ($rows as $key => $row) {
                //$row = $result[$key];
                extract($row);
                $rosDay = substr($daypart, 0, -18);
                $rosTime = substr($daypart, -17);
                $objPHPExcel->setActiveSheetIndex($n)->getStyle("A".($i).":N".($i))->applyFromArray($styleArray, False);
                $resp_array = array('A'.$i=>$network_code,'B'.$i=>$network_alias, 'C'.$i=>$ccount,'D'.$i=>$length,'E'.$i=>$rosDay,'F'.$i=> $rosTime,'G'.$i=>$count,'H'.$i=>$total_spend,'I'.$i=>$national,'J'.$i=>$nationalP,'K'.$i=>$national_spend,'L'.$i=>$local,'M'.$i=>$localP,'N'.$i=>$local_spend);
                foreach($resp_array as $key => $val){                      
                    $objPHPExcel->setActiveSheetIndex($n)->SetCellValue($key, $val);
                }
                $i++;
                update_excel_progress($id, $rowNum++);
            }
        }else{
            $objPHPExcel->setActiveSheetIndex($n)->mergeCells('A'.$i.':N'.$i);
            $objPHPExcel->setActiveSheetIndex($n)->SetCellValue('A'.$i, "No records found.");
            $objPHPExcel->setActiveSheetIndex($n)->getStyle('A'.$i)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );
        }
        unset($rows);
        $objPHPExcel->setActiveSheetIndex($n)->setTitle("Network");
        $objDrawing->setWorksheet($objPHPExcel->setActiveSheetIndex($n));
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($m);
    }

    //For Creatives  
    $objDrawing1 = new PHPExcel_Worksheet_Drawing();
    $objDrawing1->setName('Logo');
    $objDrawing1->setDescription('Logo');     
    $objDrawing1->setPath($logo);  //setOffsetY has no effect
    $objDrawing1->setCoordinates('A1');    
    $objDrawing1->setHeight($imageHeight);
    $objDrawing1->setWidth($imageWidth);
    $objDrawing1->setOffsetX(20);
            
    $objPHPExcel->setActiveSheetIndex($m)->setTitle("Creative");
    $objDrawing1->setWorksheet($objPHPExcel->setActiveSheetIndex($m));

    $objPHPExcel->setActiveSheetIndex($m)->getCell('B1')->setValue($objRichText);    
    $objPHPExcel->setActiveSheetIndex($m)->getStyle('B1')->getAlignment()->setWrapText(true);    
    $objPHPExcel->setActiveSheetIndex($m)->getRowDimension('1')->setRowHeight(69);
    $objPHPExcel->setActiveSheetIndex($m)->mergeCells('B1:O1');
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("B1:O1")->applyFromArray($styleHeader);
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("A".(2).":O".(2))->applyFromArray($styleArray, False);
    $header_array = array('A2'=>'Station Code', 'B2'=>'Station Name', 'C2'=>'Creative', 'D2'=>'Brand', 'E2'=>'Length', 'F2'=>'rosDay',  'G2'=>'rosTime', 'H2'=>'Total Airings', 'I2'=>'Total Spend','J2'=>'National Airings','K2'=>'National %','L2'=>'National Spend ($)','M2'=>'DPI Airings','N2'=>'DPI %','O2'=>'DPI Spend ($)');
    foreach($header_array as $key => $val){
        $objPHPExcel->setActiveSheetIndex($m)->SetCellValue($key,$val);
    }
    $objPHPExcel->setActiveSheetIndex($m)->getRowDimension('2')->setRowHeight(24);
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("A2:O2")->applyFromArray($styleSubHeader);
    $objPHPExcel->setActiveSheetIndex($m)->getColumnDimension("A")->setWidth(30);
    foreach(range('B','O') as $columnID) {
        $objPHPExcel->setActiveSheetIndex($m)->getColumnDimension($columnID)
            ->setAutoSize(true);
    }
    $result  =  array();
    $i=3;
    $rows = getResult($query[0]);  
    $rows = reorderResultForRankingArrayForExcel($rows); 

    $column_index_array = ['H','I','K','L','M','N','O'];
    setColumnNumberFormat($objPHPExcel , $column_index_array);

    if (!empty($rows)) {
        //arsort($resultArr);
        //echo "<pre>"; print_r($rows); exit;
        $rowNum = 1;
        foreach ($rows as $key => $row) {
            //$row = $result[$key];
            extract($row);
            $rosDay = substr($daypart, 0, -18);
            $rosTime = substr($daypart, -17);
            $objPHPExcel->setActiveSheetIndex($m)->getStyle("A".($i).":O".($i))->applyFromArray($styleArray, False);
            $resp_array = array('A'.$i=>$network_code,'B'.$i=>$network_alias, 'C'.$i=>$creative_name,'D'.$i=>$brand_name,'E'.$i=>$length,'F'.$i=>$rosDay,'G'.$i=> $rosTime,'H'.$i=>$count,'I'.$i=>$total_spend,'J'.$i=>$national,'K'.$i=>$nationalP,'L'.$i=>$national_spend,'M'.$i=>$local,'N'.$i=>$localP,'O'.$i=>$local_spend);           
            foreach($resp_array as $key => $val){                      
                $objPHPExcel->setActiveSheetIndex($m)->SetCellValue($key, $val);
            }
            $i++;
            update_excel_progress($id, $rowNum++);
        }
    }else{
        $objPHPExcel->setActiveSheetIndex($m)->mergeCells('A'.$i.':O'.$i);
        $objPHPExcel->setActiveSheetIndex($m)->SetCellValue('A'.$i, "No records found.");
        $objPHPExcel->setActiveSheetIndex($m)->getStyle('A'.$i)->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
    }
    unset($rows);
   
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex($p);
     //For Programs
     $objDrawing1 = new PHPExcel_Worksheet_Drawing();
     $objDrawing1->setName('Logo');
     $objDrawing1->setDescription('Logo');
     $objDrawing1->setPath($logo);  //setOffsetY has no effect
     $objDrawing1->setCoordinates('A1');
     $objDrawing1->setHeight($imageHeight);
     $objDrawing1->setWidth($imageWidth);
     $objDrawing1->setOffsetX(20);
     $objPHPExcel->setActiveSheetIndex($p)->setTitle("Program");
     $objDrawing1->setWorksheet($objPHPExcel->setActiveSheetIndex($p));
     $objPHPExcel->setActiveSheetIndex($p)->getCell('B1')->setValue($objRichText);
     $objPHPExcel->setActiveSheetIndex($p)->getStyle('B1')->getAlignment()->setWrapText(true);
     $objPHPExcel->setActiveSheetIndex($p)->getRowDimension('1')->setRowHeight(69);
     $objPHPExcel->setActiveSheetIndex($p)->mergeCells('B1:J1');
     $objPHPExcel->setActiveSheetIndex($p)->getStyle("B1:J1")->applyFromArray($styleHeader);
     $objPHPExcel->setActiveSheetIndex($p)->getStyle("A".(2).":J".(2))->applyFromArray($styleArray, False);
     $header_array = array('A2'=>'Station Name', 'B2'=>'Program', 'C2'=>'Total Airings', 'D2'=>'Total Spend', 'E2'=>'National Airings', 'F2'=>'National %',  'G2'=>'National Spend ($)', 'H2'=>'DPI Airings', 'I2'=>'DPI %','J2'=>'DPI Spend ($)');

     foreach($header_array as $key => $val){
         $objPHPExcel->setActiveSheetIndex($p)->SetCellValue($key,$val);
     }
     $objPHPExcel->setActiveSheetIndex($p)->getRowDimension('2')->setRowHeight(24);
     $objPHPExcel->setActiveSheetIndex($p)->getStyle("A2:J2")->applyFromArray($styleSubHeader);
     $objPHPExcel->setActiveSheetIndex($p)->getColumnDimension("A")->setWidth(30);
     foreach(range('B','J') as $columnID) {
         $objPHPExcel->setActiveSheetIndex($p)->getColumnDimension($columnID)
             ->setAutoSize(true);
     }
     $result  =  array();
     $i=3;
     $rows = getResult($query[2]);
     $rows = reorderResultForRankingArrayForExcel($rows);
     $column_index_array = ['C','D','E','J','G','H'];
     setColumnNumberFormat($objPHPExcel , $column_index_array);
     if (!empty($rows)) {
         //arsort($resultArr);
         //echo "<pre>"; print_r($rows); exit;
         $rowNum = 1;
         foreach ($rows as $key => $row) {
             //$row = $result[$key];
             extract($row);
             $rosDay = substr($daypart, 0, -18);
             $rosTime = substr($daypart, -17);
             $objPHPExcel->setActiveSheetIndex($p)->getStyle("A".($i).":J".($i))->applyFromArray($styleArray, False);
             $resp_array = array('A'.$i=>$network_alias,'B'.$i=>$program, 'C'.$i=>$total_airings,'D'.$i=>$total_spend,'E'.$i=>$national_airings,'F'.$i=>$nationalP,'G'.$i=> $nat_spend,'H'.$i=>$local_airings,'I'.$i=>$localP,'J'.$i=>$loc_spend);           
             foreach($resp_array as $key => $val){
                 $objPHPExcel->setActiveSheetIndex($p)->SetCellValue($key, $val);
             }
             $i++;
             update_excel_progress($id, $rowNum++);
         }
     }else{
         $objPHPExcel->setActiveSheetIndex($p)->mergeCells('A'.$i.':J'.$i);
         $objPHPExcel->setActiveSheetIndex($p)->SetCellValue('A'.$i, "No records found.");
         $objPHPExcel->setActiveSheetIndex($p)->getStyle('A'.$i)->getAlignment()->applyFromArray(
             array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
         );
     }
     unset($rows);

    if(isset($n) && count($query) == 3 ){
        $objPHPExcel->setActiveSheetIndex($n)->setTitle("Network");
    }
    

    //$objPHPExcel->setActiveSheetIndex(0);    
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
    //$objWriter->setOffice2003Compatibility(true);//commented because it was creating problem with Copy and paste in excel PT##132219317
    $objWriter->save($newFilePath);
    return $newFilePath;
}

function _downloadShortFormAiring_detailExcel($sql, $day_type, $file_name, $user_id, $name, $date_range_str, $excel_values, $id = '') {
    set_time_limit(0);
    ini_set('memory_limit','8192M');
    $db = getConnection();
    $rowNum = 0;   
    $dir_name = createDir($user_id);
    $file_name = $dir_name.$file_name;
    
    $rows = getResult($sql);
    closeConnection();
     $insert_sql = "INSERT INTO export_log (value)VALUES('".THRESHOLD_FOR_PHPEXCEL."')";
     $stmt = $db->prepare($insert_sql);
     $stmt->execute();
     
     $insert_sql = "INSERT INTO export_log (value)VALUES('".count($rows)."')";
     $stmt = $db->prepare($insert_sql);
     $stmt->execute();

     $generic_dayparts = unserialize (GENERTIC_DAYPARTS);
    // show($generic_dayparts, 1);
    if (!empty($rows) && count($rows) > THRESHOLD_FOR_PHPEXCEL){
        $newFilePath        = $file_name;
        $style = (new StyleBuilder())->setFontBold()->build();
        $writer = WriterFactory::create(Type::CSV); // for XLSX files
        $writer->openToFile($newFilePath); // write data to a file or to a PHP stream

        $headersArr         = ['Ad ID', 'Station Code', 'Station Name', 'Creative', 'Play', 'Start Time', 'End Time', 'Start Date', 'End Date', 'Brand', 'Length', 'Break Type', 'Verified', 'TFN', 'URL', 'Promo', 'Thumbnail', 'Program', 'rosDay', 'rosTime', 'rosDaypart','Spend ($)','Dow'];
        $writer->addRowWithStyle($headersArr, $style);
    
        $rowNum = 1;    
         $insert_sql = "INSERT INTO export_log (value)VALUES('Before for loop')";
     $stmt = $db->prepare($insert_sql);
     $stmt->execute();
        foreach ($rows as $key => $row) {
            extract($row);
            $rosDaypart     = $generic_dayparts[$gen_daypart_id];
            $rosDay         = substr($daypart, 0, -18);
            $rosTime        = substr($daypart, -17);
            $startTime      = substr($start, -8);
            $endTime        = substr($end, -8);
            $startDate      = transformDate(substr($start, 0, -9));
            $endDate        = transformDate(substr($end, 0, -9));
            $startDate      = str_replace("-","/",$startDate);
            $endDate        = str_replace("-","/",$endDate);

            if ($verified != 1) {
                $tfn    = '';
                $url    = '';
                $promo  = '';
                $verified = 'No';
            } else {
                $verified = 'Yes';
            }
            $breakType = ($breakType == 'L') ? 'D' : $breakType;
            $play_url = createVideoLink($creative_id, $airing_id, $id);
            $thumb_url = createImageLink($creative_id, $airing_id, $id);
            $array = array(
                $airing_id,
                $network_code, 
                $network_alias,
                $creative_name,
                $play_url,
                $startTime,
                $endTime,
                $startDate,
                $endDate,
                $brand_name,
                $length,
                $breakType,
                $verified,
                $tfn,
                $url,
                $promo,
                $thumb_url,
                $program,
                $rosDay,
                $rosTime,
                $rosDaypart,
                $rate,
                $dow);
            $writer->addRow($array);
            update_excel_progress($id, $rowNum++);
        }
    
        $writer->close();
    } else {
        
        $newFilePath        = $file_name;
        $insert_sql = "INSERT INTO export_log (value)VALUES('else condition')";
     $stmt = $db->prepare($insert_sql);
     $stmt->execute();
     
        $styleHeader = array(
            'font'  => array(
                'size'  => 16,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                //'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
             'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                    'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                    'rotation'   => 90,
                    'startcolor' => array(
                        'argb' => 'B5B5B5'
                    ),
                    'endcolor'   => array(
                        'argb' => 'E0E0E0'
                    )
                )
        );

        $styleSubHeader = array(
            'font'  => array(
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 11,
                'bold'  => true,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => '202b39')
            )
        );

        $styleArray = array(
            'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
            )
        );

        $objPHPExcel = new PHPExcel();
        $phpColor = new PHPExcel_Style_Color();
        $phpColor->setRGB('0000FF');

        //Image Add
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $logo = IMAGE ; 
        $objDrawing->setPath($logo);  //setOffsetY has no effect
        $objDrawing->setCoordinates('A1');
        if(isset($logo)){
            $ImageDimension = getimagesize($logo);
            $imageWidth = $ImageDimension[0];
            $imageHeight = $ImageDimension[1];
        }
        $objDrawing->setHeight($imageHeight);
        $objDrawing->setWidth($imageWidth);
        $objDrawing->setOffsetX(20);

        $daytype_string = getDayType($excel_values['day_type']);
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText("  ".$name." - Airing Details Report\n");
        $objBold = $objRichText->createTextRun("    ".$date_range_str. $daytype_string);
        $objBold->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getCell('B1')->setValue($objRichText);

        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(69);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B1:R1');
        $objPHPExcel->getActiveSheet()->getStyle("B1:W1")->applyFromArray($styleHeader);
        $objPHPExcel->getActiveSheet()->getStyle("A".(2).":W".(2))->applyFromArray($styleArray, False);
        $header_array = array('A2'=>'Ad ID', 'B2'=>'Station Code', 'C2'=>'Station Name', 'D2'=>'Creative', 'E2'=>'Play', 'F2'=>'Start Time',  'G2'=>'End Time', 'H2'=>'Start Date', 'I2'=>'End Date', 'J2'=>'Brand', 'K2'=>'Length', 'L2'=>'Break Type', 'M2'=>'Verified', 'N2'=> 'TFN', 'O2'=>'URL', 'P2'=>'Promo', 'Q2'=>'Thumbnail' , 'R2'=>'Program', 'S2'=>'rosDay', 'T2'=>'rosTime', 'U2'=>'rosDaypart','V2'=>'Spend ($)', 'W2'=>'Dow');
        foreach($header_array as $key => $val){
            $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key,$val);
        }
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(24);
        $objPHPExcel->getActiveSheet()->getStyle("A2:W2")->applyFromArray($styleSubHeader);
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
        foreach(range('B','W') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        $i=3;    

        $column_index_array = ['V'];
        setColumnNumberFormat($objPHPExcel , $column_index_array);
        try{
        if (!empty($rows)){
            $rowNum = 1;    
            foreach ($rows as $key => $row) {
                extract($row);
                $breakType = ($breakType == 'L') ? 'D' : $breakType;
                $rosDaypart     = $generic_dayparts[$gen_daypart_id];
                $rosDay         = substr($daypart, 0, -18);
                $rosTime        = substr($daypart, -17);
                $startTime      = substr($start, -8);
                $endTime        = substr($end, -8);
                $startDate      = substr($start, 0, -9);
                $endDate        = substr($end, 0, -9);
                $startDate      = str_replace("-","/",$startDate);
                $endDate        = str_replace("-","/",$endDate);

                if ($verified != 1) {
                    $tfn    = '';
                    $url    = '';
                    $promo  = '';
                    $verified = 'No';
                } else {
                    $verified = 'Yes';
                }
                $objPHPExcel->getActiveSheet()->getStyle("A".($i).":W".($i))->applyFromArray($styleArray, False);
                $resp_array = array('A'.$i=>$airing_id,'B'.$i=>$network_code, 'C'.$i=>$network_alias,'D'.$i=>$creative_name,'E'.$i=>'dUrl','F'.$i=>$startTime,'G'.$i=>$endTime,'H'.$i=>$startDate,'I'.$i=>$endDate,'J'.$i=>$brand_name,'K'.$i=>$length,'L'.$i=>$breakType,'M'.$i=>$verified,'N'.$i=>$tfn,'O'.$i=>$url,'P'.$i=>$promo,'Q'.$i=>'thumb_url','R'.$i=>$program,'S'.$i=>$rosDay,'T'.$i=>$rosTime,'U'.$i=>$rosDaypart,'V'.$i=>$rate ,'W'.$i=>$dow);
                foreach($resp_array as $key => $val){
                    if($val == 'dUrl'){
                        $url = createVideoLink($creative_id, $airing_id, $id);
                        $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key, 'Play');
                        $objPHPExcel->getActiveSheet()->getStyle($key)->getFont()->setColor( $phpColor );
                        $objPHPExcel->setActiveSheetIndex(0)->getCell($key)->getHyperlink('Play')->setUrl($url);
                        /*
                        $objPHPExcel->setActiveSheetIndex(0)->getStyle($key)->getFont()->setColor( $phpColor );
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($key,'=HYPERLINK("'.$url.'", "Play")' );

                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($key, $url);*/
                    } elseif ($val == 'thumb_url') {
                        $url = createImageLink($creative_id, $airing_id, $id);
                        $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key, 'View');
                        $objPHPExcel->getActiveSheet()->getStyle($key)->getFont()->setColor( $phpColor );
                        $objPHPExcel->setActiveSheetIndex(0)->getCell($key)->getHyperlink('View')->setUrl($url);
                        /*
                        $objPHPExcel->setActiveSheetIndex(0)->getStyle($key)->getFont()->setColor( $phpColor );
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($key,'=HYPERLINK("'.$url.'", "View")' );
                        */
                        /*$objPHPExcel->setActiveSheetIndex(0)->setCellValue($key, $url);*/
                    } else if((preg_match('/(H)/', $key)) || (preg_match('/(I)/', $key))){
                        if(preg_match('/(H)/', $key)) {
                            $date = new DateTime($startDate);
                       } else {
                            $date = new DateTime($endDate);
                       }
                        $objPHPExcel->getActiveSheet()->SetCellValue($key, PHPExcel_Shared_Date::PHPToExcel( $date ));
                        $objPHPExcel->getActiveSheet()->getStyle($key)->getNumberFormat()->setFormatCode('m/d/yyyy');
                    } else {            
                        $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key, $val);
                    }
                    $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(15);
                }
                $i++;
                update_excel_progress($id, $rowNum++);
            }
        }else{
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$i.':W'.$i);
            $objPHPExcel->setActiveSheetIndex(0)->SetCellValue('A'.$i, "No records found.");
            $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );
            update_excel_progress($id, $rowNum++);
        }
    }catch(Exception $e){
        $insert_sql = "INSERT INTO export_log (value)VALUES($e->getMessage())";
    }
     $insert_sql = "INSERT INTO export_log (value)VALUES('before save')";
     $stmt = $db->prepare($insert_sql);
     $stmt->execute();
     
        unset($rows);
        $objDrawing->setWorksheet($objPHPExcel->setActiveSheetIndex(0));
        $objPHPExcel->getActiveSheet()->setTitle("Short Form Airing Detail");
        $objPHPExcel->setActiveSheetIndex(0);    
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
        //$objWriter->setOffice2003Compatibility(true);//commented because it was creating problem with Copy and paste in excel PT##132219317
        $objWriter->save($newFilePath);
        
        $insert_sql = "INSERT INTO export_log (value)VALUES('$newFilePath')";
        $stmt = $db->prepare($insert_sql);
        $stmt->execute();
    }
    return $newFilePath;
}



function _downloadLongFormSummaryExcel($sql, $day_type, $file_name, $user_id, $name, $date_range_str, $excel_values, $id = '') {
    set_time_limit(0);
    ini_set('memory_limit','8192M');
    $db = getConnection();

    require_once(dirname(__FILE__).'/PHPExcel_1.8.0_doc/Classes/PHPExcel.php');
    
    $styleHeader = array(
        'font'  => array(
            'size'  => 16,            
            'name'  => 'Calibri'
        ),
        'alignment' => array(
            //'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
         'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
                'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                'rotation'   => 90,
                'startcolor' => array(
                    'argb' => 'B5B5B5'
                ),
                'endcolor'   => array(
                    'argb' => 'E0E0E0'
                )
            )
    );
    
    $styleSubHeader = array(
        'font'  => array(
            'color' => array('rgb' => 'FFFFFF'),
            'size'  => 11,
            'bold'  => true,
            'name'  => 'Calibri'
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '202b39')
        )
    );

    $styleArray = array(
        'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
        )
    );

    $objPHPExcel = new PHPExcel();    
    $dir_name = createDir($user_id);
    $file_name = $dir_name.$file_name;
    $newFilePath        = $file_name;

    //Image Add
    $logo = IMAGE ; 
    if(isset($logo)){
        $ImageDimension = getimagesize($logo);
        $imageWidth = $ImageDimension[0];
        $imageHeight = $ImageDimension[1];
    }
    $daytype_string = getDayType($excel_values['day_type']);
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText("  ".$name." - Summary Report\n");
    $objBold = $objRichText->createTextRun("    ".$date_range_str. $daytype_string);
    $objBold->getFont()->setBold(true);

    $query = explode('===',$sql);
    $m = 0;
    $lastCol = 'E';
    if(count($query) == 3){
        $n = 0; $m = 1; $p = 2;
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');    
        $objDrawing->setPath($logo);  //setOffsetY has no effect
        $objDrawing->setCoordinates('A1');    
        $objDrawing->setHeight($imageHeight);
        $objDrawing->setWidth($imageWidth);
        $objDrawing->setOffsetX(20);
        //For networks
        $objPHPExcel->setActiveSheetIndex($n)->getCell('B1')->setValue($objRichText);    
        $objPHPExcel->setActiveSheetIndex($n)->getStyle('B1')->getAlignment()->setWrapText(true);    
        $objPHPExcel->setActiveSheetIndex($n)->getRowDimension('1')->setRowHeight(69);
        $objPHPExcel->setActiveSheetIndex($n)->mergeCells('B1:'.$lastCol.'1');
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("B1:".$lastCol."1")->applyFromArray($styleHeader);
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("A".(2).":".$lastCol.(2))->applyFromArray($styleArray, False);
        $header_array = array('A2'=>'Station Code', 'B2'=>'Station Name', 'C2'=>'Creatives', 'D2'=>'Airings','E2'=>'Spend ($)');
        foreach($header_array as $key => $val){
            $objPHPExcel->setActiveSheetIndex($n)->SetCellValue($key,$val);
        }
        $objPHPExcel->setActiveSheetIndex($n)->getRowDimension('2')->setRowHeight(24);
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("A2:".$lastCol."2")->applyFromArray($styleSubHeader);
        $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension("A")->setWidth(30);
        foreach(range('B',$lastCol) as $columnID) {
            //$objPHPExcel->setActiveSheetIndex($n)->getColumnDimension($columnID)->setAutoSize(true);
            if($columnID == 'B')
                $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension($columnID)->setWidth("70");
            if($columnID == 'C' || $columnID == 'D' || $columnID == 'E')
                $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension($columnID)->setWidth("30");


        }
        $i=3;
        $rows = getResult($query[1]); 
        $rows = reorderResultForRankingArrayForExcel($rows); 
        $final_array = array();
        $net_array = array();   

        $column_index_array = ['D','E'];
        setColumnNumberFormat($objPHPExcel , $column_index_array);

        foreach ($rows as $key => $value) {   
            if(!in_array($value['network_code'],$net_array)){
                array_push($net_array, $value['network_code']);
                $final_array[$value['network_code']]['network_code'] = $value['network_code'];
                $final_array[$value['network_code']]['network_alias'] = $value['network_alias'];
                $final_array[$value['network_code']]['ccount'] = $value['ccount'];
                $final_array[$value['network_code']]['airings_count'] = $value['count'];
                $final_array[$value['network_code']]['total_spend'] = $value['total_spend'];
            }else{
                $final_array[$value['network_code']]['ccount'] = $final_array[$value['network_code']]['ccount'] + $value['ccount'];
            }
        }  

        /*foreach($final_array as $c=>$key) {
            $sort_numcie[] = $key['ccount'];
        }
        array_multisort($sort_numcie, SORT_DESC, $final_array);*/
        if (!empty($rows)) {
            $rows = $final_array;
            //arsort($resultArr);
            $rowNum = 1;
            foreach ($rows as $key => $row) {
               // $row = $result[$key];
                extract($row);
                $objPHPExcel->setActiveSheetIndex($n)->getStyle("A".($i).":".$lastCol.($i))->applyFromArray($styleArray, False);
                $resp_array = array('A'.$i=>$network_code,'B'.$i=>$network_alias, 'C'.$i=>$ccount, 'D'.$i=>$airings_count,'E'.$i =>$total_spend);
                foreach($resp_array as $key => $val){                      
                    $objPHPExcel->setActiveSheetIndex($n)->SetCellValue($key, $val);
                }
                $i++;
                update_excel_progress($id, $rowNum++);
            }
        }else{
            $objPHPExcel->setActiveSheetIndex($n)->mergeCells('A'.$i.':D'.$i);
            $objPHPExcel->setActiveSheetIndex($n)->SetCellValue('A'.$i, "No records found.");
            $objPHPExcel->setActiveSheetIndex($n)->getStyle('A'.$i)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );
        }
        unset($rows);
        $objPHPExcel->setActiveSheetIndex($n)->setTitle("Network");
        $objDrawing->setWorksheet($objPHPExcel->setActiveSheetIndex($n));
    }
    
    //For Creatives  
    $lastCreativeCol = 'G';
    $objDrawing1 = new PHPExcel_Worksheet_Drawing();
    $objDrawing1->setName('Logo');
    $objDrawing1->setDescription('Logo');     
    $objDrawing1->setPath($logo);  //setOffsetY has no effect
    $objDrawing1->setCoordinates('A1');    
    $objDrawing1->setHeight($imageHeight);
    $objDrawing1->setWidth($imageWidth);
    $objDrawing1->setOffsetX(20);

    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex($m);        
    $objPHPExcel->setActiveSheetIndex($m)->setTitle("Creative");
    $objDrawing1->setWorksheet($objPHPExcel->setActiveSheetIndex($m));

        
    $objPHPExcel->setActiveSheetIndex($m)->getCell('B1')->setValue($objRichText);    
    $objPHPExcel->setActiveSheetIndex($m)->getStyle('B1')->getAlignment()->setWrapText(true);    
    $objPHPExcel->setActiveSheetIndex($m)->getRowDimension('1')->setRowHeight(69);
    $objPHPExcel->setActiveSheetIndex($m)->mergeCells('B1:'.$lastCreativeCol.'1');
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("B1:".$lastCreativeCol."1")->applyFromArray($styleHeader);
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("A".(2).":".$lastCreativeCol.(2))->applyFromArray($styleArray, False);    
    $header_array = array('A2'=>'Station Code', 'B2'=>'Station Name', 'C2'=>'Creative', 'D2'=>'Brand', 'E2'=>'Length', 'F2'=>'Airings','G2'=>'Spend ($)');
    foreach($header_array as $key => $val){
        $objPHPExcel->setActiveSheetIndex($m)->SetCellValue($key,$val);
    }
    $objPHPExcel->setActiveSheetIndex($m)->getRowDimension('2')->setRowHeight(24);
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("A2:".$lastCreativeCol."2")->applyFromArray($styleSubHeader);
    $objPHPExcel->setActiveSheetIndex($m)->getColumnDimension("A")->setWidth(30);
    foreach(range('B',$lastCreativeCol) as $columnID) {
        $objPHPExcel->setActiveSheetIndex($m)->getColumnDimension($columnID)
            ->setAutoSize(true);
    }
    $i=3;
    $rows  = getResult($query[0]);    
    $rows  = reorderResultForRankingArrayForExcel($rows); 
    /*foreach ($rows as $key => $value) {
        $new_key = $value['concat'];        
        $result[$new_key] = $value;
        $resultArr[$new_key] = $value['count'];
    } */

    $column_index_array = ['G','F'];
    setColumnNumberFormat($objPHPExcel , $column_index_array);

    if (!empty($rows)){
        //arsort($resultArr);
        $rowNum = 1;
        foreach ($rows as $key => $row) {
           // $row = $result[$key];
            extract($row);
            $objPHPExcel->setActiveSheetIndex($m)->getStyle("A".($i).":".$lastCreativeCol.($i))->applyFromArray($styleArray, False);
            $resp_array = array('A'.$i=>$network_code,'B'.$i=>$network_alias, 'C'.$i=>$creative_name,'D'.$i=>$brand_name,'E'.$i=>$length,'F'.$i=>$count,'G'.$i => $total_spend);
            foreach($resp_array as $key => $val){                      
                $objPHPExcel->setActiveSheetIndex($m)->SetCellValue($key, $val);
            }
            $i++;
            update_excel_progress($id, $rowNum++);
        }
    }else{
        $objPHPExcel->setActiveSheetIndex($m)->mergeCells('A'.$i.':'.$lastCreativeCol.$i);
        $objPHPExcel->setActiveSheetIndex($m)->SetCellValue('A'.$i, "No records found.");
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
    }
    unset($rows);
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex($p);
      //For Programs
      $objDrawing1 = new PHPExcel_Worksheet_Drawing();
      $objDrawing1->setName('Logo');
      $objDrawing1->setDescription('Logo');
      $objDrawing1->setPath($logo);  //setOffsetY has no effect
      $objDrawing1->setCoordinates('A1');
      $objDrawing1->setHeight($imageHeight);
      $objDrawing1->setWidth($imageWidth);
      $objDrawing1->setOffsetX(20);
      $objPHPExcel->setActiveSheetIndex($p)->setTitle("Program");
      $objDrawing1->setWorksheet($objPHPExcel->setActiveSheetIndex($p));
      $objPHPExcel->setActiveSheetIndex($p)->getCell('B1')->setValue($objRichText);
      $objPHPExcel->setActiveSheetIndex($p)->getStyle('B1')->getAlignment()->setWrapText(true);
      $objPHPExcel->setActiveSheetIndex($p)->getRowDimension('1')->setRowHeight(69);
      $objPHPExcel->setActiveSheetIndex($p)->mergeCells('B1:D1');
      $objPHPExcel->setActiveSheetIndex($p)->getStyle("B1:D1")->applyFromArray($styleHeader);
      $objPHPExcel->setActiveSheetIndex($p)->getStyle("A".(2).":D".(2))->applyFromArray($styleArray, False);
      $header_array = array('A2'=>'Station Name', 'B2'=>'Program', 'C2'=>'Airings', 'D2'=>'Spend ($)');
 
      foreach($header_array as $key => $val){
          $objPHPExcel->setActiveSheetIndex($p)->SetCellValue($key,$val);
      }
      $objPHPExcel->setActiveSheetIndex($p)->getRowDimension('2')->setRowHeight(24);
      $objPHPExcel->setActiveSheetIndex($p)->getStyle("A2:D2")->applyFromArray($styleSubHeader);
      $objPHPExcel->setActiveSheetIndex($p)->getColumnDimension("A")->setWidth(30);
      foreach(range('B','D') as $columnID) {
          $objPHPExcel->setActiveSheetIndex($p)->getColumnDimension($columnID)
              ->setAutoSize(true);
      }
      $result  =  array();
      $i=3;
      $rows = getResult($query[2]);
      $rows = reorderResultForRankingArrayForExcel($rows);
      $column_index_array = ['C','D'];
      setColumnNumberFormat($objPHPExcel , $column_index_array);
      if (!empty($rows)) {
          //arsort($resultArr);
          //echo "<pre>"; print_r($rows); exit;
          $rowNum = 1;
          foreach ($rows as $key => $row) {
              //$row = $result[$key];
              extract($row);
              $rosDay = substr($daypart, 0, -18);
              $rosTime = substr($daypart, -17);
              $objPHPExcel->setActiveSheetIndex($p)->getStyle("A".($i).":D".($i))->applyFromArray($styleArray, False);
              $resp_array = array('A'.$i=>$network_alias,'B'.$i=>$program, 'C'.$i=>$total_airings,'D'.$i=>$total_spend);           
              foreach($resp_array as $key => $val){
                  $objPHPExcel->setActiveSheetIndex($p)->SetCellValue($key, $val);
              }
              $i++;
              update_excel_progress($id, $rowNum++);
          }
      }else{
          $objPHPExcel->setActiveSheetIndex($p)->mergeCells('A'.$i.':D'.$i);
          $objPHPExcel->setActiveSheetIndex($p)->SetCellValue('A'.$i, "No records found.");
          $objPHPExcel->setActiveSheetIndex($p)->getStyle('A'.$i)->getAlignment()->applyFromArray(
              array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
          );
      }
      unset($rows);
 
    if(isset($n) && count($query) == 3 ){
        $objPHPExcel->setActiveSheetIndex($n)->setTitle("Network");
    }
    
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
    //$objWriter->setOffice2003Compatibility(true);//commented because it was creating problem with Copy and paste in excel PT##132219317
    $objWriter->save($newFilePath);
    return $newFilePath;
}

function _downloadLongFormSummaryExcel_withLengthColumn($sql, $day_type, $file_name, $user_id, $name, $date_range_str, $excel_values, $id = '') {
    set_time_limit(0);
    ini_set('memory_limit','8192M');
    $db = getConnection();

    require_once(dirname(__FILE__).'/PHPExcel_1.8.0_doc/Classes/PHPExcel.php');
    
    $styleHeader = array(
        'font'  => array(
            'size'  => 16,            
            'name'  => 'Calibri'
        ),
        'alignment' => array(
            //'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
         'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
                'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                'rotation'   => 90,
                'startcolor' => array(
                    'argb' => 'B5B5B5'
                ),
                'endcolor'   => array(
                    'argb' => 'E0E0E0'
                )
            )
    );
    
    $styleSubHeader = array(
        'font'  => array(
            'color' => array('rgb' => 'FFFFFF'),
            'size'  => 11,
            'bold'  => true,
            'name'  => 'Calibri'
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array('rgb' => '202b39')
        )
    );

    $styleArray = array(
        'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
        )
    );

    $objPHPExcel = new PHPExcel();    
    $dir_name = createDir($user_id);
    $file_name = $dir_name.$file_name;
    $newFilePath        = $file_name;

    //Image Add
    $logo = IMAGE ; 
    if(isset($logo)){
        $ImageDimension = getimagesize($logo);
        $imageWidth = $ImageDimension[0];
        $imageHeight = $ImageDimension[1];
    }
    $daytype_string = getDayType($excel_values['day_type']);
    $objRichText = new PHPExcel_RichText();
    $objRichText->createText("  ".$name." - Summary Report\n");
    $objBold = $objRichText->createTextRun("    ".$date_range_str. $daytype_string);
    $objBold->getFont()->setBold(true);

    $query = explode('===',$sql);
    $m = 0;

    $lastCol = 'F';
    if(count($query) == 2){
        $n = 0; $m = 1;
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');    
        $objDrawing->setPath($logo);  //setOffsetY has no effect
        $objDrawing->setCoordinates('A1');    
        $objDrawing->setHeight($imageHeight);
        $objDrawing->setWidth($imageWidth);
        $objDrawing->setOffsetX(20);
        //For networks
        $objPHPExcel->setActiveSheetIndex($n)->getCell('B1')->setValue($objRichText);    
        $objPHPExcel->setActiveSheetIndex($n)->getStyle('B1')->getAlignment()->setWrapText(true);    
        $objPHPExcel->setActiveSheetIndex($n)->getRowDimension('1')->setRowHeight(69);
        $objPHPExcel->setActiveSheetIndex($n)->mergeCells('B1:'.$lastCol.'1');
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("B1:".$lastCol."1")->applyFromArray($styleHeader);
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("A".(2).":".$lastCol.(2))->applyFromArray($styleArray, False);
        $header_array = array('A2'=>'Station Code', 'B2'=>'Station Name', 'C2'=>'Length', 'D2'=>'Creatives', 'E2'=>'Airings','F2'=>'Spend ($)');
        foreach($header_array as $key => $val){
            $objPHPExcel->setActiveSheetIndex($n)->SetCellValue($key,$val);
        }
        $objPHPExcel->setActiveSheetIndex($n)->getRowDimension('2')->setRowHeight(24);
        $objPHPExcel->setActiveSheetIndex($n)->getStyle("A2:".$lastCol."2")->applyFromArray($styleSubHeader);
        $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension("A")->setWidth(30);
        foreach(range('B',$lastCol) as $columnID) {
            //$objPHPExcel->setActiveSheetIndex($n)->getColumnDimension($columnID)->setAutoSize(true);
            if($columnID == 'B')
                $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension($columnID)->setWidth("70");
            if($columnID == 'C' || $columnID == 'D' || $columnID == 'E' || $columnID == 'F')
                $objPHPExcel->setActiveSheetIndex($n)->getColumnDimension($columnID)->setWidth("30");


        }
        $i=3;
        $rows = getResult($query[1]); 
        $rows = reorderResultForRankingArrayForExcel($rows); 
        $final_array = array();
        $net_array = array();   
        foreach ($rows as $key => $value) {   
            if(!in_array($value['network_code'],$net_array)){
                array_push($net_array, $value['network_code']);
                $final_array[$value['network_code']]['network_code'] = $value['network_code'];
                $final_array[$value['network_code']]['network_alias'] = $value['network_alias'];
                $final_array[$value['network_code']]['length'] = $value['length'];
                $final_array[$value['network_code']]['ccount'] = $value['ccount'];
                $final_array[$value['network_code']]['airings_count'] = $value['count'];
                $final_array[$value['network_code']]['total_spend'] = $value['total_spend'];
            }else{
                $final_array[$value['network_code']]['ccount'] = $final_array[$value['network_code']]['ccount'] + $value['ccount'];
            }
        }  

        /*foreach($final_array as $c=>$key) {
            $sort_numcie[] = $key['ccount'];
        }
        array_multisort($sort_numcie, SORT_DESC, $final_array);*/
        if (!empty($rows)) {
            $rows = $final_array;
            //arsort($resultArr);
            $rowNum = 1;
            foreach ($rows as $key => $row) {
               // $row = $result[$key];
                extract($row);
                $objPHPExcel->setActiveSheetIndex($n)->getStyle("A".($i).":".$lastCol.($i))->applyFromArray($styleArray, False);
                $resp_array = array('A'.$i=>$network_code,'B'.$i=>$network_alias, 'C'.$i=>$length, 'D'.$i=>$ccount, 'E'.$i=>$airings_count,'F'.$i=>$total_spend);
                foreach($resp_array as $key => $val){                      
                    $objPHPExcel->setActiveSheetIndex($n)->SetCellValue($key, $val);
                }
                $i++;
                update_excel_progress($id, $rowNum++);
            }
        }else{
            $objPHPExcel->setActiveSheetIndex($n)->mergeCells('A'.$i.':'.$lastCol.$i);
            $objPHPExcel->setActiveSheetIndex($n)->SetCellValue('A'.$i, "No records found.");
            $objPHPExcel->setActiveSheetIndex($n)->getStyle('A'.$i)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );
        }
        unset($rows);
        $objPHPExcel->setActiveSheetIndex($n)->setTitle("Network");
        $objDrawing->setWorksheet($objPHPExcel->setActiveSheetIndex($n));
    }
    
    //For Creatives  
    $lastCreativeCol = 'G';
    $objDrawing1 = new PHPExcel_Worksheet_Drawing();
    $objDrawing1->setName('Logo');
    $objDrawing1->setDescription('Logo');     
    $objDrawing1->setPath($logo);  //setOffsetY has no effect
    $objDrawing1->setCoordinates('A1');    
    $objDrawing1->setHeight($imageHeight);
    $objDrawing1->setWidth($imageWidth);
    $objDrawing1->setOffsetX(20);

    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex($m);        
    $objPHPExcel->setActiveSheetIndex($m)->setTitle("Creative");
    $objDrawing1->setWorksheet($objPHPExcel->setActiveSheetIndex($m));
        
    $objPHPExcel->setActiveSheetIndex($m)->getCell('B1')->setValue($objRichText);    
    $objPHPExcel->setActiveSheetIndex($m)->getStyle('B1')->getAlignment()->setWrapText(true);    
    $objPHPExcel->setActiveSheetIndex($m)->getRowDimension('1')->setRowHeight(69);
    $objPHPExcel->setActiveSheetIndex($m)->mergeCells('B1:'.$lastCreativeCol.'1');
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("B1:".$lastCreativeCol."1")->applyFromArray($styleHeader);
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("A".(2).":".$lastCreativeCol.(2))->applyFromArray($styleArray, False);    
    $header_array = array('A2'=>'Station Code', 'B2'=>'Station Name', 'C2'=>'Creative', 'D2'=>'Brand', 'E2'=>'Length', 'F2'=>'Airings','G2'=>'Spend ($)');
    foreach($header_array as $key => $val){
        $objPHPExcel->setActiveSheetIndex($m)->SetCellValue($key,$val);
    }
    $objPHPExcel->setActiveSheetIndex($m)->getRowDimension('2')->setRowHeight(24);
    $objPHPExcel->setActiveSheetIndex($m)->getStyle("A2:".$lastCreativeCol."2")->applyFromArray($styleSubHeader);
    $objPHPExcel->setActiveSheetIndex($m)->getColumnDimension("A")->setWidth(30);
    foreach(range('B',$lastCreativeCol) as $columnID) {
        $objPHPExcel->setActiveSheetIndex($m)->getColumnDimension($columnID)
            ->setAutoSize(true);
    }
    $i=3;
    $rows  = getResult($query[0]);    
    $rows  = reorderResultForRankingArrayForExcel($rows); 
    /*foreach ($rows as $key => $value) {
        $new_key = $value['concat'];        
        $result[$new_key] = $value;
        $resultArr[$new_key] = $value['count'];
    } */
    if (!empty($rows)){
        //arsort($resultArr);
        $rowNum = 1;
        foreach ($rows as $key => $row) {
           // $row = $result[$key];
            extract($row);
            $objPHPExcel->setActiveSheetIndex($m)->getStyle("A".($i).":".$lastCreativeCol.($i))->applyFromArray($styleArray, False);
            $resp_array = array('A'.$i=>$network_code,'B'.$i=>$network_alias, 'C'.$i=>$creative_name,'D'.$i=>$brand_name,'E'.$i=>$length,'F'.$i=>$count,'G'.$i =>$total_spend);
            foreach($resp_array as $key => $val){                      
                $objPHPExcel->setActiveSheetIndex($m)->SetCellValue($key, $val);
            }
            $i++;
            update_excel_progress($id, $rowNum++);
        }
    }else{
        $objPHPExcel->setActiveSheetIndex($m)->mergeCells('A'.$i.':'.$lastCreativeCol.$i);
        $objPHPExcel->setActiveSheetIndex($m)->SetCellValue('A'.$i, "No records found.");
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->applyFromArray(
            array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
    }
    unset($rows);
    
    if(isset($n) && count($query) == 2 ){
        $objPHPExcel->setActiveSheetIndex($n)->setTitle("Network");
    }
    
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
    //$objWriter->setOffice2003Compatibility(true);//commented because it was creating problem with Copy and paste in excel PT##132219317
    $objWriter->save($newFilePath);
    return $newFilePath;
}

function createVideoLink($creative_id, $airing_id, $excel_id, $no_of_days = EXCEL_VIDEO_EXPIRY_DAYS) {
    $query_string = base64_encode("creative_id=$creative_id&airing_id=$airing_id&date=".time()."&excel_id=".$excel_id);
    return 'http://'.HOST."/drmetrix/video/{$query_string}?video=1";
}

function createImageLink($creative_id, $airing_id, $excel_id, $no_of_days = EXCEL_VIDEO_EXPIRY_DAYS) {
    $query_string = base64_encode("creative_id=$creative_id&airing_id=$airing_id&date=".time()."&excel_id=".$excel_id."&page=Thumbnail");
    return 'http://'.HOST."/drmetrix/video/{$query_string}?video=2";
}

function _downloadLongFormAiring_detailExcel($sql, $day_type, $file_name, $user_id, $name, $date_range_str, $excel_values, $id = '') {
    set_time_limit(0);
    ini_set('memory_limit','8192M');
    
    $dir_name           = createDir($user_id);
    $file_name          = $dir_name.$file_name;
    $generic_dayparts   = unserialize (GENERTIC_DAYPARTS);

    $rows = getResult($sql);
    $lastCol = 'V';
    if (!empty($rows) && count($rows) > THRESHOLD_FOR_PHPEXCEL){
        $newFilePath        = $file_name;
        $style = (new StyleBuilder())->setFontBold()->build();
        $writer = WriterFactory::create(Type::CSV); // for XLSX files
        $writer->openToFile($newFilePath); // write data to a file or to a PHP stream

        $headersArr         = 
        ['Ad ID',
        'Station Code',
        'Station Name',
        'Creative', 
        'Play',
        'Start Time',
        'End Time',
        'Start Date',
        'End Date',
        'Brand',
        'Length',
        'Verified',
        'TFN',
        'URL',
        'Promo',
        'Thumbnail',
        'Program',
        'rosDay',
        'rosTime',
        'rosDaypart',
        'Spend ($)',
        'Dow'];
        $writer->addRowWithStyle($headersArr, $style);
    
        $rowNum = 1;    

        foreach ($rows as $key => $row) {
            extract($row);
            $rosDaypart     = $generic_dayparts[$gen_daypart_id];
            $rosDay         = substr($daypart, 0, -18);
            $rosTime        = substr($daypart, -17);
            $startTime      = substr($start, -8);
            $endTime        = substr($end, -8);
            $startDate      = transformDate(substr($start, 0, -9));
            $endDate        = transformDate(substr($end, 0, -9));
            $startDate      = str_replace("-","/",$startDate);
            $endDate        = str_replace("-","/",$endDate);

            if ($verified != 1) {
                $tfn    = '';
                $url    = '';
                $promo  = ''; 
                $verified = 'No';
            } else {
                $verified = 'Yes';
            }
            
            $play_url   = createVideoLink($creative_id, $airing_id, $id);
            $thumb_url  = createImageLink($creative_id, $airing_id, $id);
            $array = array(
                $airing_id,
                $network_code, 
                $network_alias,
                $creative_name,
                $play_url,
                $startTime,
                $endTime,
                $startDate,
                $endDate,
                $brand_name,
                $length,
                $verified,
                $tfn,
                $url,
                $promo,
                $thumb_url,
                $program,
                $rosDay,
                $rosTime,
                $rosDaypart,
                $dow);
            $writer->addRow($array);
            update_excel_progress($id, $rowNum++);
        }
    
        $writer->close();
    } else {    
        $newFilePath    = $file_name;
        $styleHeader = array(
            'font'  => array(
                'size'  => 16,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                //'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
             'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                    'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                    'rotation'   => 90,
                    'startcolor' => array(
                        'argb' => 'B5B5B5'
                    ),
                    'endcolor'   => array(
                        'argb' => 'E0E0E0'
                    )
                )
        );

        $styleSubHeader = array(
            'font'  => array(
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 11,
                'bold'  => true,
                'name'  => 'Calibri'
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => '202b39')
            )
        );

        $styleArray = array(
            'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
            )
        );

        $objPHPExcel = new PHPExcel();
        $phpColor = new PHPExcel_Style_Color();
        $phpColor->setRGB('0000FF');

        //Image Add
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Logo');
        $objDrawing->setDescription('Logo');
        $logo = IMAGE ; 
        $objDrawing->setPath($logo);  //setOffsetY has no effect
        $objDrawing->setCoordinates('A1');
        if(isset($logo)){
            $ImageDimension = getimagesize($logo);
            $imageWidth = $ImageDimension[0];
            $imageHeight = $ImageDimension[1];
        }
        $objDrawing->setHeight($imageHeight);
        $objDrawing->setWidth($imageWidth);
        $objDrawing->setOffsetX(20);

        $daytype_string = getDayType($excel_values['day_type']);
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText("  ".$name." - Airing Details Report\n");
        $objBold = $objRichText->createTextRun("    ".$date_range_str. $daytype_string);
        $objBold->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getCell('B1')->setValue($objRichText);

        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setWrapText(true);

        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(69);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('B1:'.$lastCol.'1');
        $objPHPExcel->getActiveSheet()->getStyle("B1:".$lastCol."1")->applyFromArray($styleHeader);
        $objPHPExcel->getActiveSheet()->getStyle("A".(2).":".$lastCol.(2))->applyFromArray($styleArray, False);

        $header_array = array('A2'=>'Ad ID', 'B2'=>'Station Code', 'C2'=>'Station Name', 'D2'=>'Creative', 'E2'=>'Play', 'F2'=>'Start Time',  'G2'=>'End Time', 'H2'=>'Start Date', 'I2'=>'End Date', 'J2'=>'Brand', 'K2'=>'Length', 'L2'=>'Verified', 'M2'=>'TFN', 'N2'=> 'URL', 'O2'=>'Promo', 'P2'=>'Thumbnail', 'Q2'=>'Program' , 'R2'=>'rosDay', 'S2'=>'rosTime', 'T2'=>'rosDaypart','U2'=>'Spend ($)', 'V2'=>'Dow');
        foreach($header_array as $key => $val){
            $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key,$val);
        }   
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(24);
        $objPHPExcel->getActiveSheet()->getStyle("A2:".$lastCol."2")->applyFromArray($styleSubHeader);
        $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
        foreach(range('B',$lastCol) as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $i=3;
        $column_index_array = ['U'];
        setColumnNumberFormat($objPHPExcel , $column_index_array);

        if (!empty($rows)) {
            $rowNum = 1;
            foreach ($rows as $key => $row) {
                extract($row);
                $rosDaypart     = $generic_dayparts[$gen_daypart_id];
                $rosDay         = substr($daypart, 0, -18);
                $rosTime        = substr($daypart, -17);
                $startTime      = substr($start, -8);
                $endTime        = substr($end, -8);
                $startDate      = substr($start, 0, -9);
                $endDate        = substr($end, 0, -9);
                $startDate      = str_replace("-","/",$startDate);
                $endDate        = str_replace("-","/",$endDate);

                if ($verified != 1) {
                    $tfn    = '';
                    $url    = '';
                    $promo  = ''; 
                    $verified = 'No';
                } else {
                    $verified = 'Yes';
                }
                $objPHPExcel->getActiveSheet()->getStyle("A".($i).":".$lastCol.($i))->applyFromArray($styleArray, False);
                $resp_array = array('A'.$i=>$airing_id,'B'.$i=>$network_code, 'C'.$i=>$network_alias,'D'.$i=>$creative_name,'E'.$i=>'dUrl','F'.$i=>$startTime,'G'.$i=>$endTime,'H'.$i=>$startDate,'I'.$i=>$endDate,'J'.$i=>$brand_name,'K'.$i=>$length,'L'.$i=>$verified,'M'.$i=>$tfn,'N'.$i=>$url,'O'.$i=>$promo,'P'.$i=>'thumb_url','Q'.$i=>$program,'R'.$i=>$rosDay,'S'.$i=>$rosTime,'T'.$i=>$rosDaypart,'U'.$i=>$rate,'V'.$i=>$dow);
                foreach($resp_array as $key => $val){
                    if($val == 'dUrl'){
                            $url = createVideoLink($creative_id, $airing_id, $id);
                            $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key, 'Play');
                            $objPHPExcel->getActiveSheet()->getStyle($key)->getFont()->setColor( $phpColor );
                            $objPHPExcel->setActiveSheetIndex(0)->getCell($key)->getHyperlink('Play')->setUrl($url);
                            /*$objPHPExcel->setActiveSheetIndex(0)->getStyle($key)->getFont()->setColor( $phpColor );
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($key,'=HYPERLINK("'.$url.'", "Play")' );*/
                        } else if((preg_match('/(H)/', $key)) || (preg_match('/(I)/', $key))){
                           if(preg_match('/(H)/', $key)) {
                                $date = new DateTime($startDate);
                           } else {
                                $date = new DateTime($endDate);
                           }
                            $objPHPExcel->getActiveSheet()->SetCellValue($key, PHPExcel_Shared_Date::PHPToExcel( $date ));
                            $objPHPExcel->getActiveSheet()->getStyle($key)->getNumberFormat()->setFormatCode('m/d/yyyy');
                        } elseif ($val == 'thumb_url') {
                            $url = createImageLink($creative_id, $airing_id ,$id);
                            $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key, 'View');
                            $objPHPExcel->getActiveSheet()->getStyle($key)->getFont()->setColor( $phpColor );
                            $objPHPExcel->setActiveSheetIndex(0)->getCell($key)->getHyperlink('View')->setUrl($url);
                            /*$objPHPExcel->setActiveSheetIndex(0)->getStyle($key)->getFont()->setColor( $phpColor );
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($key,'=HYPERLINK("'.$url.'", "View")' );*/
                        } else {            
                            $objPHPExcel->setActiveSheetIndex(0)->SetCellValue($key, $val);
                        }
                }
                $i++;
                update_excel_progress($id, $rowNum++);
            }
        }else{
            $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$i.':'.$lastCol.$i);
            $objPHPExcel->setActiveSheetIndex(0)->SetCellValue('A'.$i, "No records found.");
            $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getAlignment()->applyFromArray(
                array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
            );
        }
        unset($rows);
        $objPHPExcel->getActiveSheet()->setTitle("Long Form Airing Detail");
        //$objPHPExcel->setActiveSheetIndex(0); 
        $objDrawing->setWorksheet($objPHPExcel->setActiveSheetIndex(0));   
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
        //$objWriter->setOffice2003Compatibility(true);//commented because it was creating problem with Copy and paste in excel PT##132219317
        $objWriter->save($newFilePath);
    }
    
    return $newFilePath;
}

function formatExcel($file_name='', $no_of_rows) {
    set_time_limit(0);
    ini_set('memory_limit','1024M');
    
    $excel2 = PHPExcel_IOFactory::createReader('Excel2007');
    $excel2 = $excel2->load($file_name); // Empty Sheet
    $excel2->setActiveSheetIndex(0);

    /*
    $excel2->getActiveSheet()->getColumnDimensionByColumn('A')->setAutoSize(false);
    $excel2->getActiveSheet()->getColumnDimensionByColumn('A')->setWidth('37');
    $excel2->getActiveSheet()->getRowDimension(1)->setRowHeight(90);


    $objDrawing = new PHPExcel_Worksheet_Drawing();
    $objDrawing->setName('Logo');
    $objDrawing->setDescription('Logo');
    $logo = 'F:/xampp/htdocs/drmetrix/assets/img/adsSphere.png'; // Provide path to your logo file
    $objDrawing->setPath($logo);  //setOffsetY has no effect
    $objDrawing->setCoordinates('A1');
    $objDrawing->setWorksheet($excel2->getActiveSheet());
   
    */
    for ($row = 3; $row <= $no_of_rows + 2; $row++) {
    $excel2->getActiveSheet()
        ->setCellValue(
            'U' . $row,
            "=HYPERLINK(E{$row}, E{$row})"
            );
    }

    $objWriter = PHPExcel_IOFactory::createWriter($excel2, 'Excel2007');
    $objWriter->save($file_name);

    
}
