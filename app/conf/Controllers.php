<?php

namespace App\Conf;
use App\utils\JsonResponse;

abstract class Controllers {
        
    /**
     *
     * @var array
     */
    protected $params;
    
    /**
     *
     * @var Env
     */
    protected $env;
            
    public function __construct(Env $env, array $params) {
        $this->env = $env;
        $this->params = $params;
    }
    
    public function send400() {
        JsonResponse::sendSomething(400, ['error' => 'unknown command']);        
    }    
}
