<?php

namespace App\Conf;

class Models {
    protected $env;
    
    public function __construct(Env $env) {
        $this->env = $env;        
    }
}
