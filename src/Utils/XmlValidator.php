<?php

namespace PedroACF\Invoicing\Utils;

use PedroACF\Invoicing\Exceptions\KeyException;
use PedroACF\Invoicing\Services\KeyService;

class XmlValidator
{
    private $xml;
    public function __construct($xmlContent)
    {
        $this->xml = new \DOMDocument();
        $this->xml->loadXML($xmlContent);
    }

    public function validate(){
        $schema = config("siat_invoicing.main_schema");
//        dump(__DIR__.'/../Schemas/'.$schema.".xsd");
        $result = $this->xml->schemaValidate(__DIR__.'/../Schemas/'.$schema.".xsd");
//        dump($result);
//        dump(libxml_get_errors());
//        dd("hola mundo");
    }
}
