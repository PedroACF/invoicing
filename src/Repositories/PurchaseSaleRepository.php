<?php

namespace PedroACF\Invoicing\Repositories;
use PedroACF\Invoicing\Requests\PurchaseSale\AnulacionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionPaqueteFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\ValidacionRecepcionPaqueteRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\VerificacionEstadoFacturaRequest;
use PedroACF\Invoicing\Responses\PurchaseSale\PurchaseSaleComunicacionResponse;
use PedroACF\Invoicing\Responses\PurchaseSale\ServicioFacturacionResponse;
use PedroACF\Invoicing\Utils\TokenUtils;

class PurchaseSaleRepository
{
    protected $client;
    public function __construct()
    {
        $wsdl = config("pacf_invoicing.endpoints.compra_venta");
        $this->client = app()->call(function(SoapRepository $soap) use ($wsdl){
            return $soap->getClient($wsdl);
        });
    }

    //anulacionFactura
    public function cancelInvoice(AnulacionFacturaRequest $req): ServicioFacturacionResponse{
        $response = $this->client->anulacionFactura($req->toArray());
        return ServicioFacturacionResponse::build($response);
    }

    //recepcionAnexos
    public function sendAnnexes(){
        $response = $this->client->recepcionAnexos();
    }

    //recepcionFactura
    public function sendInvoice(RecepcionFacturaRequest $req): ServicioFacturacionResponse{
        $response = $this->client->recepcionFactura( $req->toArray() );
        return ServicioFacturacionResponse::build($response);
    }

    //recepcionMasivaFactura
    public function sendMassiveInvoice(){
        $response = $this->client->recepcionMasivaFactura();
    }

    //recepcionPaqueteFactura
    public function sendInvoicePackage(RecepcionPaqueteFacturaRequest $request): ServicioFacturacionResponse{
        $response = $this->client->recepcionPaqueteFactura($request->toArray());
        return ServicioFacturacionResponse::build($response);
    }

    //validacionRecepcionMasivaFactura
    public function validateMassiveInvoiceSend(){
        $response = $this->client->validacionRecepcionMasivaFactura();
    }

    //validacionRecepcionPaqueteFactura
    public function validateInvoicePackageSend(ValidacionRecepcionPaqueteRequest $request): ServicioFacturacionResponse{
        $response = $this->client->validacionRecepcionPaqueteFactura($request->toArray());
        return ServicioFacturacionResponse::build($response);
    }

    //verificacionEstadoFactura
    public function checkInvoiceStatus(VerificacionEstadoFacturaRequest $req){
        $response = $this->client->verificacionEstadoFactura($req->toArray());
        return $response;
    }

    public function checkConnection(): PurchaseSaleComunicacionResponse{
        $response = $this->client->verificarComunicacion();
        return PurchaseSaleComunicacionResponse::build($response);
    }
}
