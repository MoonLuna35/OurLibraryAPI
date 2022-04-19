<?php 

    
    class Author {
        protected int $_id = -1; 
        protected string $_name = "";
        protected string $_surname = "";
        protected string $_function = "";
        protected ?bool $_is_done = false;

        public function __construct($arr) {
            if (isset($arr["id"])) {
                $this->_id = $arr["id"];
            }
            if (isset($arr["name"])) {
                $this->_name = $arr["name"];
            }
            if (isset($arr["surname"])) {
                $this->_surname = $arr["surname"];
            }
            if (isset($arr["function"])) {
                $this->_function = $arr["function"];
            }
        }

        public function to_array() {
            return array(
                "id" => $this->_id,
                "name" => $this->_name,
                "surname" => $this->_surname,
                "function" => $this->_function
            );
        }


        public static function insert_new_authors(PDO $db,  Author &$author): bool {
            $authorDb = new AuthorDb();
            if( //SI
                !$author->get_is_done() //On a pas encore travailler dessus 
                && //ET 
                $author->get_id() === -1 //qu'il contiens des auteur pas encore notes
                && //ET
                !$authorDb->author_is_existing($author) //que l'auteur n'existe pas encore

            ) {//ALORS
                $id = $authorDb->add($author, $db); //On tente d'ajouter l'auteur j
                if($id === 0) { //SI on y arrive pas ALORS
                    return false; //On renvoie falsee
                } 
                $author->set_is_done(true); //On est passer sur l'auteur j (les volumes qui on le meme auteurs sont aussi modifies) 
                    $author->set_id($id); //On mets le nouvel id    
                }  
                
            
            
            return true;
        }

        public function get_id() {
            return $this->_id;
        }
        public function get_name() {
            return $this->_name;
        }
        public function get_surname() {
            return $this->_surname;
        }
        public function get_function() {
            return $this->_function;
        }
        public function get_is_done() {
            return $this->_is_done;
        }


        public function set_id($new_id) {
            $this->_id = $new_id;
        }
        public function set_name($new_name) {
            $this->_name = $new_name;
        }
        public function set_surname($new_surname) {
            $this->_surname = $new_surname;
        }
        public function set_function($new_function) {
            $this->_function = $new_function;
        }

        public function set_is_done($new_is_done) {
            $this->_is_done = $new_is_done;
        }
    }