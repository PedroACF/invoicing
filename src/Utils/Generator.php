<?php

namespace PedroACF\Invoicing\Utils;
use Faker\Factory as Faker;
use PedroACF\Invoicing\Invoices\DetailEInvoice;
use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Invoices\HeaderEInvoice;
use PedroACF\Invoicing\Models\SIN\EmissionType;
use PedroACF\Invoicing\Models\SIN\IdentityDocType;
use PedroACF\Invoicing\Models\SIN\Legend;
use PedroACF\Invoicing\Models\SIN\Measurement;
use PedroACF\Invoicing\Models\SIN\Product;
use PedroACF\Invoicing\Models\SYS\Buyer;
use PedroACF\Invoicing\Models\SYS\Sale;
use PedroACF\Invoicing\Models\SYS\SaleDetail;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Services\CodeService;

class Generator
{
    public function generateTestInvoice(): EInvoice{
        // - inicializar valores auxiliares
        $faker = Faker::create('es_PE');

        // - Generar cabecera
        $invoiceHeader = new HeaderEInvoice();
        $invoiceHeader->nombreRazonSocial = $faker->name;
        $typeDocument = IdentityDocType::where('descripcion', 'CI - CEDULA DE IDENTIDAD')->first();
        $invoiceHeader->codigoTipoDocumentoIdentidad = $typeDocument? $typeDocument->codigo_clasificador: 0;
        $invoiceHeader->numeroDocumento = $faker->randomNumber(8, true);
        $hasComplement = rand(0,1) == 1;
        if($hasComplement){
            $invoiceHeader->complemento = ($faker->randomLetter()).($faker->randomDigit());
        }
        $invoiceHeader->codigoCliente = $faker->randomNumber();//TODO: Esto debe salir de otra tabla
        $invoiceHeader->codigoMetodoPago = 1;
        $invoiceHeader->montoTotal = 0.00;
        $invoiceHeader->montoTotalSujetoIva = 0.00;
        $invoiceHeader->codigoMoneda = 1;
        $invoiceHeader->tipoCambio = 1.00;
        $invoiceHeader->montoTotalMoneda = 0;
        $invoiceHeader->montoGiftCard = 0;
        $invoiceHeader->descuentoAdicional = 0.00;
        $invoiceHeader->codigoExcepcion = 0;//TODO: para nit invalidos 0=>registro normal, 1=> se autoriza el registro
        $invoiceHeader->leyenda = Legend::inRandomOrder()->first()->descripcion_leyenda;//TODO: Ver de sacar de otro lado
        $invoiceHeader->usuario = $faker->regexify('[A-Z]{5}[0-4]{3}');
        $eInvoice = new EInvoice(config("pacf_invoicing.main_schema"), $invoiceHeader);
        for($i=0;$i<$faker->numberBetween(1, 3); $i++){
            //TODO check
            $detail = new DetailEInvoice();
            $product = Product::inRandomOrder()->first();
            $detail->actividadEconomica = $product->codigo_actividad;
            $detail->codigoProductoSin = $product->codigo_producto;
            $detail->codigoProducto = $faker->randomNumber();
            $detail->descripcion = $faker->sentence(10);
            $qty = $faker->randomNumber(1, 10);
            $detail->cantidad = $qty;
            $detail->unidadMedida = $faker->numberBetween(1, 200);
            $price = round($faker->randomFloat(5, 1, 100), 2);
            $detail->precioUnitario = $price;
            $detail->montoDescuento = 0.0;
            $detail->subTotal = round($qty*$price, 2);
            $detail->numeroImei = 0.0;
            $eInvoice->addDetail($detail);
        }
        return $eInvoice;
    }

    public function generateTestSale(SalePoint $salePoint, EmissionType $emissionType, ?string $cafc): Sale{
        $faker = Faker::create('es_PE');
        //Generar venta
        $sale = new Sale();
        //CUF, CUFD se llena despues
        //$table->dateTime('emission_date');
        $sale->total_amount = 0.0;
        $sale->iva_total_amount = 0.0;
        $sale->currency_total_amount = 0.0;
        $sale->cafc = $cafc;
        $buyer_doc = $faker->randomNumber(8, true);
        $buyer = Buyer::where("document_number", $buyer_doc)->first();
        if($buyer==null){
            $buyer = new Buyer();
            $buyer->name = $faker->name;
            $buyer->document_number = $buyer_doc;
            $buyer->email = $faker->email;
            $buyer->phone = $faker->numberBetween($min = 66000000, $max = 71000000);
            $documentType = IdentityDocType::inRandomOrder()->first();
            if($documentType->codigo_clasificador == 1){
                if(rand(0, 1) == 1){
                    $buyer->document_complement = ($faker->randomLetter()).($faker->randomDigit());
                }
            }elseif ($documentType->codigo_clasificador == 5){
                $codeService = app(CodeService::class);
                $response = $codeService->checkNit($salePoint, $buyer_doc);
                if(!$response->transaccion){
                    $sale->exception_code = 1;
                    $buyer->observations = $response->getJsonMessages();
                }
            }
            $buyer->document_type_code = $documentType->codigo_clasificador;
            $buyer->save();
        }
        //CLIENT DATA
        $sale->document_number = $buyer->document_number;
        $sale->document_complement = $buyer->document_complement;
        $sale->buyer_name = $buyer->name;
        $sale->buyer_id = $buyer->id;
        //SIN RELATIONS
        $sale->document_type_code = $buyer->document_type_code;
        $sale->emission_type_code = $emissionType->codigo_clasificador;
        $sale->payment_method_code = 1;//EFECTIVO
        $sale->sector_doc_type_code = 1;//TODO: Consumir de servicio
        $sale->sale_point_code = $salePoint->sin_code;
        $sale->currency_code = 1;//MONEDA BOLIVIANOS
        $sale->exchange_rate = 1;
        //$table->binary('signed_invoice')->nullable();
        //MY TABLES RELATIONS
        $sale->user_creation = $faker->regexify('[A-Z]{5}[0-4]{3}');
        $sale->save();
        $total = 0;
        for($i=0;$i<$faker->numberBetween(1, 5); $i++){
            //TODO check
            $detail = new SaleDetail();
            //TODO: CAMBIAR A TIPO DE ACTIVIDAD?
            $product = Product::inRandomOrder()->first();
            $detail->code = $faker->randomNumber();
            $detail->description = $faker->sentence(10);
            $qty = $faker->randomNumber(1, 10);
            $detail->quantity = $qty;
            $price = round($faker->randomFloat(5, 1, 100), 2);
            $detail->unit_price = $price;
            $detail->discount_amount = 0.0;
            $detail->sub_amount = round($qty*$price, 2);

            $detail->activity_code = $product->codigo_actividad;
            $detail->sin_product_code = $product->codigo_producto;
            $detail->measurement_unit_code = Measurement::inRandomOrder()->first()->codigo_clasificador;

            $detail->serial_number = null;
            $detail->imei_number = null;
            $detail->sale_id = $sale->id;
            $detail->save();
            $total = $total + round($qty*$price, 2);
        }
        $sale->total_amount = $total;
        $sale->iva_total_amount = $total;
        $sale->currency_total_amount = $total * $sale->exchange_rate;
        $sale->save();
        return $sale;
    }
}
