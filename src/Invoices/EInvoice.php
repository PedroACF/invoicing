<?php
namespace PedroACF\Invoicing\Invoices;

use DOMDocument;
use PedroACF\Invoicing\Utils\XmlSigner;

class EInvoice
{
    private $rootName = '';
    public $header;
    public $details = [];

    public function __construct($root, HeaderEInvoice $header)
    {
        $this->rootName = $root;
        $this->header = $header;
    }

    public function addDetail(BaseDetailInvoice $detail){
        $this->details[] = $detail;
    }

    public function clearDetails(){
        $this->details = [];
    }

    public function toXml(): DOMDocument{
        $xml = new DOMDocument('1.0', "UTF-8");

        $xmlRoot = $xml->createElement($this->rootName);

        $xmlAttr = $xml->createAttribute("xmlns:xsi");
        $xmlAttr->value = "http://www.w3.org/2001/XMLSchema-instance";
        $xmlRoot->appendChild($xmlAttr);

        $xmlAttr = $xml->createAttribute("xsi:noNamespaceSchemaLocation");
        $xmlAttr->value = $this->rootName.".xsd";
        $xmlRoot->appendChild($xmlAttr);

        $xmlRoot->appendChild($this->header->getXmlHeader($xml));

        /* @var $detail BaseDetailInvoice */
        foreach($this->details as $detail){
            $xmlRoot->appendChild($detail->getXmlDetail($xml));
        }

        $xml->appendChild( $xmlRoot );
        return $xml;
    }

    public function getSignedInvoiceXml(): string{
        $xml = $this->toXml();
        $signer = new XmlSigner($xml);
        return $signer->sign();
    }

    public function getGraphicalInvoice(){

    }
}
