<?php

namespace PedroACF\Invoicing\Repositories;

use PedroACF\Invoicing\Requests\Code\CufdRequest;
use PedroACF\Invoicing\Requests\Code\VerificarNitRequest;
use PedroACF\Invoicing\Responses\Code\CodeComunicacionResponse;
use PedroACF\Invoicing\Responses\Code\CufdResponse;
use PedroACF\Invoicing\Requests\Code\CuisRequest;
use PedroACF\Invoicing\Responses\Code\CuisResponse;
use PedroACF\Invoicing\Utils\TokenUtils;

class CodeRepository
{
    private $client;

    public function __construct()
    {
        $tokenReg = TokenUtils::getValidTokenReg();
        $wsdl = config("siat_invoicing.endpoints.obtencion_codigos");
        $token = $tokenReg->token;
        $this->client = new \SoapClient($wsdl, [
            'stream_context' => stream_context_create([
                'http'=> [
                    'header' => "apikey: TokenApi $token"
                ]
            ]),
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
        ]);
    }

    public function cufd(CufdRequest $req): CufdResponse{
        $response = $this->client->cufd( $req->toArray() );
        return CufdResponse::build($response);
    }

    public function cuis(CuisRequest $req): CuisResponse{
        $response = $this->client->cuis( $req->toArray());
        return CuisResponse::build($response);
    }

    /*public function notificaCertificadoRevocado(){
        return $this->client->call('notificaCertificadoRevocado');
    }*/

    public function verificarNit(VerificarNitRequest $req){
        return $this->client->verificarNit($req->toArray());
    }

    public function verificarComunicacion(): CodeComunicacionResponse{
        $response = $this->client->verificarComunicacion();
        return CodeComunicacionResponse::build($response);
    }
}
