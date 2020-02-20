<?php

class Slim_App_Crons_UpdateThumbDb {

    private $db = NULL;
    var $videoAPIUrl = "http://video.drmetrix.com/api/v1";

    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
    }

    public function UpdateThumbDb(){
    	//$sql ="SELECT * FROM  `creative`";
        $sql = "SELECT * FROM  `creative` where (thumb IS NULL OR thumb ='') AND (request_url IS NOT NULL OR request_url !='')";
    	$stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $i=1; $c=0;
	        foreach ($result as $key => $value){     

		        $imageDir = realpath(dirname(__FILE__)) . "/../../../../assets/img/creatives/".$value['creative_id'];
                if (is_dir($imageDir)) {
                    if ($dh = opendir($imageDir)) {
                        while (($file = readdir($dh)) !== false) {
                            if($file != '..' && $file != '.'){
                                $getname = explode('thumb', $file);
                                if(isset($getname[1])){
                                    //if(!empty($value['thumb']) && $file != $value['thumb']){
                                        $c++;
                                        echo $c .'--->'."folder_file == ".$file.'----> DB file--->'.$value['thumb'].'---  creative Id--->'.$value['creative_id']."<br />";
                                       /* $sql_update = "UPDATE creative SET thumb = '".$file."' WHERE creative_id = '".$value['creative_id']."'";
                                        echo $sql_update."<br>";
                                        $stmt = $this->db->prepare($sql_update);
                                        $stmt->execute();*/
                                   // }
                                }
                            }
                        }
                        closedir($dh);
                    }
                }
		        
		    }
        }       
    } 
}
?>