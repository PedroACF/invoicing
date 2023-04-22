<?php

namespace PedroACF\Invoicing\Repositories;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
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
    public function cancelInvoice(){
        $response = $this->client->anulacionFactura();
    }

    //recepcionAnexos
    public function sendAnnexes(){
        $response = $this->client->recepcionAnexos();
    }

    //recepcionFactura
    public function sendInvoice(RecepcionFacturaRequest $req){
        dump($req->toArray());
        $response = $this->client->recepcionFactura( $req->toArray() );
        dump($response);
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
    public function checkInvoiceStatus(){
        $response = $this->client->verificacionEstadoFactura();
    }

    public function verificarComunicacion(): PurchaseSaleComunicacionResponse{
        $response = $this->client->verificarComunicacion();
        return PurchaseSaleComunicacionResponse::build($response);
    }
}
