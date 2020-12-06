<?php

namespace App\pages\search;
use App\Conf\Controllers;
use App\pages\search\SearchModel;
use App\conf\Exceptions\SearchModelException;
use App\utils\JsonResponse;

class SearchController extends Controllers {
    public function zobraz() {
        if (empty($_GET['q'])) {
            JsonResponse::sendClientError(['error' => 'empty query']);
            exit();
        }
        $query = trim($_GET['q']);
        if (empty($query) or strlen($query) > 100) {
            JsonResponse::sendClientError(['error' => 'invalid input']);
            return false;
        }
        
        $model = new SearchModel($this->env);
        try {
            $result = $model->search($query);
            JsonResponse::sendResponse($result);
        }
        catch (SearchModelException $e) {
            if ($e->getCode() == 500) {
                JsonResponse::sendServerError(['error' => 'server error']);
                return false;
            }
            if ($e->getCode() == 999) {
                JsonResponse::sendClientError(['error' => 'too much records returned']);
                return false;
            }
            throw $e;
        }
    }
}
