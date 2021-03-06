<?php


abstract class MysqliConnect implements DatabaseConnect {
    private $dbhost;
    private $dbuser;
    private $dbpass;
    private $dbname;
    private $dbh;     /*يحمل بيانات الاتصال بقاعده البيانات*/
    private $error;   /*يحمل الاخطاء*/
    private $conn;    /*لعمل الاتصال بقاعده البيانات*/
    private $stmt;

    public function __construct(){
        $this->dbhost = DB_HOST;
        $this->dbuser = DB_USER;
        $this->dbpass = DB_PASS;
        $this->dbname = DB_NAME;
    }

    /*method خاص بالاتصال بقاعده البيانات*/
    protected function conn(){
        $this->conn = mysqli_connect($this->dbhost,$this->dbuser,$this->dbpass,$this->dbname);
                      mysqli_set_charset($this->conn,'utf8');
        try {
            $this->dbh = $this->conn;
        }catch (Exception $e){
            die($this->error = $e->getMessage());
        }
        return $this->dbh; /*لارجاع بيانات الاتصال بالقاعده*/
    }

    public function query($colum,$table,$other = null){
        $this->stmt = mysqli_query($this->conn(),"SELECT {$colum} FROM {$table} {$other}");
    }

    public function execute(){
        return $this->stmt;
    }

    public function rowCount(){
        return mysqli_num_rows($this->stmt);
    }

    public function fetch(){
        return mysqli_fetch_array($this->stmt);
    }

    public function lastId(){
        return mysqli_insert_id($this->conn());
    }

    public function insert($table,$colum,$value){
        $this->stmt = mysqli_query($this->conn(),"INSERT INTO {$table} ({$colum}) VALUES ({$value})");
    }

    public function update($table,$data,$colum,$id,$other = null){
        $this->stmt = mysqli_query($this->conn(),"UPDATE {$table} SET {$data} WHERE {$colum} = {$id} {$other}");
    }

    public function delete($table,$colum,$id,$other = null){
        $this->stmt = mysqli_query($this->conn(),"DELETE FROM {$table} WHERE {$colum} = {$id} {$other}");
    }

    public function html_special($string){
        return htmlspecialchars($string);
    }

    public function html_tags($string){
        return strip_tags($string);
    }

    public function filter_string($variable){
        return filter_var($variable,FILTER_SANITIZE_STRING);
    }

    public function filter_email($variable){
        return filter_var($variable,FILTER_SANITIZE_EMAIL);
    }

    public function esc($string){
        return mysqli_real_escape_string($this->conn(),$string);
    }
}