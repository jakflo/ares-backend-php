<?php

namespace App\Pages\pageNotFound;
use App\Conf\Controllers;

class PageNotFoundController extends Controllers {
    
    public function zobraz() {
        $this->send400();
    }
}
