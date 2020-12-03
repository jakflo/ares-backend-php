<?php

namespace App\Conf;
use PDO;
use PDOException;


class Db_wrap {
    private $servername, $username, $password, $dbname;    
    private $conn;
    
    private function connect(){
        try {
            $this->conn = new PDO("mysql:host=$this->servername;dbname=$this->dbname", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e) {
            echo "Chyba připojení k databázi";
            exit();            
        }        
    }
    
    public function __construct(string $servername, string $username, string $password, string $dbname) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;

        if (!isset($this->conn)) {
            $this->connect();            
        }        
    }
    
    public function __destruct() {
        $this->conn = null;        
    }
    
    public function sendSQL(string $sql, array $para_arr = array()) {
        try {
            $prepared = $this->conn->prepare($sql);
            $prepared->execute($para_arr);
        }
        catch (PDOException $e) {
            echo "<br>{$sql}<br>";
            print_r($para_arr);
            throw $e;
        }
        return $prepared;
    }
    
    public function sendSQL_varType_forced(string $sql, array $para_arr, array $type_mask) {
        $prepared = $this->conn->prepare($sql);
        if (count($para_arr) != count($type_mask)) {
            return FALSE;            
        }
        $c = 0;
        foreach ($type_mask as $mask){
            switch ($mask){
                case "s": 
                    $type_mask_proc = PDO::PARAM_STR; 
                    break;
                case "i": 
                    $type_mask_proc = PDO::PARAM_INT; 
                    break;
                default: 
                    return FALSE; 
            }
            $prepared->bindParam($c + 1, $para_arr[$c], $type_mask_proc);
            $c++;
        }
        return $prepared->execute();
    }
        
    public function dotaz_vse(string $sql, array $para_arr = array()) {
        $prepared = $this->sendSQL($sql, $para_arr);
        return $prepared->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function dotaz_sloupec(string $sql, string $nazev_sloupce, array $para_arr = array()) {
        $result = $this->dotaz_vse($sql, $para_arr);
        return array_column($result, $nazev_sloupce);        
    }

    public function dotaz_radek(string $sql, array $para_arr = array()) {
        $prepared = $this->sendSQL($sql, $para_arr);
        return $prepared->fetch(PDO::FETCH_ASSOC);
    }
    
    public function dotaz_hodnota(string $sql, array $para_arr = array()) {
        $radek = $this->dotaz_radek($sql, $para_arr);
        $sloupce = array_keys($radek);
        return $radek[$sloupce[0]];
    }
    
    public function get_last_id() {
        return $this->dotaz_hodnota("SELECT LAST_INSERT_ID()");
    }
}
