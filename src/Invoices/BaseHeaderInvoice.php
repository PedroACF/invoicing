<?php
namespace PedroACF\Invoicing\Invoices;

use DOMDocument;

class BaseHeaderInvoice
{
    public function getXmlHeader(DOMDocument $xmlInstance){
        $xmlHead = $xmlInstance->createElement("cabecera");
        foreach($this as $key => $value){
            $xmlChild = $xmlInstance->createElement($key, $value);
            if($value == null){
                $xmlAttr = $xmlInstance->createAttribute("xsi:nil");
                $xmlAttr->value = "true";
                $xmlChild->appendChild($xmlAttr);
            }
            $xmlHead->appendChild($xmlChild);
        }
        return $xmlHead;
    }
}
