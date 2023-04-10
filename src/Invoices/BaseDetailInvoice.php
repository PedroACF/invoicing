<?php
namespace PedroACF\Invoicing\Invoices;

use DOMDocument;

class BaseDetailInvoice
{
    public function getXmlDetail(DOMDocument $xmlInstance){
        $xmlDetail = $xmlInstance->createElement("detalle");
        foreach($this as $key => $value){
            $xmlChild = $xmlInstance->createElement($key, $value);
            if($value === null){
                $xmlAttr = $xmlInstance->createAttribute("xsi:nil");
                $xmlAttr->value = "true";
                $xmlChild->appendChild($xmlAttr);
            }
            $xmlDetail->appendChild($xmlChild);
        }
        return $xmlDetail;
    }
}
