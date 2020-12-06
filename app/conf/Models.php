<?php

namespace App\Conf;
use DOMDocument;
use App\conf\Exceptions\SearchModelException;

class Models {
    protected $env;
    
    /**
     * @var DOMDocument
     */
    protected $dom;
    
    public function __construct(Env $env) {
        $this->env = $env;        
    }
    
    public function loadApiXml(string $url) {
        @$answer = file_get_contents($url);
        $this->dom = new DOMDocument;
        @$xmlLoaded = $this->dom->loadXML($answer);
        if (!$xmlLoaded) {
            throw new SearchModelException('no xml response received', 500);
        }        
    }
}
