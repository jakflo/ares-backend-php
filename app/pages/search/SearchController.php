<?php

namespace App\pages\search;
use App\Conf\Controllers;
use App\pages\search\SearchModel;
use App\conf\Exceptions\SearchModelException;

class SearchController extends Controllers {
    public function zobraz() {
        if (empty($_GET['q'])) {
            $this->sendError(['err' => 'empty query']);
            exit();
        }
        $query = trim($_GET['q']);
        if (empty($query) or strlen($query) > 100) {
            $this->sendError(['error' => 'invalid input']);
            return false;
        }
        
        $model = new SearchModel($this->env);
        try {
            $result = $model->search($query);
            $this->sendResponse($result);
        }
        catch (SearchModelException $e) {
            if ($e->getCode() == 500) {
                $this->sendError(['error' => 'server error']);
                return false;
            }
            if ($e->getCode() == 999) {
                $this->sendError(['error' => 'too much records returned']);
                return false;
            }
            throw new SearchModelException($e);
        }
    }
}
