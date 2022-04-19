<?php
    class User {
        private $_id = 1;

        public function get_id(): int {
            return $this->_id;
        }
    }
    $current_user = new User();
    $output["data"] = [];
?>