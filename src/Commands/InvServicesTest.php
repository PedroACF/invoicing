<?php

namespace PedroACF\Invoicing\Commands;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use PedroACF\Invoicing\Invoices\DetailEInvoice;
use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Invoices\HeaderEInvoice;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SIN\IdentityDocType;
use PedroACF\Invoicing\Models\SIN\Legend;
use PedroACF\Invoicing\Models\SIN\Product;
use PedroACF\Invoicing\Models\SIN\SignificantEventType;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\Invoice;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Models\SYS\SignificantEvent;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
use PedroACF\Invoicing\Services\CatalogService;
use PedroACF\Invoicing\Services\CodeService;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Services\InvoicingService;
use PedroACF\Invoicing\Services\KeyService;
use PedroACF\Invoicing\Services\OperationService;
use PedroACF\Invoicing\Services\TokenService;
use PedroACF\Invoicing\Utils\XmlSigner;
use PedroACF\Invoicing\Utils\XmlValidator;

class InvServicesTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inv:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test invoice package';

    private $salePointLimit = 0;

    private $configService;
    private $tokenService;
    private $keyService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ConfigService $configService, TokenService $tokenService, KeyService $keyService)
    {
        $this->configService = $configService;
        $this->tokenService = $tokenService;
        $this->keyService = $keyService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->readAndSetDelegateToken();

        $this->readAndSetConfigs();

        $publicKey = $this->readPublicKey();
        $this->keyService->addPublicKeyFromPem($publicKey);

        $privateKey = $this->readPrivateKey();
        $this->keyService->addPrivateKeyFromPem($privateKey);

        $salePoint = SalePoint::where('sin_code', 0)->first();
        $this->etapaI($salePoint, 1);
        $this->etapaII($salePoint, 1);
        $this->etapaIII($salePoint, 1);
        $this->etapaIV($salePoint, 1);
        //$this->etapaV($salePoint, 1);
//            $this->etapaVI();
        $this->etapaVII($salePoint);

    }

    private function readAndSetConfigs(){
        //READ BUSINESS NAME
        $fieldValidator = [ 'business_name' => 'required' ];
        $showPrompt = true;
        $businessName = '';
        while($showPrompt){
            $businessName = $this->ask('Ingrese Razon Social');
            $validator = Validator::make(['business_name' => $businessName], $fieldValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['business_name'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
        }

        //READ Municipality
        $fieldValidator = [ 'municipality' => 'required' ];
        $showPrompt = true;
        $municipality = '';
        while($showPrompt){
            $municipality = $this->ask('Ingrese Municipio [POTOSI]', 'POTOSI');
            $validator = Validator::make(['municipality' => $municipality], $fieldValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['municipality'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
        }

        //READ Office
        $fieldValidator = [ 'office' => 'required|numeric' ];
        $showPrompt = true;
        $office = 0;
        while($showPrompt){
            $office = $this->ask('Ingrese Numero Sucursal (0=>Casa Matriz)');
            $validator = Validator::make(['office' => $office], $fieldValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['office'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
        }

        //READ Phone
        $fieldValidator = [ ];
        $showPrompt = true;
        $phone = '';
        while($showPrompt){
            $phone = $this->ask('Ingrese Telefono sucursal', '');
            $validator = Validator::make(['phone' => $phone], $fieldValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['phone'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
        }

        //READ Office Address
        $fieldValidator = [ 'office_address' => 'required' ];
        $showPrompt = true;
        $office_address = '';
        while($showPrompt){
            $office_address = $this->ask('Ingrese Direccion Sucursal');
            $validator = Validator::make(['office_address' => $office_address], $fieldValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['office'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
        }

        $this->configService->setEnvironment(Config::$ENV_TEST);
        $this->configService->setInvoiceMode(Config::$MODE_ELEC);
        $this->configService->setBusinessName($businessName);
        $this->configService->setMunicipality($municipality);
        $this->configService->setOfficeCode(0);
        $this->configService->setOfficePhone($phone);
        $this->configService->setOfficeAddress($office_address);

        //OTROS
        $this->configService->setSectorDocumentCode(1);
        //server_time_diff, LAST_INVOICE_NUMBER
    }

    private function readAndSetDelegateToken(){
        $tokenValidator = [ 'token' => 'required' ];
        $showPrompt = true;
        $dToken = null;

        while($showPrompt){
            $dToken = $this->ask('Token Delegado');
            $validator = Validator::make(['token' => $dToken], $tokenValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['token'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            try{
                list($header, $body) = explode('.', $dToken);
                $body = base64_decode($body);
                $data = json_decode($body);
                $systemCode = $data->codigoSistema;
                $nit = $data->nitDelegado;
                $expTime = $data->exp;
                $expDate = Carbon::createFromTimestampUTC($expTime);
                $this->configService->setNit($nit);
                $this->configService->setSystemCode($systemCode);
                $this->tokenService->addDelegateToken($dToken, $expDate);
                $showPrompt = false;
            }catch(\Exception $e){
                $validator->errors()->add('token', $e->getMessage());
                $errors = $validator->errors()->messages()['token'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
            }
        }
    }

    private function readPublicKey(){
        $showPrompt = true;
        $fileContent = null;
        while($showPrompt){
            $keyPath = $this->ask('Ruta del certificado publico (.pem): ');
            $validator = Validator::make([], []);
            try{
                $fileContent = file_get_contents($keyPath);
                $showPrompt = false;
            }catch(\Exception $e){
                $validator->errors()->add('key', $e->getMessage());
                $errors = $validator->errors()->messages()['key'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
            }
        }
        return $fileContent;
    }

    private function readPrivateKey(){
        $showPrompt = true;
        $fileContent = null;
        while($showPrompt){
            $keyPath = $this->ask('Ruta del certificado privado (.pem): ');
            //$keyPassword = $this->ask('Password para el certificado privado: ');
            $validator = Validator::make([], []);
            try{
                $fileContent = file_get_contents($keyPath);
                $showPrompt = false;
            }catch(\Exception $e){
                $validator->errors()->add('key', $e->getMessage());
                $errors = $validator->errors()->messages()['key'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
            }
        }
        return $fileContent;
    }

    private function etapaVIII(){
        $testLimit = 115;
        $this->writeMessage("Etapa VIII: Firma digital (punto de venta: $this->salePoint)", true, 'warning');
    }

    private function etapaVII(SalePoint $salePoint){

        $this->writeMessage("Etapa VII: Anulacion (punto de venta: $salePoint->sin_code)", true, 'warning');
        // TODO: Tomar toda la lista de la etapa iv (mejorar esto)
        $forNullifyList = Invoice::all();
        $testLimit = count($forNullifyList);
        $test = 1;
        foreach($forNullifyList as $invoice){
            try{
                $service = app(InvoicingService::class);
                $cancelReason = CancelReason::inRandomOrder()->first();
                $passed = $service->cancelInvoice($salePoint, $invoice, $cancelReason, 1);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test++, $testLimit, $passed);
        }
    }
    private function etapaVI($salePoint, $testLimit = 0){
        $this->writeMessage("Etapa VI: Consumo de metodos de emision de paquetes (punto de venta: $this->salePoint)", true, 'warning');

    }
    private function etapaV(SalePoint $salePoint, $testLimit = 0){
        $this->writeMessage("Etapa V: Registro de Eventos Significativos (punto de venta: $salePoint->sin_code)", true, 'warning');
        $codeService = app(CodeService::class);

        $eventTypes = SignificantEventType::all();
        $test = 1;
        $faker = Faker::create('es_PE');
        $now = Carbon::now();
        $now->subDays(1);
        foreach($eventTypes as $eventType){
            if($test>$testLimit){
                break;
            }
            //Revisar CUFD
            $event = new SignificantEvent();
            $event->event_code = $eventType->codigo_clasificador;
            $event->description = $faker->regexify('[A-Z]{5}[0-4]{3}');
            $event->event_cufd = $codeService->getCufdCode($salePoint);
            $event->start_datetime = $now;
            $event->sale_point = $salePoint;
            $event->save();
        }
        $events = SignificantEvent::whereNull("end_datetime")->where('sale_point', $salePoint)->get();
        foreach($events as $event){
            try{
                $service = app(OperationService::class);
                $passed = $service->addSignificantEvent($salePoint, $event);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }

            $this->write($test, $testLimit, $passed);
        }
    }

    private function etapaIV(SalePoint $salePoint, $testLimit = 0){
        $invoicingService = app(InvoicingService::class);
        $this->writeMessage("Etapa IV: Consumo de metodos de emision individual (punto de venta: $salePoint->sin_code)", true, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                //Ejemplo de como generar factura
                //GENERAR ARCHIVO XML
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
                //=>emision en linea = 1
                //1=>factura con derecho a credito fiscal
                $passed = $invoicingService->sendElectronicInvoice($salePoint, $eInvoice, 1,1);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
    }

    private function etapaIII(SalePoint $salePoint, $testLimit = 0){
        $codeService = app(CodeService::class);
        $this->writeMessage("Etapa III: Obtencion CUFD (punto de venta: $salePoint->sin_code)", true, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $cufd = $codeService->getCufdModel($salePoint, true);
                $passed = $cufd!=null;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
    }

    private function etapaII(SalePoint $salePoint, $testLimit = 0){
        $catalogService = app(CatalogService::class);
        $this->writeMessage("Etapa II: Sincronizacion de catalogos (punto de venta: $salePoint->sin_code)", true, 'warning');
        $this->writeMessage("* 01 LISTADO TOTAL DE ACTIVIDADES *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncActividades($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 02 FECHA Y HORA ACTUAL *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncFechaHora($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 03 LISTADO TOTAL DE ACTIVIDADES DOCUMENTO SECTOR *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncActividadesDocumentosSector($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 04 LISTADO TOTAL DE LEYENDAS DE FACTURAS", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncLeyendas($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 05 LISTADO TOTAL DE MENSAJES DE SERVICIOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncMensajes($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 06 LISTADO TOTAL DE PRODUCTOS Y SERVICIOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncProductos($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 07 LISTADO TOTAL DE EVENTOS SIGNIFICATIVOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncEventosSignificativos($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
        $this->writeMessage("* 08 LISTADO TOTAL DE MOTIVOS DE ANULACION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncMotivosAnulacion($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
        $this->writeMessage("* 09 LISTADO TOTAL DE PAISES *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncPaises($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
        $this->writeMessage("* 10 LISTADO TOTAL DE TIPOS DE DOCUMENTO DE IDENTIDAD *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposDocumentoIdentidad($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
        $this->writeMessage("* 11 LISTADO TOTAL DE TIPOS DE DOCUMENTO SECTOR *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposDocumentoSector($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
        $this->writeMessage("* 12 LISTADO TOTAL DE TIPOS DE EMISION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposEmision($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 13 LISTADO TOTAL DE TIPO HABITACION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposHabitacion($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 14 LISTADO TOTAL DE METODO DE PAGO *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposMetodoPago($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 15 LISTADO TOTAL DE TIPOS DE MONEDA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposMoneda($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 16 LISTADO TOTAL DE TIPOS DE PUNTO DE VENTA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposPuntoVenta($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
        $this->writeMessage("* 17 LISTADO TOTAL DE TIPOS DE FACTURA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncTiposFactura($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }

        $this->writeMessage("* 18 LISTADO TOTAL DE UNIDAD DE MEDIDA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $passed = $catalogService->syncUnidadesMedida($salePoint);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
    }

    private function etapaI(SalePoint $salePoint, $testLimit = 0){
        $codeService = app(CodeService::class);
        $this->writeMessage("Etapa I: Obtencion de CUIS (punto de venta: $salePoint->sin_code)", true, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $cuis = $codeService->getCuisCode($salePoint, true);
                $passed = $cuis!=null;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test, $testLimit, $passed);
        }
    }

    public function write($test, $testLimit, $passed){
        $number = str_pad($test, 3, "0", STR_PAD_LEFT);
        $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
        $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
    }

    private function writeMessage($message = "", $withBorder = false, $type = ''){
        $borderLength = strlen($message) + 2;
        $border = '+'.str_pad('-', $borderLength, '-').'+';
        $text = $message;
        if($withBorder){
            $text = "| $text |";
        }
        if($withBorder){
            $this->output->writeln($this->getColorText($type, $border));
        }
        $this->output->writeln($this->getColorText($type, $text));
        if($withBorder){
            $this->output->writeln($this->getColorText($type, $border));
        }
    }

    private function getColorText($type, $text){
        $rText = '';
        switch ($type){
            case 'error':
                $rText = "<fg=red>$text</fg=red>";
                break;
            case 'info':
                $rText = "<info>$text</info>";
                break;
            case 'warning':
                $rText = "<fg=blue>$text</fg=blue>";
                break;
            default:
                $rText = $text;
                break;
        }
        return $rText;
    }
}
