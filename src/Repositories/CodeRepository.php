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
    protected $client;

    public function __construct()
    {
        $wsdl = config("siat_invoicing.endpoints.obtencion_codigos");
        $this->client = app()->call('SoapRepository@getClient', $wsdl);
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

    public function checkNit(VerificarNitRequest $req){
        return $this->client->verificarNit($req->toArray());
    }

    public function checkConnection(): CodeComunicacionResponse{
        $response = $this->client->verificarComunicacion();
        return CodeComunicacionResponse::build($response);
    }
}
