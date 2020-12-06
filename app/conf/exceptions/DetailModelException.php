<?php

namespace App\conf\Exceptions;
use Exception;
use App\utils\JsonResponse;

class DetailModelException extends Exception {
    public function sendErrorJson() {
        switch ($this->getCode()) {
            case 400:
                JsonResponse::sendClientError(['error' => 'invalid input']);
                exit();
            case 401:
                JsonResponse::sendClientError(['error' => 'empty query']);
                exit();
            case 404:
                JsonResponse::sendClientError(['error' => 'no records found']);
                exit();
            default:
                throw $this;                    
        }
    }
}
