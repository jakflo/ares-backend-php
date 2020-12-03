<?php

namespace App\Conf;
use App\conf\Exceptions\Autorun_exception;

class Router {
    
    /**
     *
     * @var Env
     */
    protected $env;
    
    /**
     *
     * @var Page_not_found_controller
     */
    protected $not_found;


    public function __construct(Env $env) {
        $this->env = $env;
        $this->not_found = $this->load('pageNotFound');
    }


    public function parse(string $uri) {
        $uri = trim($uri, ' /');
        if (strpos($uri, '?') === 0) {
            $uri = '';
        }
        else {
            $uri_arr = explode('?', $uri);
            $uri = $uri_arr[0];
        }
        return explode('/', $uri);        
    }
    
    public function load(string $uri) {
        $parsed = $this->parse($uri);
        $page_name = trim($parsed[0]);
        $page_name = empty($page_name)? 'pageNotFound':$page_name;
        $className = ucfirst($page_name);
        $folder = "{$this->env->get_param('root')}/pages/{$page_name}";
        $controller_path = "App\\pages\\{$page_name}\\{$className}Controller";
        unset($parsed[0]);
        $parsed = array_values($parsed);
        
        try {
            $controller = new $controller_path($this->env, $parsed);
            return $controller;
        }
        catch (Autorun_exception $e) {
            return $this->not_found;
        }        
    }
}
