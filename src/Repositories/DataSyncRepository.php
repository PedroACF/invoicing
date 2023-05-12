<?php
namespace PedroACF\Invoicing\Repositories;

use PedroACF\Invoicing\Exceptions\SoapException;
use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;
use PedroACF\Invoicing\Responses\DataSync\DataSyncComunicacionResponse;
use PedroACF\Invoicing\Responses\DataSync\FechaHoraResponse;
use PedroACF\Invoicing\Responses\DataSync\ListaActividadesDocumentoSectorResponse;
use PedroACF\Invoicing\Responses\DataSync\ListaActividadesResponse;
use PedroACF\Invoicing\Responses\DataSync\ListaParametricasLeyendasResponse;
use PedroACF\Invoicing\Responses\DataSync\ListaParametricasResponse;
use PedroACF\Invoicing\Responses\DataSync\ListaProductosResponse;
use SoapFault;

class DataSyncRepository
{
    protected $client;

    public function __construct()
    {
        $wsdl = config("pacf_invoicing.endpoints.sincronizacion_datos");
        $this->client = app()->call(function(SoapRepository $soap) use ($wsdl){
            return $soap->getClient($wsdl);
        });
    }

    //sincronizarFechaHora
    public function getFechaHora(SincronizacionRequest $req): FechaHoraResponse{
        $response = $this->client->sincronizarFechaHora($req->toArray());
        return FechaHoraResponse::build($response);
    }

    //sincronizarActividades
    public function getActividades(SincronizacionRequest $req): ListaActividadesResponse{
        $response = $this->client->sincronizarActividades($req->toArray());
        return ListaActividadesResponse::build($response);
    }

    //sincronizarListaActividadesDocumentoSector
    public function getActividadesDocumentSector(SincronizacionRequest $req): ListaActividadesDocumentoSectorResponse{
        $response = $this->client->sincronizarListaActividadesDocumentoSector($req->toArray());
        return ListaActividadesDocumentoSectorResponse::build($response);
    }

    //sincronizarListaLeyendasFactura
    public function getParamLeyendas(SincronizacionRequest $req): ListaParametricasLeyendasResponse{
        $response = $this->client->sincronizarListaLeyendasFactura($req->toArray());
        return ListaParametricasLeyendasResponse::build($response);
    }

    //sincronizarListaMensajesServicios
    public function getParamMensajes(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarListaMensajesServicios($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarListaProductosServicios
    public function getProductos(SincronizacionRequest $req): ListaProductosResponse{
        $response = $this->client->sincronizarListaProductosServicios($req->toArray());
        return ListaProductosResponse::build($response);
    }

    //sincronizarParametricaEventosSignificativos
    public function getParamEventosSignificativos(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaEventosSignificativos($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaMotivoAnulacion
    public function getParamMotivosAnulacion(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaMotivoAnulacion($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaPaisOrigen
    public function getParamPaises(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaPaisOrigen($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTipoDocumentoIdentidad
    public function getParamTiposDocumentoIdentidad(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTipoDocumentoIdentidad($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTipoDocumentoSector
    public function getParamTiposDocumentoSector(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTipoDocumentoSector($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTipoEmision
    public function getParamTiposEmision(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTipoEmision($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTipoHabitacion
    public function getParamTiposHabitacion(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTipoHabitacion($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTipoMetodoPago
    public function getParamTiposMetodoPago(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTipoMetodoPago($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTipoMoneda
    public function getParamTiposMoneda(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTipoMoneda($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTipoPuntoVenta
    public function getParamTiposPuntoVenta(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTipoPuntoVenta($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaTiposFactura
    public function getParamTiposFactura(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaTiposFactura($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    //sincronizarParametricaUnidadMedida
    public function getParamUnidadMedida(SincronizacionRequest $req): ListaParametricasResponse{
        $response = $this->client->sincronizarParametricaUnidadMedida($req->toArray());
        return ListaParametricasResponse::build($response);
    }

    public function checkConnection(): DataSyncComunicacionResponse{
        try{
            $response = $this->client->verificarComunicacion();
            return DataSyncComunicacionResponse::build($response);
        }catch (SoapFault $ex){
            dump($ex);
            throw new SoapException($ex->getMessage());
        }
    }
}
