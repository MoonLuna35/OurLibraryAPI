<?php 

define('COLLECTION_PATH', dirname(dirname(dirname(__FILE__))));
include_once COLLECTION_PATH . '/book/author/authorDb.php';
include_once COLLECTION_PATH . '/book/volumes/volumesDb.php';
include_once COLLECTION_PATH . '/book/author_of/author-ofDB.php';
    class Collection {
        private int $_id = -1;
        private string $_title = "";
        private bool $_is_conserved = true;
        private string $_editor = "";
        private string $_resume = "";
        private array $_volumes = [];

        public function __construct($arr) { 
            if (isset($arr["id"])) {
                $this->_id = $arr["id"];
            }
            if (isset($arr["title"])) {
                $this->_title = $arr["title"];
            }
            if (isset($arr["is_conserved"])) {
                $this->_is_conserved = $arr["is_conserved"];
            }
            if (isset($arr["editor"])) {
                $this->_editor = $arr["editor"];
            }
            if (isset($arr["resume"])) {
                $this->_resume = $arr["resume"];
            }
            if (isset($arr["volumes"])) {
                $this->_volumes = $arr["volumes"];
            }
        }

        public function to_array(): array  {
            $volArr = [];

            for($i = 0; $i < sizeof($this->_volumes); $i++) {
                array_push($volArr, $this->_volumes[$i]->to_array());
                
            }
            return array(
               "id" => $this->_id, 
               "title" => $this->_title,
               "resume" => $this->_resume,
               "editor" => $this->_editor,
               "is_conserved" => $this->_is_conserved,
               "volumes" => $volArr
            );
        }

        /*
        * DANS CETTE FONCTION : 
        * On optimise les auteurs si ce sont les memes on mets des refferences
        */
        public function prepare_author_to_add() {
            for($i = 0; $i < sizeof($this->_volumes); $i++) { //POUR TOUT volume FAIRE
                for($j = 0; $j < sizeof($this->_volumes[$i]->authors); $j++){ //POUR TOUT auteur du volume i FAIRE
                    if(!$this->_volumes[$i]->authors[$j]->get_is_done()) { //SI on est deja passer sur l'auteur j du volume i ALORS
                        for($k = $i+1; $k < sizeof($this->_volumes); $k++) { //POUR TOUT volume A PARTIR du volume juste apres le volume i  FAIRE
                            for($l = 0; $l < sizeof($this->_volumes[$k]->authors); $l++){ //POUR TOUT auteur du volume k FAIRE
                                if( //SI l'auteur i du volume j est le meme que celui du volume k ALORS
                                    $this->_volumes[$i]->authors[$j]->get_name() == $this->_volumes[$k]->authors[$l]->get_name()
                                    &&
                                    $this->_volumes[$i]->authors[$j]->get_surname() == $this->_volumes[$k]->authors[$l]->get_surname()
                                    &&
                                    $this->_volumes[$i]->authors[$j]->get_function() == $this->_volumes[$k]->authors[$l]->get_function()
                                    ||
                                    $this->_volumes[$i]->authors[$j]->get_id() == $this->_volumes[$k]->authors[$l]->get_id()
                                    &&
                                    $this->_volumes[$i]->authors[$j]->get_id() !== -1
                                ) {
                                    $this->_volumes[$k]->authors[$l] = &$this->_volumes[$i]->authors[$j]; //l'auteur l du volume k pointe sur l'auteur j du volume i
                                }
                            }
                        }
                        $this->_volumes[$i]->authors[$j]->set_is_done(true);
                    }
                }
            }
        }

        /*
        * DANS CETTE FONCTION 
        *   On regarde si les auteurs avec un id sont present en base de donnees 
        *   si il y en a un qui n'est pas bon, on renvoie 400
        */ 
        public function authors_with_id_are_existing() {
            $authorDb = new AuthorDb();
            Collection::reset_authors_action_done($this->_volumes);
            for($i = 0; $i < sizeof($this->_volumes); $i++) {
                for($j = 0; $j < sizeof($this->_volumes[$i]->authors); $j++) {
                    if(
                        !$this->_volumes[$i]->authors[$j]->get_is_done()
                        &&
                        $this->_volumes[$i]->authors[$j]->get_id() !== -1
                        &&
                        !$authorDb->author_is_existing($this->_volumes[$i]->authors[$j])
                    ) {
                        header('HTTP/1.1 400 Bad Request'); //On renvoie 400
                        exit; 
                    }
                    $this->_volumes[$i]->authors[$j]->set_is_done(true);
                }
            }
            
        }
        
        public static function insert_volumes(PDO $db,  Collection &$collection): bool {
            $volumeDb = new VolumesDB();
            $volumes = &$collection->get_volumes();
            Collection::reset_authors_action_done($volumes);
            for($i = 0; $i < sizeof($volumes); $i++) {
                //On insere les infos sur le volume
                $vol_id = VolumesDB::add($db, $collection,$i); 
                if(!$vol_id) {
                    return false;
                }
                else {
                    $volumes[$i]->set_id($vol_id);
                }
                //On insere  les auteurs
                for($j = 0; $j < sizeof($volumes[$i]->authors); $j++) { //POUR TOUT auteur du volume i FAIRE
                    if(!Author::insert_new_authors($db,  $volumes[$i]->authors[$j])) {
                        return false;
                    }
                    $auth = new AuthorOf($volumes[$i]->authors[$j], $volumes[$i]);
                    if(!AuthorOfDb::add($db,  $auth)) {
                        return false;
                    }//On insere la relation "auteur de"  
                }
            }
            
            return true;
        }

        public static function instance_from_db(Array $rep): Array {
            $authorDb = new AuthorDB();
            $collections = array();
            $volumes = array();
            $i = 0; $j = -1;    
                    do  {
                        if( //SI
                            sizeof($collections) === 0 //C'est la premiere collection 
                            || //OU
                            $collections[$j]->get_id() !== (int) $rep[$i]["id_collection"] //Que c'est la collection suivante
                        ) { //ALORS
                            
                            array_push($collections, new Collection(array(
                                "id" => $rep[$i]["id_collection"],
                                "title" => $rep[$i]["title_collection"],
                                "is_conserved" => $rep[$i]["is_conserved"],
                                "editor" => $rep[$i]["editor"],
                                "resume" => $rep[$i]["resume_collection"]
                            )));//On instancie la collection
                            $j++;
                        
                        }
                        $v = new Volume(array(
                            "id" => $rep[$i]["id_volume"],
                            "num" => $rep[$i]["volume"],
                            "title" => $rep[$i]["title_volume"],
                            "resume" => $rep[$i]["resume_volume"],
                            "parution_date" => new DateTime($rep[$i]["parution_date"]),
                            "buy_link" => $rep[$i]["buy_link"],
                            "is_buyed" => $rep[$i]["is_buyed"]
                        ));
                        $authorDb->select_author_of_volumes($v);
                        $collections[$j]->add_volume($v);
                        

                        $i++;
                          
                    } while($i < sizeof($rep));
            return $collections;
        }

        public function set_id(int $new_id) {
            $this->_id = $new_id;
        }
        public function set_title(string $new_title) {
            $this->_title = $new_title;
        }
        public function set_is_conserved(bool $new_is_conserved) {
            $this->_is_conserved = $new_is_conserved;
        }
        public function set_editor(string $new_editor) {
            $this->_editor = $new_editor;
        }
        public function set_resume($new_resume) {
            $this->_resume = $new_resume;
        }

        public function add_author_at(Author $author, int $i) {
            $this->_volumes[$i]->add_author($author);
        }

        public function add_volume(Volume $vol) {
            array_push($this->_volumes, $vol);
        }

        public function get_id(): int {
            return $this->_id;
        }
        public function get_title(): string {
            return $this->_title;
        }
        public function get_is_conserved(): bool {
            return $this->_is_conserved;
        }
        public function get_editor(): string {
            return $this->_editor;
        }
        public function &get_volumes(): array {
            return $this->_volumes;
        }
        public function get_resume() {
            return $this->_resume;
        }


        public static  function reset_authors_action_done(Array &$volumes) {
            for($i = 0; $i < sizeof($volumes); $i++) {
                for($j = 0; $j < sizeof($volumes[$i]->authors); $j++) {
                    $volumes[$i]->authors[$j]->set_is_done(false);
                }
            }
        }


        

        
    }   

?> 