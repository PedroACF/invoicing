<?php

namespace PedroACF\Invoicing\Utils;

use PedroACF\Invoicing\Exceptions\KeyException;
use PedroACF\Invoicing\Services\KeyService;

class XmlSigner
{
    public $xml;
    private $keyService;
    public function __construct(\DOMDocument $xml)
    {
        $this->xml = $xml;
        $this->keyService = new KeyService();
    }

    public function sign(): ?string{
        $privateKeyEntity = $this->keyService->getAvailablePrivateKey();
        if($privateKeyEntity==null){
            throw new KeyException();
        }
        $publicPlainCert = $this->keyService->getPublicKeyPlainText();
        if($publicPlainCert==null){
            throw new KeyException();
        }

        $rootElement = $this->xml->documentElement;
        $canonicalData = $rootElement->C14N(true, false);
        $digest = openssl_digest($canonicalData, 'sha256', true);
        $digest64 = base64_encode($digest);

        $signatureElement = $this->xml->createElement('Signature');
        $signatureElement->setAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');
        $this->xml->documentElement->appendChild($signatureElement);

        $signedInfoElement = $this->xml->createElement('SignedInfo');
        $signatureElement->appendChild($signedInfoElement);

        $canonicalizationMethodElement = $this->xml->createElement('CanonicalizationMethod');
        $canonicalizationMethodElement->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfoElement->appendChild($canonicalizationMethodElement);

        $signatureMethodElement = $this->xml->createElement('SignatureMethod');
        $signatureMethodElement->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $signedInfoElement->appendChild($signatureMethodElement);

        $referenceElement = $this->xml->createElement('Reference');
        $referenceElement->setAttribute('URI', '');
        $signedInfoElement->appendChild($referenceElement);

        $transformsElement = $this->xml->createElement('Transforms');
        $referenceElement->appendChild($transformsElement);

        $transformElement = $this->xml->createElement('Transform');
        $transformElement->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transformsElement->appendChild($transformElement);

        $transformElement = $this->xml->createElement('Transform');
        $transformElement->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments');
        $transformsElement->appendChild($transformElement);

        $digestMethodElement = $this->xml->createElement('DigestMethod');
        $digestMethodElement->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $referenceElement->appendChild($digestMethodElement);

        $digestValueElement = $this->xml->createElement('DigestValue', $digest64);
        $referenceElement->appendChild($digestValueElement);

        $signatureValueElement = $this->xml->createElement('SignatureValue', '');
        $signatureElement->appendChild($signatureValueElement);

        $c14nSignedInfo = $signedInfoElement->C14N(true, false);

        $signature = '';
        //========== SIGN WITH PRIVATE
        $privateKey = openssl_pkey_get_private($privateKeyEntity->content);
        openssl_sign($c14nSignedInfo, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $xpath = new \DOMXPath($this->xml);
        $nodeList = $xpath->query('//SignatureValue', $signatureElement);
        $signatureValueElement = $nodeList->item(0);
        $signatureValueElement->nodeValue = base64_encode($signature);

        $keyInfoElement = $this->xml->createElement('KeyInfo');
        $signatureElement->appendChild($keyInfoElement);

        $keyValueElement = $this->xml->createElement('X509Data');
        $keyInfoElement->appendChild($keyValueElement);

        //======== PUBLIC CERT
        $rsaKeyValueElement = $this->xml->createElement('X509Certificate', $publicPlainCert);
        $keyValueElement->appendChild($rsaKeyValueElement);

        return $this->xml->saveXML();
    }
}
