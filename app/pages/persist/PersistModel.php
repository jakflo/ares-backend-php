<?php

namespace App\pages\persist;
use App\Conf\Models;
use App\Utils\Array_tools;
use App\utils\Date_tools;
use App\Utils\Protected_in;

class PersistModel extends Models {
    
    public function removeIfExists($ico) {
        $companyIdRow = $this->env->db->dotaz_radek(
                "select id from company where ico=?", 
                [$ico]
                );
        if (!$companyIdRow) {
            return null;
        }
        $companyId = $companyIdRow['id'];
        $this->env->db->sendSQL('delete from company where id='.$companyId);
        $this->env->db->sendSQL('delete from company_has_activity where company_id='.$companyId);
    }
    
    /**
     * stores compyny detail to DB
     * @param array $data = result from App\pages\getDetail\GetDetailFromApi::getDetailFromXml
     */
    public function persist(array $data) {
        $addressId = $this->env->db->dotaz_radek(
                "select id from address where ares_address_id=?", 
                [$data['address']['aresAddressId']]
                );
        if (!$addressId) {
            $addressId = $this->saveAddress($data['address']);
        }
        else {
            $addressId = $addressId['id'];
        }
        
        $dateTools = new Date_tools;      
        $this->env->db->sendSQL(
                "insert into company(ico, dic, name, datumVzniku, datumZaniku, address_id, dateSaved) "
                . "values(:ico, :dic, :name, :datumVzniku, :datumZaniku, :address_id, :dateSaved)", 
                    [
                        ':ico' => $data['ico'], 
                        ':dic' => $data['dic'], 
                        ':name' => $data['name'], 
                        ':datumVzniku' => $data['datumVzniku'] !== null? $dateTools->czDateToEn($data['datumVzniku']) : null, 
                        ':datumZaniku' => $data['datumZaniku'] !== null? $dateTools->czDateToEn($data['datumZaniku']) : null, 
                        ':address_id' => $addressId, 
                        ':dateSaved' => date('Y-m-d')
                    ]
                );
        $id = $this->env->db->get_last_id();
        
        $activitiesData = $data['fieldOfActivity'];
        if (count($activitiesData) > 0) {
            $this->saveMissingActivities($activitiesData);
            $this->saveCompanyActivities($id, $activitiesData);
        }
    }
    
    protected function saveAddress(array $addressData) {
        $this->env->db->sendSQL(
                "insert into address(ares_address_id, stat, okres, obec, "
                    . "nazevCastiObce, mestskaCast, ulice, cisloPopisne, cisloOrientacni, psc)"
                . "values(:ares_address_id, :stat, :okres, :obec, :nazevCastiObce, :mestskaCast, :ulice, "
                    . ":cisloPopisne, :cisloOrientacni, :psc)", 
                [
                    ':ares_address_id' => $addressData['aresAddressId'], 
                    ':stat' => $addressData['stat'], 
                    ':okres' => $addressData['okres'], 
                    ':obec' => $addressData['obec'], 
                    ':nazevCastiObce' => $addressData['nazevCastiObce'], 
                    ':mestskaCast' => $addressData['mestskaCast'], 
                    ':ulice' => $addressData['ulice'], 
                    ':cisloPopisne' => $addressData['cisloPopisne'], 
                    ':cisloOrientacni' => $addressData['cisloOrientacni'], 
                    ':psc' => $addressData['psc']   
                ]
                );
        return $this->env->db->get_last_id();
    }
    
    protected function saveMissingActivities(array $activitiesData) {
        $arrayTools = new Array_tools;
        $idsInData = array_column($activitiesData, 'id');
        $idsInDb = $this->env->db->dotaz_sloupec("SELECT id FROM activity", 'id');
        $missingIds = array_diff($idsInData, $idsInDb);
        if (count($missingIds) === 0) {
            return true;
        }
        
        foreach ($missingIds as $missingId) {
            $missingActivity = $arrayTools->hledej_ve_vicepoli($activitiesData, $missingId, 'id')[0];
            $this->env->db->sendSQL(
                    "insert into activity(id, name) values(:id, :name)", 
                    [
                        ':id' => $missingActivity['id'], 
                        ':name' => $missingActivity['name']
                    ]
                    );
        }
    }
    
    protected function saveCompanyActivities(int $companyId, array $activitiesData) {
        $protectedIn = new Protected_in;
        $valuesArray = [];
        $k = 0;
        foreach ($activitiesData as $activity) {
            $prefix = "v{$k}_";
            $protectedIn->add_array($prefix, [$companyId, $activity['id']]);
            $valuesArray[] = "({$protectedIn->get_tokens($prefix)})";
            $k++;
        }
        $values = implode(', ', $valuesArray);
        $this->env->db->sendSQL(
                "insert into company_has_activity(company_id, activity_id) values {$values}", 
                $protectedIn->get_data()
                );
    }
}
