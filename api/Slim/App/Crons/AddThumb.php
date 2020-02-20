<?php

class Slim_App_Crons_AddThumb {

    private $db = NULL;
    var $videoAPIUrl = "http://video.drmetrix.com/api/v1";

    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
    }

    public function updateThumb(){
    	$sql ="SELECT * FROM  `creative`";
    	$stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $i=1;
	        foreach ($result as $key => $value){               
		        $imageDir = realpath(dirname(__FILE__)) . "/../../../../assets/img/creatives/".$value['creative_id'];
		        if (is_dir($imageDir)) {
                    $thumb_file = $value['thumb'];
                    $imageThumbPath = $imageDir.'/'.$thumb_file;
                    if(!file_exists($imageThumbPath)){                        
                        $a = explode('_',$thumb_file);
                        $main_img = $a[0].".jpg";
                        $imagePath = $imageDir.'/'.$main_img;                                              
                        if(file_exists($imagePath)){
                            $i++;
                            echo $i.' --> '.$value['creative_id']."<br>";
                            $cmd = "convert -resize 155x106 " . $imagePath . " -background none -gravity center -extent 155x106 " . $imageThumbPath;
                            shell_exec($cmd);
                        }
                    }
		        }
		    }
        }       
    } 
}
?>