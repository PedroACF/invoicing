<?php

namespace PedroACF\Invoicing\Utils;

use Carbon\Carbon;
use PedroACF\Invoicing\Models\SYS\Sale;
use PedroACF\Invoicing\Services\ConfigService;

class XmlGenerator
{
    public function saleToXml(Sale $sale){
        //$configService = app(ConfigService::class);
        $configService = new ConfigService();
        $rootName = config("pacf_invoicing.main_schema");
        $xml = new DOMDocument('1.0', "UTF-8");
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = false;
        $xml->xmlStandalone = false;
        $xmlRoot = $xml->createElement($rootName);
        $xmlAttr = $xml->createAttribute("xmlns:xsi");
        $xmlAttr->value = "http://www.w3.org/2001/XMLSchema-instance";
        $xmlRoot->appendChild($xmlAttr);

        $xmlAttr = $xml->createAttribute("xsi:noNamespaceSchemaLocation");
        $xmlAttr->value = $rootName.".xsd";
        $xmlRoot->appendChild($xmlAttr);

        // Header
        $xmlHead = $xml->createElement("cabecera");

        $headChildren = [];

        $headChildren[] = $xml->createElement('nitEmisor', $configService->getNit());
        $headChildren[] = $xml->createElement('razonSocialEmisor', $configService->getBusinessName());
        $headChildren[] = $xml->createElement('municipio', $configService->getMunicipality());
        $headChildren[] = $xml->createElement('municipio', $configService->getMunicipality());

        $value = $configService->getOfficePhone();
        $xmlChild = $xml->createElement('telefono', $value);
        if($value === null){
            $xmlAttr = $xml->createAttribute("xsi:nil");
            $xmlAttr->value = "true";
            $xmlChild->appendChild($xmlAttr);
        }
        $headChildren[] = $xmlChild;

        $headChildren[] = $xml->createElement('numeroFactura', $sale->invoice_number);
        $headChildren[] = $xml->createElement('cuf', $sale->cuf);
        $headChildren[] = $xml->createElement('cufd', $sale->cufd);
        $headChildren[] = $xml->createElement('codigoSucursal', $configService->getOfficeCode());
        $headChildren[] = $xml->createElement('direccion', $configService->getOfficeAddress());
        $headChildren[] = $xml->createElement('codigoPuntoVenta', $sale->sale_point_code);
        $emissionDate = new Carbon($sale->emission_date);
        $headChildren[] = $xml->createElement('fechaEmision', $emissionDate->format("Y-m-d\TH:i:s.v"));

        $value = $sale->buyer_name;
        $xmlChild = $xml->createElement('nombreRazonSocial', $value);
        if($value === null){
            $xmlAttr = $xml->createAttribute("xsi:nil");
            $xmlAttr->value = "true";
            $xmlChild->appendChild($xmlAttr);
        }
        $headChildren[] = $xmlChild;

        $headChildren[] = $xml->createElement('codigoTipoDocumentoIdentidad', $sale->document_type_code);
        $headChildren[] = $xml->createElement('numeroDocumento', $sale->document_number);

        $value = $sale->document_complement;
        $xmlChild = $xml->createElement('complemento', $value);
        if($value === null){
            $xmlAttr = $xml->createAttribute("xsi:nil");
            $xmlAttr->value = "true";
            $xmlChild->appendChild($xmlAttr);
        }
        $headChildren[] = $xmlChild;

        $headChildren[] = $xml->createElement('codigoCliente', $sale->buyer_id);
        $headChildren[] = $xml->createElement('codigoMetodoPago', $sale->payment_method_code);



        numeroTarjeta,Numérico,No,Cuando el método de pago es 2 (Tarjeta), debe enviarse este valor pero ofuscado con los primeros y últimos 4 dígitos en claro y ceros al medio. Ej: 4797000000007896, en otro caso, debe enviarse un valor nulo.,
        montoTotal,Numérico,Si,Monto total por el cual se realiza el hecho generador.,
        montoTotalSujetoIva,Numérico,Si,Monto base para el cálculo del crédito fiscal.
        montoGiftCard,Numérico,No,Monto a ser cancelado con una Gift Card
        descuentoAdicional,Numérico,No,Monto Adicional al descuento por item
        codigoExcepcion,Numérico,No,Valor que se envía para autorizar el registro de una factura con NIT inválido. Por defecto, enviar cero (0) o nulo y uno (1) cuando se autorice el registro.
        cafc,Alfanumérico,No,Código de Autorización de Facturas por Contingencia
        codigoMoneda,Numérico,Si,Valor de la paramétrica que identifica la moneda en la cual se realiza la transacción.
        tipoCambio,Numérico,Si,Tipo de cambio de acuerdo a la moneda en la que se realiza el hecho generador, si el código de moneda es boliviano deberá ser igual a 1.
        montoTotalMoneda,Numérico,Si,Es el Monto Total expresado en el tipo de moneda, si el código de moneda es boliviano deberá ser igual al monto total.
        leyenda,Alfanumérico,Si,Leyenda asociada a la actividad económica.
        usuario,Alfanumérico,Si,Identifica al usuario que emite la factura, deberá ser descriptivo. Por ejemplo JPEREZ
        codigoDocumentoSector,Numérico,Si,Valor de la paramétrica que identifica el tipo de factura que se está emitiendo. Para este tipo de factura este valor es 1.

         foreach ($headChildren as $headChild){
             if($value === null){
                 $xmlAttr = $xmlInstance->createAttribute("xsi:nil");
                 $xmlAttr->value = "true";
                 $xmlChild->appendChild($xmlAttr);
             }
             $xmlHead->appendChild($headChild);
         }
        $xmlRoot->appendChild($xmlHead);

        /* @var $detail BaseDetailInvoice */
        foreach($this->details as $detail){
            $xmlRoot->appendChild($detail->getXmlDetail($xml));
        }

        $xml->appendChild( $xmlRoot );
        return $xml;
    }
}

public function getXmlHeader(DOMDocument $xmlInstance){
    $xmlHead = $xmlInstance->createElement("cabecera");
    foreach($this as $key => $value){
        $xmlChild = $xmlInstance->createElement($key, $value);
        if($value === null){
            $xmlAttr = $xmlInstance->createAttribute("xsi:nil");
            $xmlAttr->value = "true";
            $xmlChild->appendChild($xmlAttr);
        }
        $xmlHead->appendChild($xmlChild);
    }
    return $xmlHead;
}
