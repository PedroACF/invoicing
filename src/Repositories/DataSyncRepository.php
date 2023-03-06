<?php
namespace PedroACF\Invoicing\Repositories;

use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;
class DataSyncRepository
{
    private $client;

    public function __construct()
    {
//        $this->client = Soap::withHeaders([
//            'apikey' => "TokenApi $token",
//        ])->withOptions([
//            'trace' => true,
//        ])->baseWsdl(config("siat_invoicing.endpoints.obtencion_codigos"));
        $wsdl = config("siat_invoicing.endpoints.sincronizacion_datos");
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



//sincronizarFechaHora
//sincronizarListaActividadesDocumentoSector
//sincronizarListaLeyendasFactura
//sincronizarListaMensajesServicios
//sincronizarListaProductosServicios
//sincronizarParametricaEventosSignificativos
//sincronizarParametricaMotivoAnulacion
//sincronizarParametricaPaisOrigen
//sincronizarParametricaTipoDocumentoIdentidad
//sincronizarParametricaTipoDocumentoSector
//sincronizarParametricaTipoEmision
//sincronizarParametricaTipoHabitacion
//sincronizarParametricaTipoMetodoPago
//sincronizarParametricaTipoMoneda
//sincronizarParametricaTipoPuntoVenta
//sincronizarParametricaTiposFactura

    //sincronizarParametricaUnidadMedida
    public function sincronizarParametricaUnidadMedida(){
        return $this->client->call('sincronizarParametricaUnidadMedida');
    }

    //sincronizarActividades
    public function sincronizarActividades(SincronizacionRequest $req){
        return $this->client->sincronizarActividades($req->toArray());
    }

    public function verificarComunicacion(){
        return $this->client->verificarComunicacion();
    }
}
