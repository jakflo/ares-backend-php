<?php

namespace App\pages\getDetail;
use App\Conf\Models;
use App\conf\Exceptions\DetailModelException;
use App\Utils\Array_tools;
use App\utils\Date_tools;

class GetDetailFromDb extends Models {

    /**
     * @param int $maxAge = max of of records in days, older will be ignored; set -1 to no limit
     */    
    public function getCompanyId(string $ico, int $maxAge = -1) {
        if ($maxAge > -1) {
            $minDateUnix = strtotime(date('Y-m-d')) - 86400 * $maxAge;
            $minDate = date('Y-m-d', $minDateUnix);
            $companyRow = $this->env->db->dotaz_radek(
                    "select id from company where ico=:ico and dateSaved>=:minDate", 
                    [':ico' => $ico, ':minDate' => $minDate]
                    );            
        }
        else {
            $companyRow = $this->env->db->dotaz_radek(
                    "select id from company where ico=?", 
                    [$ico]
                    );
        }
        if (!$companyRow) {
            return false;
        }
        return $companyRow['id'];
    }
    
    public function isCompanyStored(string $ico, int $maxAge = -1) {        
        if (!$this->getCompanyId($ico, $maxAge)) {
            return false;
        }
        return true;
    }
    
    public function getDetail(string $ico) {
        $companyId = $this->getCompanyId($ico);
        $arrayTools = new Array_tools;        
        if (!$companyId) {
            throw new DetailModelException('ICO not found', 404);
        }
        
        $basicInfoAndAddress = $this->env->db->dotaz_radek(
                "select ico, dic, name, datumVzniku, datumZaniku, dateSaved, "
                    . "ares_address_id, stat, okres, obec, nazevCastiObce, "
                    . "mestskaCast, ulice, cisloPopisne, cisloOrientacni, psc "
                . "from company c "
                . "join address a on c.address_id = a.id "
                . "where c.id=?", 
                [$companyId]
                );
        $activities = $this->env->db->dotaz_vse(
                "select a.id, a.name from company_has_activity cha "
                . "join activity a on cha.activity_id = a.id where cha.company_id=?", 
                [$companyId]
                );        

        $result = $arrayTools->zkopiruj_pouze_nektere_cleny_asoc(
                $basicInfoAndAddress, 
                ['ico', 'dic', 'name', 'datumVzniku', 'datumZaniku', 'dateSaved']
                );
        $this->dateToCz($result['datumVzniku']);
        $this->dateToCz($result['datumZaniku']);
        $this->dateToCz($result['dateSaved']);
        
        $result['address'] = $arrayTools->zkopiruj_pouze_nektere_cleny_asoc(
                $basicInfoAndAddress, 
                ['stat', 'okres', 'obec', 'nazevCastiObce', 'mestskaCast', 
                    'ulice', 'cisloPopisne', 'cisloOrientacni', 'psc']
                );
        $result['address']['aresAddressId'] = $basicInfoAndAddress['ares_address_id'];
        
        $result['fieldOfActivity'] = $activities;
        $result['source'] = 'localDb';
        return $result;
    }
    
    protected function dateToCz(&$dateRef) {
        $dateTools = new Date_tools;
        if ($dateRef !== null) {
            $dateRef = $dateTools->en_date_na_cz($dateRef);
        }                
    }
}
