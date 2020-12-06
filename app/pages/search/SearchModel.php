<?php

namespace App\pages\search;
use App\Conf\Models;
use App\utils\DomExtended;
use DOMElement;
use App\conf\Exceptions\SearchModelException;

class SearchModel extends Models {
    protected $result = ['recordsFound' => 0, 'records' => []];    
    protected $recordNumber = 1;

    public function search(string $name) {
        $this->loadApiXml('http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi?obch_jm=' . $name . '&maxpoc=400');
        $domExt = new DomExtended();                
        $recordsFound = $domExt->firstTagValue($this->dom, 'Pocet_zaznamu');
        $this->result['recordsFound'] = intval($recordsFound);
        
        $tooMuchRecordsText = 'Zadané parametry vedou k výběru více subjektů než je zadáno v "Zobrazit vět". Upravte hlediska pro vyhledání.';
        if ($recordsFound == -1 or $recordsFound > 400 or $this->getApiError() === $tooMuchRecordsText ) {
            throw new SearchModelException('too much records returned', 999);
        }
        if ($recordsFound == 0) {
            return $this->result;
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
    
    public function getApiError() {
        $domExt = new DomExtended();
        $help = $domExt->searchByTagChain($this->dom, ['Ares_odpovedi', 'Odpoved', 'Help'], true);
        if ($help !== null) {
            return $domExt->firstTagValue($help, 'R', true);
        }
        return null;
    }
}
