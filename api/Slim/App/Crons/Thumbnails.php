<?php

class Slim_App_Crons_Thumbnails {

    private $db = NULL;
    var $videoAPIUrl = "http://video.drmetrix.com/api/v1";

    public function __construct() {
        $this->db = Slim_App_Lib_Db::getInstance()->dbh;
    }

    public function updateCreativeThumb() {
        $isDropTmp = $this->dropTmpAiringTbl();
        $isCreateTmp = $this->createTmpAiringTbl();
        if ($isDropTmp && $isCreateTmp) {
            $creatives = $this->getCreatives();
            if (count($creatives) > 0) {
                $i = 0;
                foreach ($creatives as $creative) {

                    //echo $creative['airing_id'] .'==='.$creative['creative_id']."<br>";

                    $apiResponse = $this->requestVideoAPI($creative['airing_id']);
                    $updateResponse = array();
                    $updateResponse['request_url'] = $this->videoAPIUrl . "/" . $creative['airing_id'];
                    $updateResponse['response'] = serialize($apiResponse);
                    $updateResponse['thumb_url'] = (!empty($apiResponse->ocr_image_url)) ? $apiResponse->ocr_image_url : '';
                    $this->updateResponse($updateResponse, $creative['creative_id']);

                    if (empty($apiResponse->error)) {
                        $updateData = array('thumbnail' => NULL, 'video' => NULL);
                        //echo $apiResponse->ocr_image_url."<br>";
                        if (!empty($apiResponse->ocr_image_url)) {
                            if (@getimagesize($apiResponse->ocr_image_url)) {
                                $thumbnail = $this->createImage($apiResponse->ocr_image_url, $creative['creative_id']);
                                $updateData['thumbnail'] = $thumbnail;
                            }
                        }
                        if (!empty($apiResponse->streaming_url)) {
                            $updateData['video'] = $apiResponse->streaming_url;
                        }

                        if (!empty($updateData['thumbnail']) || !empty($updateData['video'])) {
                            $this->updateCreative($updateData, $creative['creative_id']);
                        }
                    }
                    $i = $i + 1;
                    if ($i == 500) {
                        sleep(2);
                        flush();
                        $i = 1;
                    }
                }
            }
        }
    }
    
    
//video = '" . $data['video'] . "'
    private function updateCreative($data, $creativeId) {
        $sql = "
            UPDATE
                creative
            SET
                thumb = '" . $data['thumbnail'] . "'
            WHERE
                creative_id = '" . $creativeId . "'
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    private function updateResponse($data, $creativeId) {
        $sql = "
            UPDATE
                creative
            SET
                request_url = '" . addslashes($data['request_url']) . "',
                response = '" . addslashes($data['response']) . "',
                thumb_url = '" . addslashes($data['thumb_url']) . "'
            WHERE
                creative_id = '" . $creativeId . "'
        ";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    private function createImage($imageUrl, $creativeId) {
        $imageDir = realpath(dirname(__FILE__)) . "/../../../../assets/img/creatives/" . $creativeId;
	
        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777);
            chmod($imageDir, 0777);
        }

        $fileDetails = pathinfo($imageUrl);
        $imagePath = $imageDir . "/" . $fileDetails['basename'];
        copy($imageUrl, $imagePath);
//        $image = file_get_contents($apiResponse->ocr_image_url);
//        file_put_contents('/var/www/html/testing/123'.$fileDetails['basename'], $image);
        $thumbName = $fileDetails['filename'] . "_thumb" . "." . $fileDetails['extension'];
        $imageThumbPath = $imageDir . "/" . $thumbName;
        $cmd = "convert -resize 155x106 " . $imagePath . " -background none -gravity center -extent 155x106 " . $imageThumbPath;
        shell_exec($cmd);

        return $thumbName;
    }

    private function createTmpAiringTbl() {
        /*$sql = "
            CREATE TABLE `airings_temp`
            SELECT a.*
            FROM   airings a
                   INNER JOIN (SELECT `airing_id`,
                                      `creative_id`,
                                      Max(`end`) AS lastAired,
                                      `length`
                               FROM   `airings`
                               GROUP  BY `creative_id`,
                                         `length`) grpairings
                           ON a.creative_id = grpairings.creative_id
				AND a.length = grpairings.length
                              AND a.end = grpairings.lastaired
        ";*/

        $sql_create = "CREATE TABLE creative_tmp LIKE creative";
        $stmt = $this->db->prepare($sql_create); 
        $stmt->execute();
        $sql_insert = "INSERT creative_tmp SELECT * FROM creative WHERE thumb IS NULL OR thumb =  '' ";
        $stmt = $this->db->prepare($sql_insert); 
        $stmt->execute();
        return true;
    }

    private function dropTmpAiringTbl() {
        //$sql = "DROP TABLE IF EXISTS `airings_temp`";
        $sql = "DROP TABLE IF EXISTS `creative_tmp`";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    private function getCreatives() {
        $creatives = array();
        /*$sql = "
            SELECT
                c.creative_id,
                c.length,
                a.airing_id
            FROM
                creative c
            LEFT JOIN
                airings_temp a ON c.creative_id = a.creative_id AND c.length = a.length
            WHERE 
                (c.thumb = '' OR c.thumb IS NULL)
                AND a.airing_id IS NOT NULL
            GROUP BY
                c.creative_id, c.length
            ORDER BY
                c.creative_id
        ";*/
        $sql ="SELECT `creative_id`, `thumbnail` as airing_id FROM `creative_tmp`" ;
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute()) {
            $creatives = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $creatives;
    }

    public function requestVideoAPI($airingId) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->videoAPIUrl . "/ads/$airingId");
        curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result   
        // Fetch and return content, save it.
        $raw_data = curl_exec($ch);
        curl_close($ch);

        // If the API is JSON, use json_decode.
        return json_decode($raw_data);
    }

}
