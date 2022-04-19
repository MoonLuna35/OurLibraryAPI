<?php 
    define('AUTHOR_OF_PATH', dirname(dirname(dirname(__FILE__))));
    include_once AUTHOR_OF_PATH . '/book/author/author.php';
    include_once AUTHOR_OF_PATH . '/book/author/author.php';

    class AuthorOf {
        private Author $_author; 
        private Volume $_volume; 

        public function __construct(Author $author, Volume $volume) {
            $this->_author = $author;
            $this->_volume = $volume; 
        }


        public function get_author() {
            return $this->_author;
        }
        public function get_volume() {
            return $this->_volume;
        }


        public function set_author($new_author) {
            $this->_author = $new_author;
        }
        public function set_volume($new_volume) {
            $this->_volume = $new_volume;
        }
     }