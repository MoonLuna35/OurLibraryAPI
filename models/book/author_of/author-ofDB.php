<?php 
    define('AUTHOR_OF_DB_PATH', dirname(dirname(dirname(__FILE__))));
    include_once AUTHOR_OF_DB_PATH.'/dbHandler.php';
    include_once AUTHOR_OF_DB_PATH.'/book/author_of/author-of.php';
    
    class AuthorOfDb extends DB {
        public static function add(PDO $db, $authorOf) {
            $query =  $db->prepare("
                    INSERT INTO
                        book_author_of(
                            author,
                            volume
                        )
                    VALUES(
                        :author,
                        :volume
                    )
                    ON DUPLICATE KEY UPDATE author = author
                ");
                $query->execute(array(
                    ":author" => $authorOf->get_author()->get_id(),
                    ":volume" => $authorOf->get_volume()->get_id()
                )); 
                if($query->rowCount() === 0) {
                    return false;
                }
                return true;
        }
    }