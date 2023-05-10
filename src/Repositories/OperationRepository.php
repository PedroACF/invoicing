<?php

namespace PedroACF\Invoicing\Repositories;
use PedroACF\Invoicing\Responses\Operation\OperationComunicacionResponse;
use PedroACF\Invoicing\Utils\TokenUtils;

class OperationRepository
{
    protected $client;
    public function __construct()
    {
        $wsdl = config("siat_invoicing.endpoints.operaciones");
        $this->client = app()->call('SoapRepository@getClient', $wsdl);
    }

    //cierreOperacionesSistema
    public function closeOperations(){
        $response = $this->client->cierreOperacionesSistema();
    }

    //cierrePuntoVenta
    public function closeSalePoint(){
        $response = $this->client->cierrePuntoVenta();
    }

    //consultaEventoSignificativo
    public function checkSignificantEvent(){
        $response = $this->client->consultaEventoSignificativo();
    }

    //consultaPuntoVenta
    public function checkSalePoint(){
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
//    public function addComissionSalePoint(){
//        $response = $this->client->registroPuntoVentaComisionista();
//    }

    //verificarComunicacion
    public function checkConnection(): OperationComunicacionResponse{
        $response = $this->client->verificarComunicacion();

        return OperationComunicacionResponse::build($response);
    }


}
