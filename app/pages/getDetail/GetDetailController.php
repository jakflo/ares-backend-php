<?php

namespace App\pages\getDetail;
use App\Conf\Controllers;

class GetDetailController extends Controllers {
    public function zobraz() {
        $this->sendResponse(['ico' => $this->params[0]]);
    }
}
