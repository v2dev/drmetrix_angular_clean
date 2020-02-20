<?php

class Slim_App_Crons_Thumbs {

    private $db = NULL;
    var $videoAPIUrl = "http://video.drmetrix.com/api/v1";

    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
    }

    public function updateCreativeThumb() {       

        $sql = "SELECT b.brand_id, c.creative_id, c.thumb
              FROM brand b
              INNER JOIN  `creative` c ON c.brand_id = b.brand_id
              WHERE logo IS NULL
              GROUP BY b.brand_id
              ORDER BY c.first_detection";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $image_brand = realpath(dirname(__FILE__)) . "/../../../../assets/img/brands";
        if (!is_dir($image_brand)) {
            mkdir($image_brand, 0777);
            chmod($image_brand, 0777);
        }

        foreach($result as $k => $v){
            $imageDir = realpath(dirname(__FILE__)) . "/../../../../assets/img/creatives/" . $v->creative_id;
            
            if (is_dir($imageDir)) {
                $thumb_file = $v->thumb;
                $imageThumbPath = $imageDir.'/'.$thumb_file;
                
                $imageThumbBrand = $image_brand .'/'.$thumb_file;
                
                if(file_exists($imageThumbPath)){

                    copy($imageThumbPath, $imageThumbBrand);
                    chmod($imageThumbBrand, 0777);
                    $updateQuery = "UPDATE brand SET logo = '".$v->thumb."' WHERE brand_id = '".$v->brand_id."'";
                    $stmt = $this->db->prepare($updateQuery);
                    $stmt->execute();
                }

            }
        }

        echo "done";
    }

}