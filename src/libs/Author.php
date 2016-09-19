<?php
Class author {
        public $author = "";
        public $role   = "";
        public function setAuthor($author) {
            $this->author = $author;
        }
        public function getRole() {
           return $this->role;
        }
        public function setRole($role) {
           $this->role = $role;
        }
}
?>
