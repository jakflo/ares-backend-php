<?php

namespace App\utils;

class JsonResponse {
    public static function sendResponse(array $response) {
        self::sendSomething(200, $response);
    }
    
    public static function sendNotFound() {
        self::sendSomething(404, ['error' => 'no records found']);
    }
    
    public static function sendClientError(array $response) {
        self::sendSomething(400, $response);
    }
    
    public static function sendServerError(array $response) {
        self::sendSomething(500, $response);        
    }
    
    public static function sendSomething(int $code, array $response) {
        http_response_code($code);
        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: *");
        echo json_encode($response);        
    }
}
