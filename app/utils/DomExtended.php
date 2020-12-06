<?php

namespace App\utils;
use App\conf\Exceptions\DomException;

class DomExtended {
    //bool $tolerateNullResult - if no node is found, returns null instead of exception
    //DomDocument|DomElement $dom
    public function firstTagValue($dom, string $tagName, bool $tolerateNullResult = false) {
        $xml = $dom->getElementsByTagName($tagName);
        if ($xml->length === 0) {
            if ($tolerateNullResult) {
                return null;
            }
            throw new DomException("tagname {$tagName} not found");
        }
        return $xml->item(0)->nodeValue;
    }
    
    //DomDocument|DomElement $dom
    public function searchByTagChain($dom, array $tagChain, bool $tolerateNullResult = false) {
        $firstIter = true;
        foreach ($tagChain as $tagName) {
            if ($firstIter) {
                $result = $dom->getElementsByTagName($tagName);
                $firstIter = false;
            }
            else {
                $result = $result->getElementsByTagName($tagName);
            }
            if ($result->length === 0) {
                if ($tolerateNullResult) {
                    return null;
                }
                throw new DomException("tagname {$tagName} not found");
            }
            $result = $result->item(0);
        }
        return $result;
    }
}
