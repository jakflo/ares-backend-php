<?php

namespace App\Conf;

use \App\Conf\Db_wrap;
use App\conf\Exceptions\Autorun_exception;

class Env {
    
    /**
     * @var string
     */
    protected $uber_root;
    
    /**
     *
     * @var string
     */
    protected $root;
    
    /**
     *
     * @var string
     */
    protected $webroot;

    /**
     *
     * @var string
     */
    private $servername, $username, $password, $dbname;


    /**
     *
     * @var Db_wrap
     */
    public $db;


    public function __construct(string $uber_root) {
        $this->uber_root = $uber_root;
        $this->root = $this->uber_root.'\app';
        $http = isset($_SERVER['HTTPS'])? 'https://' : 'http://';
        $this->webroot = $http.$_SERVER['HTTP_HOST'];
        
        spl_autoload_register(array($this, 'autorun'));
        
        //připojení k DB
        $this->servername = "localhost";
        $this->username = "root";
        $this->password = "12345";
        $this->dbname = "ares";
        $this->db = $this->connect_db($this->servername, $this->username, $this->password, $this->dbname);       
    }
    
    public function get_param(string $param_name) {
        $obj_props = array_keys(get_object_vars($this));
        if (in_array($param_name, $obj_props)) {
            return $this->$param_name;
        }
        else {
            throw new Exception("Parametr {$param_name} nenalezen v Env");
        }
    }
    
    public function autorun(string $class_nm) {
        $class_nm = str_replace('App\\', '', $class_nm);        
        $class_nm = "{$this->root}/{$class_nm}.php";
        $class_nm = str_replace('\\', '/', $class_nm);
        if (file_exists($class_nm)) {
            include_once $class_nm;
        }
        else {
            throw new Autorun_exception("<br>třída {$class_nm} nenalazena<br>");
        }
    }
    
    public function connect_db() {
        $db = new Db_wrap($this->servername, $this->username, $this->password, $this->dbname);
        return $db;        
    }
}
