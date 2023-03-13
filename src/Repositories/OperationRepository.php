<?php

namespace App\Utils\facturator\Services;
use PedroACF\Invoicing\Responses\Operation\OperationComunicacionResponse;
use PedroACF\Invoicing\Utils\TokenUtils;

class OperationRepository
{
    public function __construct()
    {
        $tokenReg = TokenUtils::getValidTokenReg();
        $wsdl = config("siat_invoicing.endpoints.operaciones");
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

    //cierreOperacionesSistema
    public function closeSystemOperation(){
        $response = $this->client->cierreOperacionesSistema();
    }

    //cierrePuntoVenta
    public function closeSalePoint(){
        $response = $this->client->cierrePuntoVenta();
    }

    //consultaEventoSignificativo
    public function querySignificantEvent(){
        $response = $this->client->consultaEventoSignificativo();
    }

    //consultaPuntoVenta
    public function querySalePoint(){
        $response = $this->client->consultaPuntoVenta();
    }

    //registroEventoSignificativo
    public function addSignificantEvent(){
        $response = $this->client->registroEventoSignificativo();
    }

    //registroPuntoVenta
    public function addSalePoint(){
        $response = $this->client->registroPuntoVenta();
    }

    //registroPuntoVentaComisionista
    public function addComissionSalePoint(){
        $response = $this->client->registroPuntoVentaComisionista();
    }

    //verificarComunicacion
    public function verificarComunicacion(): OperationComunicacionResponse{
        $response = $this->client->verificarComunicacion();

        return OperationComunicacionResponse::build($response);
    }


}
