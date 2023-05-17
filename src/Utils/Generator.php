<?php

namespace PedroACF\Invoicing\Utils;
use Faker\Factory as Faker;
use PedroACF\Invoicing\Invoices\DetailEInvoice;
use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Invoices\HeaderEInvoice;
use PedroACF\Invoicing\Models\SIN\IdentityDocType;
use PedroACF\Invoicing\Models\SIN\Legend;
use PedroACF\Invoicing\Models\SIN\Product;

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
}
