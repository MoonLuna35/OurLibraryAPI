<?php 
    define('COLLECTION_DB_PATH', dirname(dirname(dirname(__FILE__))));
    include_once COLLECTION_DB_PATH.'/dbHandler.php';
    include_once COLLECTION_DB_PATH.'/book/collection/collection.php';
    
    class CollectionDb extends DB {
        
        public function add(Collection &$collection, int $h = 0) {
            if ($h === MAX_TRY) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                
                $this->_db->beginTransaction();
                $id = $this->add_infos($collection);
                if(!$id) {
                    $this->_db->rollBack();
                    return "collection already registred";

                } //On ajoute la collection
                else {
                    $collection->set_id($id);
                }
                if(!Collection::insert_volumes($this->_db,  $collection)) { //On ajoute les auteurs
                    $this->_db->rollBack();
                    return false;
                }
                
                $this->_db->commit();
                
                
            }
            catch(Exeption $e) {
                $this->add($collection, $h+1); 
            }
            return true;
        }

        private function add_infos($collection) {
            $query = $this->_db->prepare("
                        INSERT INTO 
                            book_collection(
                                title,
                                editor,
                                resume,
                                is_conserved
                            )
                        VALUES(
                            :title,
                            :editor,
                            :resume,
                            :is_conserved
                        )
                        ON DUPLICATE KEY UPDATE id=id
        
                    ");
                    
                    $query->execute(array(
                        ":title" => $collection->get_title(),
                        ":editor" => $collection->get_editor(),
                        ":resume" => $collection->get_resume(),
                        ":is_conserved" => $collection->get_is_conserved() ? 1 : 0
                    )); 
                    if ($query->rowCount() === 0) {
                        return false;
                    }
                    else {
                        return $this->_db->lastInsertId();
                    }
                    
        }

        public function select_collections_of_user($user = 1) {
            $query = $this->_db->prepare("
                SELECT 
                    *
                FROM
                    book_volumes_of_collections
                WHERE
                    user = :user
                ORDER BY
                    id_collection        
                ");
                    
            $query->execute(array(
                ":user" => $user
            )); 
            if ($query->rowCount() === 0) {
                return [];
            }
            else {
                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                if(sizeof($rep) > 0) {
                    return Collection::instance_from_db($rep);
                }   
            }
        }

        public function update_is_conserved(Collection $c, User $u,int $h = 0): bool {
            if ($h === MAX_TRY) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    UPDATE
                        book_collection  
                    SET
                        is_conserved = :is_conserved   
                    WHERE
                        user = :user
                    AND 
                        id = :collection
                ");
                $query->execute(array(
                    ":is_conserved" => $c->get_is_conserved() ? 1 : 0,
                    ":user" => $u->get_id(),
                    ":collection" => $c->get_id(),
                ));

                $this->_db->commit();
                if ($query->rowCount() === 0) {
                    return false;
                }
                return true;
            }
            catch(Exeption $e) {
                $this->_db->rollBack();
                $this->update_is_conserved($c, $u,$h+1); 
            }
        }
    } 
?>