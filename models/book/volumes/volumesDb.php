<?php
    define('VOL_DB_PATH', dirname(dirname(dirname(__FILE__))));
    include_once VOL_DB_PATH.'/dbHandler.php';
    include_once VOL_DB_PATH.'/book/collection/collection.php';
    class VolumesDB extends DB {
        public static function add(PDO $db, Collection $collection, int $i) {
               $vols = $collection->get_volumes(); 
               $vol = $vols[$i];
               $date = ["", ""];
               $execute_array = array(
                ":collection" => $collection->get_id(),
                ":volume" => $vol->get_num(),
                ":title" => $vol->get_title(),
                ":resume" => $vol->get_resume(),
                ":buy_link" => $vol->get_buy_link(),
                ":is_buyed" => $vol->get_is_buyed() ? 1 : 0,
               ); 
                if($vol->get_parution_date() !== null) {
                    $date[0] = "parution_date,";
                    $date[1] = ":parution_date,";
                    $execute_array[":parution_date"] = date("Y-m-d",$vol->get_parution_date()->getTimestamp());
                }
                $query = $db->prepare("
                    INSERT INTO 
                        book_volumes(
                            collection,
                            volume,
                            title,
                            resume,"
                            .$date[0]."
                            buy_link, 
                            is_buyed
                        )
                    VALUES(
                        :collection,
                        :volume,
                        :title,
                        :resume,"
                        .$date[1]."
                        :buy_link, 
                        :is_buyed
                    )
                    ON DUPLICATE KEY UPDATE id=id
        
                ");
                    
                    $query->execute($execute_array); 
                    
                    if ($query->rowCount() === 0) {
                        return false;
                    }
                    else {
                        return $db->lastInsertId();
                    }
                    return true;
        }

        public function select_all(int $h = 0): array|bool {
            if ($h === MAX_TRY) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    SELECT 
                        c.id,
                        c.title,
                        c.authors,
                        c.preference,
                        v.volume,
                        v.is_buyed
                    FROM 
                        book_volumes AS v 
                    INNER JOIN 
                        book_collection AS c
                    ON v.collection = c.id
                    WHERE 
                        c.user=1
                    ORDER BY 
                        c.id

                ");
                
                $query->execute(); 
                $this->_db->commit();
                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($query->rowCount() > 0) {
                    $collections = array();
                    $i = 0;
                    $j = 0;
                    do {    
                        if(
                            sizeof($collections) === 0 
                            ||
                            $collections[sizeof($collections) - 1].get_id() !== $rep[$i]["id"]
                        ) {
                            array_push($collections, new Collection(array(
                                "id" => $rep[$i]["id"],
                                "id" => $rep[$i]["id"],
                                "id" => $rep[$i]["id"],
                                "id" => $rep[$i]["id"],
                            )));
                        }
                    } while($i < sizeof($rep));
                }
                return false;
                
            }
            catch(Exeption $e) {
                $this->select_all($h+1); 
            }
            
        }
        
        public function update_is_buyed(Volume $v, User $u,int $h = 0): bool {
            if ($h === MAX_TRY) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    UPDATE
                        book_volumes AS v 
                    INNER JOIN 
                        book_collection AS c
                    ON 
                        v.collection = c.id    
                    SET
                        v.is_buyed = :is_buyed  
                    WHERE
                        c.user = :user
                    AND 
                        v.id = :volume
                ");
                $query->execute(array(
                    ":is_buyed" => $v->get_is_buyed() ? 1 : 0,
                    ":user" => $u->get_id(),
                    ":volume" => $v->get_id(),
                ));

                $this->_db->commit();
                if ($query->rowCount() === 0) {
                    return false;
                }
                return true;
            }
            catch(Exeption $e) {
                $this->_db->rollBack();
                $this->update_is_buyed($v, $u,$h+1); 
            }
        }
    }   
    
?>