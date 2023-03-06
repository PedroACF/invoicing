<?php

namespace PedroACF\Invoicing\Repositories;

use App\Utils\Facturator\Requests\Code\CufdRequest;
use App\Utils\Facturator\Requests\Code\CuisRequest;

class CodeRepository
{
    private $client;

    public function __construct()
    {
//        $this->client = Soap::withHeaders([
//            'apikey' => "TokenApi $token",
//        ])->withOptions([
//            'trace' => true,
//        ])->baseWsdl(config("siat_invoicing.endpoints.obtencion_codigos"));

        $wsdl = config("siat_invoicing.endpoints.obtencion_codigos");
        $token = config("siat_invoicing.token");
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

    public function cufd(CufdRequest $req){
        return $this->client->cufd( $req->toArray() );
    }

    public function cuis(CuisRequest $req){
        return $this->client->cuis( $req->toArray());
    }

    public function notificaCertificadoRevocado(){
        return $this->client->call('notificaCertificadoRevocado');
    }

    public function verificarNit(){
        return $this->client->call('verificarNit');
    }

    public function verificarComunicacion(){
        return $this->client->verificarComunicacion();
    }
}
