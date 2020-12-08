<?php

namespace App\pages\getDetail;
use App\Conf\Controllers;
use App\utils\JsonResponse;
use App\conf\Exceptions\DetailModelException;
use App\pages\getDetail\GetDetailFromApi;
use App\pages\getDetail\GetDetailFromDb;

class GetDetailController extends Controllers {
    public function zobraz() {
        if (empty($this->params[0])) {
            JsonResponse::sendClientError(['error' => 'empty query']);
            return false;
        }
        $api = new GetDetailFromApi($this->env);
        $dbModel = new GetDetailFromDb($this->env);
        if (isset($_GET['forceAres']) and $_GET['forceAres'] == 1) {
            $forceAres = true;
        }
        else {
            $forceAres = false;
        }
        
        try {
            $ico = trim($this->params[0]);
            $api->validateIco($ico);
            if (!$forceAres and $dbModel->isCompanyStored($ico, 30)) {
                $result = $dbModel->getDetail($ico);
            }
            else {
                $result = $api->getDetailFromXml($ico);
            }            
            JsonResponse::sendResponse($result);
        }
        catch (SearchModelException|DetailModelException $e) {
            $e->sendErrorJson();           
        }
    }
}
