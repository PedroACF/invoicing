<?php

namespace PedroACF\Invoicing\Repositories;

use PedroACF\Invoicing\Exceptions\SoapException;
use PedroACF\Invoicing\Requests\Code\CufdRequest;
use PedroACF\Invoicing\Requests\Code\VerificarNitRequest;
use PedroACF\Invoicing\Responses\Code\CodeComunicacionResponse;
use PedroACF\Invoicing\Responses\Code\CufdResponse;
use PedroACF\Invoicing\Requests\Code\CuisRequest;
use PedroACF\Invoicing\Responses\Code\CuisResponse;
use SoapFault;

class CodeRepository
{
    protected $client;

    public function __construct()
    {
        $wsdl = config("pacf_invoicing.endpoints.obtencion_codigos");
        $this->client = app()->call(function(SoapRepository $soap) use ($wsdl){
            return $soap->getClient($wsdl);
        });
    }

    public function cufd(CufdRequest $req): CufdResponse{
        try{
            $response = $this->client->cufd( $req->toArray() );
            return CufdResponse::build($response);
        }catch(SoapFault $ex){
            throw new SoapException($ex->getMessage());
        }

    }

    public function cuis(CuisRequest $req): CuisResponse{
        try{
            $response = $this->client->cuis( $req->toArray() );
            return CuisResponse::build($response);
        }catch (SoapFault $ex){
            throw new SoapException($ex->getMessage());
        }
    }

    /*public function notificaCertificadoRevocado(){
        return $this->client->call('notificaCertificadoRevocado');
    }*/

    public function checkNit(VerificarNitRequest $req){
        try{
            return $this->client->verificarNit($req->toArray());
        }catch (SoapFault $ex){
            throw new SoapException($ex->getMessage());
        }
    }

    public function checkConnection(): CodeComunicacionResponse{
        try{
            $response = $this->client->verificarComunicacion();
            return CodeComunicacionResponse::build($response);
        }catch (SoapFault $ex){
            dump($ex);
            throw new SoapException($ex->getMessage());
        }
    }
}
