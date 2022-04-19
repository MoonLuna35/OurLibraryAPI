<?php 
    define('AUTHOR_DB_PATH', dirname(dirname(dirname(__FILE__))));
    include_once AUTHOR_DB_PATH.'/dbHandler.php';
    include_once AUTHOR_DB_PATH.'/book/collection/collection.php';
    
    class AuthorDB extends DB {
        public static function add(Author $author, PDO $db) {

                $query =  $db->prepare("
                    INSERT INTO
                        book_author(
                            name,
                            surname,
                            function
                        )
                    VALUES(
                        :name,
                        :surname,
                        :function
                    )
                    ON DUPLICATE KEY UPDATE id = id
                ");
                $query->execute(array(
                    ":name" => $author->get_name(),
                    ":surname" => $author->get_surname(),
                    ":function" => $author->get_function()
                )); 
            
                $id = $db->lastInsertId();;
                return $id;
        }

        public function author_is_existing(Author $author, int $h=0) {
            
            if ($h === MAX_TRY) {
                
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query =  $this->_db->prepare("
                    SELECT 
                        id
                    FROM 
                        book_author
                    WHERE 
                        name = :name
                        AND
                        surname = :surname
                        AND
                        function = :function
                ");
                $query->execute(array(
                    ":name" => $author->get_name(),
                    ":surname"  => $author->get_surname(),
                    ":function" => $author->get_function()
                )); 
                $this->_db->commit();
                if($query->rowCount() > 0) {
                    return true;
                }
                return false;
            }
            catch(Exeption $e) {
                $this->author_is_existing($author, $h+1); 
            }
        }

        public function author_is_existing_by_id(Author $author, int $h=0) {
            
            if ($h === MAX_TRY) {
                
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query =  $this->_db->prepare("
                    SELECT 
                        id
                    FROM 
                        book_author
                    WHERE 
                        id=:id
                ");
                $query->execute(array(
                    ":id" => $author->get_id()
                )); 
                $this->_db->commit();
                if($query->rowCount() > 0) {
                    return true;
                }
                return false;
            }
            catch(Exeption $e) {
                $this->author_is_existing($author, $h+1); 
            }
        }

        public function select_author_of_volumes(Volume &$v, $user =1 ) {
            $authors = array();
            $query = $this->_db->prepare("
                SELECT 
                    id,
                    name,
                    surname, 
                    function
                FROM
                    book_authors_of_volunmes
                WHERE
                    user = :user
                AND
                    volume = :volume        
                ");
                    
            $query->execute(array(
                ":user" => $user,
                ":volume" => $v->get_id()
            )); 
            if ($query->rowCount() === 0) {
                return [];
            }
            else {
                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                if(sizeof($rep) > 0) {
                    for($i = 0; $i < sizeof($rep); $i++) {
                        $v->add_author(new Author(array(
                            "id" => $rep[$i]["id"],
                            "name" => $rep[$i]["name"],
                            "surname" => $rep[$i]["surname"], 
                            "function" => $rep[$i]["function"]
                        )));
                    }
                }   
            }
            return $authors;
        }
    }