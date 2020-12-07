<?php

namespace App\Utils;
use App\conf\Exceptions\Array_tool_exception;

class Array_tools {
    
    /*
     * prohledá 2d array a vrátí array, kde ve sloupcích $kde jsou hodoty $co;
     */
    public function hledej_ve_vicepoli(array $input, $co, string $kde, bool $zachovat_jmena_klicu = false) {
        $result = array();
        foreach ($input as $key => $row) {
            if ($row[$kde] == $co) {
                if ($zachovat_jmena_klicu) {
                    $result[$key] = $row;                    
                }
                else {
                    $result[] = $row;
                }
            }
        }
        return $result;
    }
    
    public function hledej_ve_vicepoli_multi(array $input, array $asoc_term, bool $zachovat_jmena_klicu = false) {
        $result = $input;
        foreach ($asoc_term as $kde => $co) {
            $result = $this->hledej_ve_vicepoli($result, $co, $kde, $zachovat_jmena_klicu);
        }
        return $result;
    }
    
    /*
     * převede vícepole na asoc. 1d pole, kde klíče jsou hodnoty sloupců $klic a hodnoty $hodnota
     */
    public function vicepole_na_value_asoc(array $input, string $klic, string $hodnota) {
        $result = array();
        foreach ($input as $row) {
            if (isset($row[$klic])) {
                $result[$row[$klic]] = $row[$hodnota];
            }
        }
        return $result;
    }
    
    /*
     * jak vicepole_na_value_asoc, ale hodnoty se stejným názvem klíče sčítá dohromady
     * tudíž hodnoty musí být čísla
     */
    public function vicepole_na_sum_asoc(array $input, string $klic, string $hodnota) {
        $result = array();
        foreach ($input as $row) {
            if (isset($row[$klic])) {
                $result[$row[$klic]] += $row[$hodnota];
            }
        }
        return $result;        
    }
    
    /*
     * převede vícepole na asoc. 2d pole, kde klíče jsou hodnoty sloupců $klic
     */
    public function vicepole_na_2d_asoc(array $input, string $klic) {
        $result = array();
        foreach ($input as $row) {
            if (isset($row[$klic])) {
                $result[$row[$klic]] = $row;
            }
        }
        return $result;
    }
    
    /*
     * převede pole dataobjektů (potomci Data_object) na 2d asoc pole
     * ACHTUNG!! vrací to pole NEošetřené na XSS
     */
    public function array_dataobjektu_na_2d_asoc(array $data_obj_array) {
        $result = array();
        foreach ($data_obj_array as $data_obj) {
            $result[] = $data_obj->get_source_array();
        }
        return $result;
    }
    
    /*
     * změní null hodnoty v poli na prázdné řetězce
     */
    public function null_na_empty_string(array $input) {
        foreach ($input as &$v) {
            $v = $v == null? '':$v;
        }
        return $input;
    }
    public function null_na_empty_string_vicepole(array $input) {
        foreach ($input as &$v) {
            $v = $this->null_na_empty_string($v);
        }
        return $input;
    }
    
    /*
     * imploduje prostý array do řetězce, tak aby jej šlo použit do IN() termu DB dotazu
     * @var quotes = zda má hodnoty ohraničit měkkýma úvozovkama
     * !!ACHTUNG!! Nepoužívat u hodnot náchylných k SQL injection (k tomu je Protected_in)
     */
    public function implode_pro_in(array $input, bool $quotes = false) {
        if (count($input) == 0) {
            throw new Array_tool_exception('Pole pro IN nemuze byt prazdne');
        }
        if (!$quotes) {
            return implode(',', $input);
        }
        else {
            return "'" . implode("','", $input) . "'";
        }        
    }
    
    public function asoc_na_uri(array $input) {
        $out = array();
        foreach ($input as $k=>$v) {
            $v = urlencode($v);
            $out[] = "{$k}={$v}";
        }
        return implode('&', $out);
    }
    
    /**
     * vytvoří kopii asoc. pole $input, ale kopíruje pouze členy s názvy klíčů obsažených v poli $klice
     */
    public function zkopiruj_pouze_nektere_cleny_asoc(array $input, array $klice) {
        $output = array();
        foreach ($input as $k => $v) {
            if (in_array($k, $klice)) {
                $output[$k] = $v;
            }
        }
        return $output;
    }
    
    public function trim_values(array $input) {
        foreach ($input as $k => &$val) {
            $val = trim($val);
        }
        return $input;        
    }
}
