<?php
	require_once 'config.php';
	header("Content-Type: application/octet-stream");
	$file_to_save = EULA_PDF;
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Content-Type: application/force-download");
    header('Content-Disposition: attachment; filename=' . urlencode(basename('EULA_Agreement.pdf')));
    // header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_to_save));
    ob_clean();
    flush();
    readfile($file_to_save);
    exit;
   
?>