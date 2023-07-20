<?php

namespace PedroACF\Invoicing\Utils;

use Brick\Math\BigInteger;
use DOMDocument;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use PedroACF\Invoicing\Models\SYS\Sale;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Services\ConfigService;

class XmlGenerator
{
    protected $configService;
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function arrayToXml( $arrayData = [] ): DOMDocument{
        //$configService = app(ConfigService::class);
        $rootName = Arr::get($arrayData, 'root', 'NONE');
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
        $headList = Arr::get($arrayData, 'head', []);
        foreach ($headList as $key=>$value ){
            $xmlChild = $xml->createElement($key, $value);
            if($value === null || strlen($value)==0){
                $xmlChild = $xml->createElement($key);
                $xmlAttr = $xml->createAttribute("xsi:nil");
                $xmlAttr->value = "true";
                $xmlChild->appendChild($xmlAttr);
            }
            $xmlHead->appendChild($xmlChild);
        }
        $xmlRoot->appendChild($xmlHead);

        $details = Arr::get($arrayData, 'details', []);
        foreach ($details as $detail){
            $xmlDetail = $xml->createElement("detalle");
            foreach($detail as $key => $value){
                $xmlChild = $xml->createElement($key, $value);
                if($value === null || strlen($value)==0){
                    $xmlChild = $xml->createElement($key);
                    $xmlAttr = $xml->createAttribute("xsi:nil");
                    $xmlAttr->value = "true";
                    $xmlChild->appendChild($xmlAttr);
                }
                $xmlDetail->appendChild($xmlChild);
            }
            $xmlRoot->appendChild($xmlDetail);
        }
        $xml->appendChild( $xmlRoot );
        return $xml;
    }

    public function saleToArray($rootName, Sale $sale, $controlCode){
        $result = [];
        $result['root'] = $rootName;
        $head = [];
        $head['nitEmisor'] = $this->configService->getNit()??'';
        $head['razonSocialEmisor'] = $this->configService->getBusinessName()??'';
        $head['municipio'] = $this->configService->getMunicipality()??'';
        $head['telefono'] = $this->configService->getOfficePhone()??'';
        $head['numeroFactura'] = $sale->invoice_number??'';
        $head['cuf'] = $sale->cuf??'';
        $head['cufd'] = $sale->cufd??'';
        $head['codigoSucursal'] = $this->configService->getOfficeCode()??'';
        $head['direccion'] = $this->configService->getOfficeAddress()??'';
        $head['codigoPuntoVenta'] = $sale->sale_point_code??'';
        $emissionDate = new Carbon($sale->emission_date);
        $head['fechaEmision'] = $emissionDate->isValid()? $emissionDate->format("Y-m-d\TH:i:s.v"): '';
        $head['nombreRazonSocial'] = $sale->buyer_name??'';
        $head['codigoTipoDocumentoIdentidad'] = $sale->document_type_code??'';
        $head['numeroDocumento'] = $sale->document_number??'';
        $head['complemento'] = $sale->document_complement??'';
        $head['codigoCliente'] = $sale->buyer_id??'';
        $head['codigoMetodoPago'] = $sale->payment_method_code??'';
        $head['numeroTarjeta'] = '';
        $head['montoTotal'] = round($sale->total_amount??0, 2);
        $head['montoTotalSujetoIva'] = round($sale->iva_total_amount??0, 2);
        $head['codigoMoneda'] = $sale->currency_code??'';
        $head['tipoCambio'] = round($sale->exchange_rate??0, 2);
        $head['montoTotalMoneda'] = round($sale->currency_total_amount??0, 2);
        $head['montoGiftCard'] = round($sale->gift_card_amount??0, 2);
        $head['descuentoAdicional'] = round($sale->extra_discount_amount??0, 2);
        $head['codigoExcepcion'] = $sale->exception_code != null? $sale->exception_code: 0;
        $head['cafc'] = $sale->cafc??'';
        $head['leyenda'] = $this->configService->getLegendText()??'';
        $head['usuario'] = $sale->user_creation??'';
        $head['codigoDocumentoSector'] = $sale->sector_doc_type_code??'';

        $details = [];
        foreach($sale->details as $detailModel){
            $detail = [];
            $detail['actividadEconomica'] = $detailModel->activity_code??'';
            $detail['codigoProductoSin'] = $detailModel->sin_product_code??'';
            $detail['codigoProducto'] = $detailModel->code??'';
            $detail['descripcion'] = $detailModel->description??'';
            $detail['cantidad'] = round($detailModel->quantity, 2);
            $detail['unidadMedida'] = $detailModel->measurement_unit_code??'';
            $detail['precioUnitario'] = round($detailModel->unit_price??0, 2);
            $detail['montoDescuento'] = round($detailModel->discount_amount??0, 2);
            $detail['subTotal'] = round($detailModel->sub_amount??0, 2);
            $detail['numeroSerie'] = $detailModel->serial_number??'';
            $detail['numeroImei'] = $detailModel->imei_number??'';
            $details[] = $detail;
        }
        $head['cuf'] = $this->generateCufCode($head, $sale->emission_type_code, $controlCode);
        $result['head'] = $head;
        $result['details'] = $details;
        return $result;
    }
    private function generateCufCode($headData, $emissionTypeCode, $controlCode){
        $nit = str_pad(Arr::get($headData, 'nitEmisor', ''), 13, "0", STR_PAD_LEFT);
        $date = str_replace(["-","T",":","."], "", Arr::get($headData, 'fechaEmision', ''));
        $office = str_pad(Arr::get($headData, 'codigoSucursal', ''), 4, "0", STR_PAD_LEFT);
        $mode = $this->configService->getInvoiceMode();
        $invoiceType = $this->configService->getInvoiceTypeCode();
        $sectorType = str_pad($this->configService->getSectorDocumentCode(), 2, "0", STR_PAD_LEFT);
        $invoiceNumber = str_pad(Arr::get($headData, 'numeroFactura', ''), 10, "0", STR_PAD_LEFT);
        $sale = str_pad(Arr::get($headData, 'codigoPuntoVenta', ''), 4, "0", STR_PAD_LEFT);
        $cuf = $nit.$date.$office.$mode.$emissionTypeCode.$invoiceType.$sectorType.$invoiceNumber.$sale;
        $number = $this->mod11String($cuf);
        $number = BigInteger::of($number);
        $cuf = $number->toBase(16);
        return strtoupper($cuf).$controlCode;
    }

    private function mod11String(string $number){
        //int mult, suma, i, n, dig;
        $limMul = 9;
        $sum = 0;
        $mul = 2;
        for($i = strlen($number) - 1; $i >= 0; $i--){
            $sum += ($mul * ((int)$number[$i]));
            if(++$mul > $limMul){
                $mul = 2;
            }
        }
        $mod = $sum % 11;
        //TODO: Verificar esto, falta resta segun algoritmo
        if ($mod == 10) {
            $number .= "1";
        }
        if ($mod == 11) {
            $number .= "0";
        }
        if ($mod < 10) {
            $number .= $mod;
        }
        return $number;
    }
}
