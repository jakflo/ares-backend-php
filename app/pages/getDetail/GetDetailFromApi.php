<?php

namespace App\pages\getDetail;
use App\Conf\Models;
use App\utils\DomExtended;
use DOMElement;
use App\conf\Exceptions\DetailModelException;
use App\utils\Date_tools;

class GetDetailFromApi extends Models {    
    public function validateIco($ico) {
        if (empty($ico)) {
            throw new DetailModelException('empty query', 401);            
        }
        if (empty($ico) or !is_numeric($ico)) {
            throw new DetailModelException('Invalid input', 400);            
        }
    }
    
    public function getDetailFromXml(string $ico) {        
        $this->loadApiXml('http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=' . $ico);
        $domExt = new DomExtended;
        $error = $this->getApiError();
        if (trim($error) === 'Chyba 23 - chyba ic') {
            throw new DetailModelException('Invalid input', 400);
        }
        if (strpos($error, 'Chyba 71 - nenalezeno') !== false) {
            throw new DetailModelException('No records found', 404);
        }
        
        $result = ['ico' => trim($ico), 'source' => 'Ares']; // source = Ares, or local
        $answer = $domExt->searchByTagChain($this->dom, ['Ares_odpovedi', 'Odpoved', 'VBAS']);
        $result['dic'] = $domExt->firstTagValue($answer, 'DIC', true);
        $result['name'] = $domExt->firstTagValue($answer, 'OF', true);
        $result['datumVzniku'] = $this->enDateToCzIfNotNull($domExt->firstTagValue($answer, 'DV', true));
        $result['datumZaniku'] = $this->enDateToCzIfNotNull($domExt->firstTagValue($answer, 'DZ', true));
        $result['address'] = $this->parseAddress($domExt->searchByTagChain($answer, ['AA']));
        $result['fieldOfActivity'] = $this->parseFieldOfActivity($domExt->searchByTagChain($answer, ['Obory_cinnosti'], true));        
        return $result;
    }
    
    public function getApiError() {
        $domExt = new DomExtended();
        $e = $domExt->searchByTagChain($this->dom, ['Ares_odpovedi', 'Odpoved', 'E'], true);
        if ($e !== null) {
            return $domExt->firstTagValue($e, 'ET', true);
        }
        return null;
    }
    
    public function enDateToCzIfNotNull($date) {
        if ($date === null) {
            return null;
        }
        $dateTools = new Date_tools;
        return $dateTools->en_date_na_cz($date);
    }
    
    public function parseAddress(DOMElement $address) {
        $domExt = new DomExtended;
        $zipCzech = $domExt->firstTagValue($address, 'PSC', true);
        $zipForeign = $domExt->firstTagValue($address, 'Zahr_PSC', true);
        $zip = $zipCzech === null ? $zipForeign : $zipCzech;
        return [
            'aresAddressId' => $domExt->firstTagValue($address, 'IDA'), 
            'stat' => $domExt->firstTagValue($address, 'NS', true), 
            'okres' => $domExt->firstTagValue($address, 'NOK', true), 
            'obec' => $domExt->firstTagValue($address, 'N', true), 
            'nazevCastiObce' => $domExt->firstTagValue($address, 'NCO', true), 
            'mestskaCast' => $domExt->firstTagValue($address, 'NMC', true), 
            'ulice' => $domExt->firstTagValue($address, 'NU', true), 
            'cisloPopisne' => $domExt->firstTagValue($address, 'CD', true), 
            'cisloOrientacni' => $domExt->firstTagValue($address, 'CO', true), 
            'psc' => $zip            
        ];
    }
    
    /**
     * @param DomElement|null $fieldOfActivity
     * @return array
     */
    public function parseFieldOfActivity($fieldOfActivity) {
        if ($fieldOfActivity === null) {
            return [];
        }
        $domExt = new DomExtended;
        $result = [];
        foreach ($fieldOfActivity->getElementsByTagName('Obor_cinnosti') as $activity) {
            $result[] = [
                'id' => $domExt->firstTagValue($activity, 'K'), 
                'name' => $domExt->firstTagValue($activity, 'T')
            ];
        }
        return $result;
    }
}
