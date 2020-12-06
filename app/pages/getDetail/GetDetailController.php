<?php

namespace App\pages\getDetail;
use App\Conf\Controllers;
use App\utils\JsonResponse;
use App\conf\Exceptions\DetailModelException;
use App\pages\getDetail\GetDetailFromApi;

class GetDetailController extends Controllers {
    public function zobraz() {
        if (empty($this->params[0])) {
            JsonResponse::sendClientError(['error' => 'empty query']);
            return false;
        }
        $api = new GetDetailFromApi($this->env);
        try {
            $ico = trim($this->params[0]);
            $api->validateIco($ico);
            $result = $api->getDetailFromXml($ico);
            JsonResponse::sendResponse($result);
        }
        catch (SearchModelException|DetailModelException $e) {
            $e->sendErrorJson();           
        }
    }
}
