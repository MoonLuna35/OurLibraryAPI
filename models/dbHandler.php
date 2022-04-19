<?php
require_once("../../includes/CONF.php");
// Connect with the database.
class DB {
        protected $_db;
        function __construct() {
                $this->_db = new PDO(
                        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, 
                        DB_USER, 
                        DB_PASS, array(
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
                $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
}
