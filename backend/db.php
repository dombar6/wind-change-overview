<?php
class Database{
    private $server = "localhost";
    private $db = "projektas";
    private $user = "stud";
    private $password = "stud";
    public function getConnection(){
            $conn = new mysqli($this->server, $this->user, $this->password, $this->db);
           if($conn->connect_error) die("Login error: " . $conn->connect_error);
          return $conn;
    }
}
?>