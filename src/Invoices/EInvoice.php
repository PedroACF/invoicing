<?php
namespace PedroACF\Invoicing\Invoices;

use DOMDocument;

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

    public function getSignedInvoiceXml(){
        //https://siatinfo.impuestos.gob.bo/index.php/facturacion-en-linea/firma-digital/firma-digital
        //generate xml without signature
        $baseXml = $this->toXml();
        //01. canonicalization
        $canonicalization = $baseXml->C14N();
        //02. hash on sha256
        $hash_sha256 = hash('sha256', $canonicalization);
        //03. base64
        $encoded = base64_encode($hash_sha256);
        //04. add signature tags
        //05. add step 3 value to digestValue
        $signature = $this->getSignatureTag($baseXml, $encoded);
        $rootNode = $baseXml->getElementsByTagName($this->rootName);
        if(count($rootNode)>0){
            $rootNode[0]->appendChild($signature);
        }
        //
        dump($baseXml->saveXML());
        dd($encoded);
    }

    public function getGraphicalInvoice(){

    }

    private function getSignatureTag(DOMDocument $xmlInstance, $value){
        $signature = $xmlInstance->createElement("Signature");
        $attr = $xmlInstance->createAttribute("xmlns");
        $attr->value = "http://www.w3.org/2000/09/xmldsig#";
        $signature->appendChild($attr);

        $signedInfo = $xmlInstance->createElement("SignedInfo");

        $canonicalizationMethod = $xmlInstance->createElement("CanonicalizationMethod");
        $canonicalizationAttr = $xmlInstance->createAttribute("Algorithm");
        $canonicalizationAttr->value = "http://www.w3.org/TR/2001/REC-xml-c14n-20010315";
        $canonicalizationMethod->appendChild($canonicalizationAttr);
        $signedInfo->appendChild($canonicalizationMethod);

        $signatureMethod = $xmlInstance->createElement("SignatureMethod");
        $signatureAttr = $xmlInstance->createAttribute("Algorithm");
        $signatureAttr->value = "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256";
        $signatureMethod->appendChild($signatureAttr);
        $signedInfo->appendChild($signatureMethod);

        $reference = $xmlInstance->createElement("Reference");
        $referenceAttr = $xmlInstance->createAttribute("URI");
        $referenceAttr->value = "";
        $reference->appendChild($referenceAttr);

        $transforms = $xmlInstance->createElement("Transforms");

        $transform = $xmlInstance->createElement("Transform");
        $transformAttr = $xmlInstance->createAttribute("Algorithm");
        $transformAttr->value = "http://www.w3.org/2000/09/xmldsig#enveloped-signature";
        $transform->appendChild($transformAttr);
        $transforms->appendChild($transform);

        $transform = $xmlInstance->createElement("Transform");
        $transformAttr = $xmlInstance->createAttribute("Algorithm");
        $transformAttr->value = "http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments";
        $transform->appendChild($transformAttr);
        $transforms->appendChild($transform);

        $reference->appendChild($transforms);

        $digestMethod = $xmlInstance->createElement("DigestMethod");
        $digestMethodAttr = $xmlInstance->createAttribute("Algorithm");
        $digestMethodAttr->value = "http://www.w3.org/2001/04/xmlenc#sha256";
        $digestMethod->appendChild($digestMethodAttr);
        $reference->appendChild($digestMethod);

        //05. add step 3 value to digestValue
        $digestValue = $xmlInstance->createElement("DigestValue", $value);
        $reference->appendChild($digestValue);

        $signedInfo->appendChild($reference);

        $signature->appendChild($signedInfo);

        //
        $signatureValue = $xmlInstance->createElement("SignatureValue", "hola");
        $signature->appendChild($signatureValue);

        $keyInfo = $xmlInstance->createElement("KeyInfo");
        $x509Data = $xmlInstance->createElement("X509Data");
        $x509Certificate = $xmlInstance->createElement("X509Certificate", "----");
        $x509Data->appendChild($x509Certificate);
        $keyInfo->appendChild($x509Data);
        $signature->appendChild($keyInfo);
        return $signature;
    }
}
