<?php

namespace App\Utils;
use App\conf\Exceptions\Array_tool_exception;

/*
 * slouží k přípravě polí ke vložení do IN termu v DB dotazu, kde je riziko SQL útoku
 * vytvoří to string s tokeny s daným prefixem a asoc. pole s daty pro prepared statements
 */
class Protected_in {
    protected $prefixes = array();
    protected $data = array();
    
    // $data je prostý 1D array
    public function add_array(string $prefix, array $data) {
        if (count($data) == 0) {
            throw new Array_tool_exception('Pole pro IN nemuze byt prazdne');
        }
        $c = isset($this->prefixes[$prefix])? $this->prefixes[$prefix] : 0;
        foreach ($data as $val) {
            $this->data[":{$prefix}{$c}"] = $val;
            $c++;
            $this->prefixes[$prefix]++;            
        }
    }
    
    public function get_tokens(string $prefix) {
        if (!isset($this->prefixes[$prefix])) {
            throw new Array_tool_exception("Prefix {$prefix} nenalezen");
        }
        else {
            $tokens = array();
            for ($c = 0; $c < $this->prefixes[$prefix]; $c++) {
                $tokens[] = ":{$prefix}{$c}";
            }
            return implode(',', $tokens);
        }
    }
    
    public function get_data() {
        return $this->data;        
    }    
}
