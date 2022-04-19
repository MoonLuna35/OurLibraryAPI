

<?php 
    define('VOLUME_PATH', dirname(dirname(dirname(__FILE__))));
    include_once VOLUME_PATH . '/book/collection/collection.php';

    class Volume {
        private int $_id = -1;
        private int $_num = -1;
        private string $_title = "";
        private string $_resume = "";
        private ?DateTime $_parution_date = null; 
        private string $_buy_link = "";
        private bool $_is_buyed = false;
        public array $authors = []; 

        public function __construct($arr) { 
            if (isset($arr["volume"])) { //On construit a partir du json
                if (isset($arr["volume"]->num)) {
                    $this->_num = $arr["volume"]->num;
                }
                if (isset($arr["volume"]->title)) {
                    $this->_title = $arr["volume"]->title;
                }
                if (isset($arr["volume"]->resume)) {
                    $this->_resume = htmlentities(trim($arr["volume"]->resume));
                }
                if (isset($arr["volume"]->parution_date)) {
                    $this->_parution_date = new DateTime($arr["volume"]->parution_date);
                }
                if (isset($arr["volume"]->buy_link)) {
                    $this->_buy_link = $arr["volume"]->buy_link;
                }
                if (isset($arr["volume"]->is_buyed)) {
                    $this->_is_buyed = $arr["volume"]->is_buyed;
                }
            }
            //On construit avec un tableau
            if (isset($arr["id"])) { 
                $this->_id = $arr["id"];
            }
            if (isset($arr["num"])) {
                $this->_num = $arr["num"];
            }
            if (isset($arr["title"])) {
                $this->_title = $arr["title"];
            }
            if (isset($arr["resume"])) {
                $this->_resume = $arr["resume"];
            }
            if (isset($arr["parution_date"])) {
                $this->_parution_date = $arr["parution_date"];
            }
            if (isset($arr["buy_link"])) {
                $this->_buy_link = $arr["buy_link"];
            }
            if (isset($arr["is_buyed"])) {
                $this->_is_buyed = $arr["is_buyed"];
            }
            if (isset($arr["authors"])) {
                $this->authors = $arr["authors"];
            }
            
        }

        

        public function to_array(): array  {
            $authors_arr = array();
            $arr = array(
                "id" => $this->_id, 
                "num" => $this->_num, 
                "title" => $this->_title, 
                "resume" => $this->_resume, 
                "parution_date" => $this->_parution_date, 
                "buy_link" => $this->_buy_link, 
                "is_buyed" => $this->_is_buyed, 
            );
            for($i = 0; $i < sizeof($this->authors); $i++) {
                array_push($authors_arr, $this->authors[$i]->to_array());
            }
            $arr["authors"] = $authors_arr;
            return $arr;
        }



        public function get_id() {
            return $this->_id;
        }
        public function get_num() {
            return $this->_num;
        }
        public function get_title() {
            return $this->_title;
        }
        public function get_resume() {
            return $this->_resume;
        }
        public function get_parution_date() {
            return $this->_parution_date;
        }
        public function get_buy_link() {
            return $this->_buy_link;
        }
        public function get_is_buyed() {
            return $this->_is_buyed;
        }


        //setters
        public function set_id($new_id) {
            $this->_id = $new_id;
        }
        public function set_num($new_num) {
            $this->_num = $new_num;
        }
        public function set_title($new_title) {
            $this->_title = $new_title;
        }
        public function set_resume($new_resume) {
            $this->_resume = $new_resume;
        }
        public function set_parution_date($new_parution_date) {
            $this->_parution_date = $new_parution_date;
        }
        public function set_buy_link($new_buy_link) {
            $this->_buy_link = $new_buy_link;
        }
        public function setIs_buyed($newis_buyed) {
            $this->is_buyed = $newis_buyed;
        }
        public function add_author($author) {
            array_push($this->authors, $author);
        }
        
    }

