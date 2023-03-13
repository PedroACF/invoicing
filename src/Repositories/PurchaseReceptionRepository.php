<?php

namespace App\Utils\facturator\Services;
use PedroACF\Invoicing\Responses\PurchaseReception\PurchaseReceptionComunicacionResponse;
use PedroACF\Invoicing\Utils\TokenUtils;

class PurchaseReceptionRepository
{
    public function __construct()
    {
        $tokenReg = TokenUtils::getValidTokenReg();
        $wsdl = config("siat_invoicing.endpoints.recepcion_compras");
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

    //anulacionCompra
    public function cancelSale(){
        $response = $this->client->anulacionCompra();
    }

    //confirmacionCompras
    public function salesConfirmation(){
        $response = $this->client->confirmacionCompras();
    }

    //consultaCompras
    public function querySales(){
        $response = $this->client->consultaCompras();
    }

    //recepcionPaqueteCompras
    public function sendSalePackage(){
        $response = $this->client->recepcionPaqueteCompras();
    }

    //validacionRecepcionPaqueteCompras
    public function validateSalePackageSend(){
        $response = $this->client->validacionRecepcionPaqueteCompras();
    }

    //verificarComunicacion
    public function verificarComunicacion(): PurchaseReceptionComunicacionResponse{
        $response = $this->client->verificarComunicacion();
        return PurchaseReceptionComunicacionResponse::build($response);
    }
}
