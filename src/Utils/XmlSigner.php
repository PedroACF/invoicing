<?php

namespace PedroACF\Invoicing\Utils;

use PedroACF\Invoicing\Exceptions\KeyException;
use PedroACF\Invoicing\Services\KeyService;

class XmlSigner
{
    private $keyService;
    public function __construct()
    {
        $this->keyService = new KeyService();
    }

    public function sign(string $xmlString): ?string{
        $privateKeyEntity = $this->keyService->getAvailablePrivateKey();
        if($privateKeyEntity==null){
            throw new KeyException();
        }
        $publicCert = $this->keyService->getPublicCert();
        if($publicCert==null){
            throw new KeyException();
        }

        $algUrl = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
        $algDigUrl = 'http://www.w3.org/2001/04/xmlenc#sha256';
        $algDig = 'RSA-SHA256';
        $alg = OPENSSL_ALGO_SHA256;

        $xml = new \DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = false;
        $xml->xmlStandalone = false;
        $xml->loadXML($xmlString);

        // Whitespaces must be preserved
        $rootElement = $xml->documentElement;
        $canonicalData = $rootElement->C14N(false, true);
        $digest = openssl_digest($canonicalData, $algDig, true);
        $digest64 = base64_encode($digest);
        //$publicCert = openssl_pkey_get_public($publicCert);

        $signatureElement = $xml->createElement('Signature');
        $signatureElement->setAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');
        $xml->documentElement->appendChild($signatureElement);

        $signedInfoElement = $xml->createElement('SignedInfo');
        $signatureElement->appendChild($signedInfoElement);

        $canonicalizationMethodElement = $xml->createElement('CanonicalizationMethod');
        $canonicalizationMethodElement->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfoElement->appendChild($canonicalizationMethodElement);

        $signatureMethodElement = $xml->createElement('SignatureMethod');
        $signatureMethodElement->setAttribute(
            'Algorithm',
            $algUrl
        );
        $signedInfoElement->appendChild($signatureMethodElement);

        $referenceElement = $xml->createElement('Reference');
        $referenceElement->setAttribute('URI', '');
        $signedInfoElement->appendChild($referenceElement);

        $transformsElement = $xml->createElement('Transforms');
        $referenceElement->appendChild($transformsElement);

        $transformElement = $xml->createElement('Transform');
        $transformElement->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transformsElement->appendChild($transformElement);

        $transformElement = $xml->createElement('Transform');
        $transformElement->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments');
        $transformsElement->appendChild($transformElement);

        $digestMethodElement = $xml->createElement('DigestMethod');
        $digestMethodElement->setAttribute('Algorithm', $algDigUrl);
        $referenceElement->appendChild($digestMethodElement);

        $digestValueElement = $xml->createElement('DigestValue', $digest64);
        $referenceElement->appendChild($digestValueElement);

        $signatureValueElement = $xml->createElement('SignatureValue', '');
        $signatureElement->appendChild($signatureValueElement);

        $c14nSignedInfo = $signedInfoElement->C14N(false, false);

        $signature = '';

        $privateKeyContent = stream_get_contents($privateKeyEntity->content);
        $privateKey = openssl_pkey_get_private($privateKeyContent);

        openssl_sign($c14nSignedInfo, $signature, $privateKey, $alg);

        $xpath = new \DOMXpath($xml);
        $nodeList = $xpath->query('//SignatureValue', $signatureElement);
        $signatureValueElement = $nodeList->item(0);
        $signatureValueElement->nodeValue = base64_encode($signature);

        $keyInfoElement = $xml->createElement('KeyInfo');
        $signatureElement->appendChild($keyInfoElement);

        $keyValueElement = $xml->createElement('X509Data');
        $keyInfoElement->appendChild($keyValueElement);

        $regexPattern = '/'.'-----BEGIN CERTIFICATE-----'.'(.+)'.'-----END CERTIFICATE-----'.'/Us';
        preg_match($regexPattern, $publicCert, $matches);
        $publicCert =  str_replace(["\r\n", "\n"], '', trim($matches[1]));

        $rsaKeyValueElement = $xml->createElement('X509Certificate', $publicCert);
        $keyValueElement->appendChild($rsaKeyValueElement);

        return $xml->saveXML();
    }
}
