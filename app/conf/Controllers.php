<?php

namespace App\Conf;

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
    
    public function sendResponse(array $response) {
        $this->sendSomething(200, $response);
    }
    
    public function send400() {
        $this->sendSomething(400, ['error' => 'unknown command']);        
    }
    
    public function sendNotFound() {
        $this->sendSomething(404, ['error' => 'no records found']);
    }
    
    public function sendError(array $response) {
        $this->sendSomething(400, $response);
    }
    
    protected function sendSomething(int $code, array $response) {
        http_response_code($code);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: *");
        echo json_encode($response);        
    }
    
}
