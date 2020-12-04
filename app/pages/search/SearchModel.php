<?php

namespace App\pages\search;
use App\Conf\Models;
use App\utils\DomExtended;
use DOMDocument;
use DOMElement;
use App\conf\Exceptions\SearchModelException;

class SearchModel extends Models {
    protected $result = ['recordsFound' => 0, 'records' => []];
    
    /**
     * @var DOMDocument
     */
    protected $dom;
    
    protected $recordNumber = 1;


    public function search(string $name) {
        @$answer = file_get_contents('http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?obch_jm=' . $name);
        $this->dom = new DOMDocument;
        $domExt = new DomExtended();
        @$xmlLoaded = $this->dom->loadXML($answer);
        if (!$xmlLoaded) {
            throw new SearchModelException('no xml response received', 500);
        }
        $recordsFound = $domExt->firstTagValue($this->dom, 'Pocet_zaznamu');
        $this->result['recordsFound'] = intval($recordsFound);
        if ($recordsFound == 0) {
            return $this->result;
        }
        if ($recordsFound == -1 or $recordsFound > 400) {
            throw new SearchModelException('too much records returned', 999);
        }
        
        $records = $domExt->searchByTagChain($this->dom, ['Ares_odpovedi', 'Odpoved'])->getElementsByTagName('S');        
        foreach ($records as $record) {
            $this->result['records'][] = $this->parseRecord($record);
            $this->recordNumber++;
        }
        return $this->result;
    }
    
    public function parseRecord(DOMElement $record) {        
        $domExt = new DomExtended();
        $result = [
            'n' => $this->recordNumber, 
            'ico' => $domExt->firstTagValue($record, 'ico'), 
            'name' => $domExt->firstTagValue($record, 'ojm'), 
            'address' => $domExt->firstTagValue($record, 'jmn')
        ];        
        return $result;
    }
}
