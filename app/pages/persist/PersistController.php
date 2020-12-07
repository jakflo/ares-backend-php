<?php

namespace App\pages\persist;
use App\Conf\Controllers;
use App\utils\JsonResponse;
use App\conf\Exceptions\DetailModelException;
use App\pages\getDetail\GetDetailFromApi;
use App\pages\persist\PersistModel;

class PersistController extends Controllers {
    public function zobraz() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JsonResponse::sendSomething(405, ['error' => 'only POST method is permited']);
            return false;
        }
        
        if (empty($this->params[0])) {
            JsonResponse::sendClientError(['error' => 'empty query']);
            return false;
        }
        
        $api = new GetDetailFromApi($this->env);
        $model = new PersistModel($this->env);
        try {
            $ico = trim($this->params[0]);
            $api->validateIco($ico);
            $result = $api->getDetailFromXml($ico);
            $model->removeIfExists($ico);
            $model->persist($result);
            JsonResponse::sendResponse(['stat' => 'OK']);
        }
        catch (SearchModelException|DetailModelException $e) {
            $e->sendErrorJson();           
        }        
    }
}
