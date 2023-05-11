<?php

namespace PedroACF\Invoicing\Repositories;

use PedroACF\Invoicing\Services\TokenService;
use SoapClient;

class SoapRepository
{
    protected $tokenService;
    public function __construct(TokenService $tokenService){
        $this->tokenService = $tokenService;
    }

    public function getClient(string $wsdl): SoapClient{
        $tokenModel = $this->tokenService->getDelegateToken();
        $token = $tokenModel->token;
        return new SoapClient($wsdl, [
            'stream_context' => stream_context_create([
                'http'=> [
                    'header' => "apikey: TokenApi $token"
                ]
            ]),
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
        ]);
    }
}
