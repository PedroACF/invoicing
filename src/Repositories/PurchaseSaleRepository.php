<?php

namespace PedroACF\Invoicing\Repositories;
use PedroACF\Invoicing\Requests\PurchaseSale\AnulacionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\VerificacionEstadoFacturaRequest;
use PedroACF\Invoicing\Responses\PurchaseSale\PurchaseSaleComunicacionResponse;
use PedroACF\Invoicing\Utils\TokenUtils;

class PurchaseSaleRepository
{
    public function __construct()
    {
        $tokenReg = TokenUtils::getValidTokenReg();
        $wsdl = config("siat_invoicing.endpoints.compra_venta");
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

    //anulacionFactura
    public function cancelInvoice(AnulacionFacturaRequest $req){
        $response = $this->client->anulacionFactura($req->toArray());
        return $response;
    }

    //recepcionAnexos
    public function sendAnnexes(){
        $response = $this->client->recepcionAnexos();
    }

    //recepcionFactura
    public function sendInvoice(RecepcionFacturaRequest $req){
        $response = $this->client->recepcionFactura( $req->toArray() );
        return $response;
    }

    //recepcionMasivaFactura
    public function sendMassiveInvoice(){
        $response = $this->client->recepcionMasivaFactura();
    }

    //recepcionPaqueteFactura
    public function sendInvoicePackage(){
        $response = $this->client->recepcionPaqueteFactura();
    }

    //validacionRecepcionMasivaFactura
    public function validateMassiveInvoiceSend(){
        $response = $this->client->validacionRecepcionMasivaFactura();
    }

    //validacionRecepcionPaqueteFactura
    public function validateInvoicePackageSend(){
        $response = $this->client->validacionRecepcionPaqueteFactura();
    }

    //verificacionEstadoFactura
    public function checkInvoiceStatus(VerificacionEstadoFacturaRequest $req){
        $response = $this->client->verificacionEstadoFactura($req->toArray());
        return $response;
    }

    public function verificarComunicacion(): PurchaseSaleComunicacionResponse{
        $response = $this->client->verificarComunicacion();
        return PurchaseSaleComunicacionResponse::build($response);
    }
}
