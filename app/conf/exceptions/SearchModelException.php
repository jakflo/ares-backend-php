<?php

namespace App\conf\Exceptions;
use Exception;
use App\utils\JsonResponse;

class SearchModelException extends Exception {
    public function sendErrorJson() {
        if ($this->getCode() === 500) {
            JsonResponse::sendServerError(['error' => 'server error']);
            exit();
        }
        throw $this;
    }
}
