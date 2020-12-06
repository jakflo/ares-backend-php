<?php

namespace App\utils;

class Date_tools {
    public function en_date_na_cz(string $en_date, bool $without_year = false) {
        $date = strtotime($en_date);
        $format = $without_year? 'd.m.':'d.m.Y';
        return date($format, $date);
    }
    
    public function en_datetime_na_cz(string $en_date, bool $without_year = false) {
        $en_date = trim($en_date);
        $en_date_arr = explode(' ', $en_date);
        $date = $this->en_date_na_cz($en_date_arr[0], $without_year);
        return "{$date} {$en_date_arr[1]}";
    }
}
