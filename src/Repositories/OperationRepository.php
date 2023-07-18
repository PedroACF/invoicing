<?php

namespace PedroACF\Invoicing\Repositories;
use PedroACF\Invoicing\Requests\Operation\CierrePuntoVentaRequest;
use PedroACF\Invoicing\Requests\Operation\ConsultaEventoRequest;
use PedroACF\Invoicing\Requests\Operation\ConsultaPuntoVentaRequest;
use PedroACF\Invoicing\Requests\Operation\EventoSignificativoRequest;
use PedroACF\Invoicing\Requests\Operation\RegistroPuntoVentaRequest;
use PedroACF\Invoicing\Responses\Operation\CierrePuntoVentaResponse;
use PedroACF\Invoicing\Responses\Operation\ConsultaPuntoVentaResponse;
use PedroACF\Invoicing\Responses\Operation\ListaEventosResponse;
use PedroACF\Invoicing\Responses\Operation\OperationComunicacionResponse;
use PedroACF\Invoicing\Responses\Operation\RegistroPuntoVentaResponse;
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
    public function closeSalePoint(CierrePuntoVentaRequest $request): CierrePuntoVentaResponse{
        $response = $this->client->cierrePuntoVenta($request->toArray());
        return CierrePuntoVentaResponse::build($response);
    }

    //consultaEventoSignificativo
    public function getSignificantEvents(ConsultaEventoRequest $request): ListaEventosResponse{
        $response = $this->client->consultaEventoSignificativo($request->toArray());
        return ListaEventosResponse::build($response);
    }

    //consultaPuntoVenta
    public function checkSalePoints(ConsultaPuntoVentaRequest $request){
        $response = $this->client->consultaPuntoVenta($request->toArray());
        return ConsultaPuntoVentaResponse::build($response);
    }

    //registroEventoSignificativo
    public function addSignificantEvent(EventoSignificativoRequest $request): ListaEventosResponse{
        $response = $this->client->registroEventoSignificativo($request->toArray());
        return ListaEventosResponse::build($response);
    }

    //registroPuntoVenta
    public function addSalePoint(RegistroPuntoVentaRequest $request): RegistroPuntoVentaResponse{
        $response = $this->client->registroPuntoVenta($request->toArray());
        return RegistroPuntoVentaResponse::build($response);
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
