<?php

namespace PedroACF\Invoicing\Repositories;
use PedroACF\Invoicing\Requests\Operation\EventoSignificativoRequest;
use PedroACF\Invoicing\Responses\Operation\ListaEventosResponse;
use PedroACF\Invoicing\Responses\Operation\OperationComunicacionResponse;
use PedroACF\Invoicing\Utils\TokenUtils;

class OperationRepository
{
    protected $client;
    public function __construct()
    {
        $wsdl = config("pacf_invoicing.endpoints.operaciones");
        $this->client = app()->call(function(SoapRepository $soap) use ($wsdl){
            return $soap->getClient($wsdl);
        });
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
    public function addSignificantEvent(EventoSignificativoRequest $request): ListaEventosResponse{
        $response = $this->client->registroEventoSignificativo($request->toArray());
        dump($response);
        return ListaEventosResponse::build($response);
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
