<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/constants.php';
require_once dirname(__FILE__) . '/functions.php';
ignore_user_abort();

updateAdvAliases();
function updateAdvAliases(){
  $db = getConnection();
  $sql = "UPDATE advertiser SET alt_adv_names = NULL ";
  $stmt = $db->prepare($sql);
  $stmt->execute();

  $alt_adv_arr = array('1930'=>'Bulbhead.com,Bulbhead,Telebrands','1932'=>'TY Young','1936'=>'Liberator Medical','1959'=>'Stanley Steemer','1983'=>'Esurance','1998'=>'Service Master Brands,Service Master','2001'=>'Select Quote,SelectQuote','2035'=>'United Healthcare Services','2058'=>'Safe Step Walkin Tub,Safe Step Walk in Tub','2087'=>'Clear Choice Management Services,Clear Choice','2104'=>'Guthy Renker','2107'=>'Tristar','2109'=>'Basic Research','2111'=>'Christie Brinkely Skincare','2136'=>'Obesity Research','2150'=>'NAC Marketing Company','2182'=>'Integrity Tracking','2200'=>'Den Mat Holdings','2209'=>'FabriClear','2213'=>'GolfKnickers,GoldKnickers.com','2232'=>'Ontel','2280'=>'Johnson and Johnson,Johnson & Johnson','2334'=>'Monaco Rare Coins','2444'=>'Plymouth Direct','2497'=>'Save the Children','2566'=>'Boehringer Ingelheim','2658'=>'Sanofi Aventis','2715'=>'Aarons','2745'=>'Teva Pharmaceutical, Actavis','2842'=>'Bayer','3053'=>'TuffShed','3099'=>'Doctors Clinical','3302'=>'Northern Response','3414'=>'Wearever','3518'=>'CallsDirect,Tax Resolvers','3637'=>'Tempur Sealy,Tempur-Sealy,Tempur Pedic,Tempur-Pedic','3813'=>'Trade Station Technologies','4062'=>'JJ Best & Company, J.J. Best Banc','4463'=>'M Thomassen and Associates','5033'=>'Stain Away','5046'=>'Stop IRS Debt,StopIRSDebt.com','5184'=>'Ideal DRTV','5194'=>'Legacy','5548'=>'Global Life Distribution','5558'=>'Chargon','5681'=>'Vuzix');

  	foreach ($alt_adv_arr as $key => $value) {
   		$sql = "UPDATE advertiser SET alt_adv_names = '".$value."'  WHERE adv_id = '".$key."'";
	  	$stmt = $db->prepare($sql);
	  	$stmt->execute();
   	} 
   	closeConnection();
}


?>