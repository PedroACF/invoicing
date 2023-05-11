<?php

namespace PedroACF\Invoicing\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use PedroACF\Invoicing\Invoices\DetailEInvoice;
use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Invoices\HeaderEInvoice;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SIN\IdentityDocType;
use PedroACF\Invoicing\Models\SIN\Product;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\Invoice;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
use PedroACF\Invoicing\Services\CatalogService;
use PedroACF\Invoicing\Services\CodeService;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Services\InvoicingService;
use PedroACF\Invoicing\Services\KeyService;
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
        $this->readAndSetConfigs();

        $delegateToken = $this->readDelegateToken();
        $expiredDate = $this->readDateToken();
        $this->tokenService->addDelegateToken($delegateToken, $expiredDate);

        $publicKey = $this->readPublicKey();
        $this->keyService->addPublicKeyFromPem($publicKey);

        $privateKey = $this->readPrivateKey();
        $this->keyService->addPrivateKeyFromPem($privateKey);

        $salePoint = 0; // Sin puntos de venta
        $this->etapaI($salePoint);
//            $this->etapaII($salePoint);
//            $this->etapaIII($salePoint);
//            $this->etapaIV($salePoint);
//            $this->etapaV();
//            $this->etapaVI();
        //$this->etapaVII($salePoint);

    }

    private function readAndSetConfigs(){
        //READ System Code
        $fieldValidator = [ 'code' => 'required' ];
        $showPrompt = true;
        $code = '';
        while($showPrompt){
            $code = $this->ask('Ingrese Codigo Sistema');
            $validator = Validator::make(['code' => $code], $fieldValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['code'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
        }

        //READ NIT
        $fieldValidator = [ 'nit' => 'required' ];
        $showPrompt = true;
        $nit = '';
        while($showPrompt){
            $nit = $this->ask('Ingrese NIT');
            $validator = Validator::make(['nit' => $nit], $fieldValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['nit'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
        }

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
        $this->configService->setSystemCode($code);

//        $configService->setOfficeCode(0);
//        $configService->setOfficeAddress('18 de nobiembre');
        $this->configService->setNit($nit);
        $this->configService->setBusinessName($businessName);
        $this->configService->setMunicipality($municipality);
        $this->configService->setOfficeCode(0);
        $this->configService->setOfficePhone($phone);
        $this->configService->setOfficeAddress($office_address);
        //server_time_diff, LAST_INVOICE_NUMBER
    }

    private function readDelegateToken(){
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
            $this->delegatedToken = $dToken;
            $showPrompt = false;

//            try{
//                list($header, $body) = explode('.', $dToken);
//                $body = base64_decode($body);
//                $data = json_decode($body);
//                if($data->codigoSistema != config("siat_invoicing.system_code")){
//                    $validator->errors()->add('token', "Codigo de sistema incorrecto para el token ingresado");
//                    $errors = $validator->errors()->messages()['token'];
//                    $errors = implode(', ', $errors);
//                    $this->writeMessage("> Error: $errors", false, 'error');
//                    continue;
//                }
//                $showPrompt = false;
//                $this->delegatedToken = $dToken;
//            }catch(\Exception $e){
//                $validator->errors()->add('token', $e->getMessage());
//                $errors = $validator->errors()->messages()['token'];
//                $errors = implode(', ', $errors);
//                $this->writeMessage("> Error: $errors", false, 'error');
//            }
        }
        return $dToken;
    }

    private function readDateToken(){
        $dateValidator = [ 'date' => 'required|date_format:Y-m-d' ];
        $showPrompt = true;
        $dtDate = null;
        while($showPrompt){
            $dtDate = $this->ask('Fecha límite (YYYY-MM-DD)');
            $validator = Validator::make(['date' => $dtDate], $dateValidator);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['date'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
            $this->dateToken = $dtDate;
        }
        return $dtDate;
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

    private function etapaVII($salePoint){
        $testLimit = 125;
        $this->writeMessage("Etapa VII: Anulacion (punto de venta: $salePoint)", true, 'warning');
        // TODO: Tomar toda la lista de la etapa iv (mejorar esto)
        $forNullifyList = Invoice::all();
        $test = 1;
        foreach($forNullifyList as $invoice){
            try{
                $service = new InvoicingService();
                $cancelReason = CancelReason::inRandomOrder()->first();
                $service->cancelInvoice($invoice, $cancelReason);
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test++, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
    }
    private function etapaVI(){
        $testLimit = 10;
        $this->writeMessage("Etapa VI: Consumo de metodos de emision de paquetes (punto de venta: $this->salePoint)", true, 'warning');
    }
    private function etapaV(){
        $testLimit = 5;
        $this->writeMessage("Etapa V: Registro de Eventos Significativos (punto de venta: $this->salePoint)", true, 'warning');
    }

    private function etapaIV($salePoint){
        $emissionDate = ConfigService::getTime();
        $codeService = new CodeService();
        $this->writeMessage("Etapa IV: Consumo de metodos de emision individual (punto de venta: $salePoint)", true, 'warning');
        $testLimit = 125;
        $cuisModel = $codeService->getValidCuisModel();
        for($test = 1; $test<=$testLimit; $test++){
            try{
                //GENERAR ARCHIVO XML
                // - inicializar valores auxiliares
                $faker = Faker::create('es_PE');
                $newInvoiceNumber = ConfigService::getAvailableInvoiceNumber();
                $cufdModel = $codeService->getValidCufdModel();
                $sectorDocumentCode = 1; // TODO: Revisar
                // - Generar cabecera
                $invoiceHeader = new HeaderEInvoice();
                $invoiceHeader->nitEmisor = Config::getNitConfig()->value;
                $invoiceHeader->razonSocialEmisor = Config::getBusinessNameConfig()->value;
                $invoiceHeader->municipio = Config::getMunicipalityConfig()->value;
                $invoiceHeader->telefono = Config::getOfficePhoneConfig()->value;
                $invoiceHeader->numeroFactura = $newInvoiceNumber;
                //$invoiceHeader->cuf = 'ASDQW12';
                $invoiceHeader->cufd = $cufdModel->cufd;
                $invoiceHeader->codigoSucursal = Config::getOfficeCodeConfig()->value;
                $invoiceHeader->direccion = Config::getOfficeAddressConfig()->value;
                $invoiceHeader->fechaEmision = $emissionDate->format("Y-m-d\TH:i:s.v");
                $invoiceHeader->nombreRazonSocial = $faker->name;
                $typeDocument = IdentityDocType::where('descripcion', 'CI - CEDULA DE IDENTIDAD')->first();
                $invoiceHeader->codigoTipoDocumentoIdentidad = $typeDocument? $typeDocument->codigo_clasificador: 0;
                $document = [];
                $invoiceHeader->numeroDocumento = $faker->randomNumber(8, true);
                $hasComplement = rand(0,1) == 1;
                $document[] = $invoiceHeader->numeroDocumento;
                if($hasComplement){
                    $invoiceHeader->complemento = ($faker->randomLetter()).($faker->randomDigit());
                    $document[] = $invoiceHeader->complemento;
                }
                $invoiceHeader->codigoCliente = $faker->randomNumber();
                $invoiceHeader->codigoMetodoPago = 1;
                $invoiceHeader->montoTotal = 0.00;
                $invoiceHeader->montoTotalSujetoIva = 0.00;
                $invoiceHeader->codigoMoneda = 1;
                $invoiceHeader->tipoCambio = 1.00;
                $invoiceHeader->montoTotalMoneda = 0;
                $invoiceHeader->montoGiftCard = 0;
                $invoiceHeader->descuentoAdicional = 0.00;
                $invoiceHeader->codigoExcepcion = 0;//para nit invalidos 0=>registro normal, 1=> se autoriza el registro
                $invoiceHeader->leyenda = $faker->sentence(10);
                $invoiceHeader->usuario = $faker->regexify('[A-Z]{5}[0-4]{3}');
                $invoiceHeader->codigoDocumentoSector = $sectorDocumentCode;
                $invoiceHeader->generateCufCode($cufdModel);
                $eInvoice = new EInvoice(config("siat_invoicing.main_schema"), $invoiceHeader);
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

                // FIRMAR XML
                $signer = new XmlSigner();
                $signedXml = $signer->sign($eInvoice->toXml()->saveXML());

                $model = new Invoice();
                $model->number = $newInvoiceNumber;
                $model->cuf = $invoiceHeader->cuf;
                $model->document = implode("-", $document);
                $model->client_name = $invoiceHeader->razonSocialEmisor;
                $model->emission_date = $emissionDate;
                $model->amount = $eInvoice->header->montoTotal;
                $model->content = $signedXml;
                $model->user_id = $faker->randomNumber();
                $model->save();
                $model->refresh();
                $content = stream_get_contents($model->content);
                // VALIDAR CON XSD
                $xmlValidator = new XmlValidator($content);
                $xmlValidator->validate();
//                dd(libxml_get_errors());
                // COMPRIMIR ZIP
                $compressed = gzencode($content);
                //$b64 = base64_encode($compressed);

                // OBTENER HASH
                $hash = hash('sha256', $compressed);

                //SEND PACKAGE
                $request = new RecepcionFacturaRequest($sectorDocumentCode, 1, $cufdModel->cufd, $cuisModel->cuis, 1, $compressed, $hash);
                $service = new InvoicingService();
                $service->sendInvoice($request);
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
    }

    private function etapaIII(int $salePoint){
        $codeService = new CodeService();
        $this->writeMessage("Etapa III: Obtencion CUFD (punto de venta: $salePoint)", true, 'warning');
        $testLimit = 1;
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $codeService->getCufdCode(true);
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
    }

    private function etapaII($salePoint){
        $catalogService = new CatalogService();
        $this->writeMessage("Etapa II: Sincronizacion de catalogos (punto de venta: $salePoint)", true, 'warning');
        $testLimit = 1;//para todos los casos
        $this->writeMessage("* 01 LISTADO TOTAL DE ACTIVIDADES *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncActividades();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        //TODO: Ver como hacer
        $this->writeMessage("* 02 FECHA Y HORA ACTUAL *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){

            try{
                $catalogService->syncFechaHora();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 03 LISTADO TOTAL DE ACTIVIDADES DOCUMENTO SECTOR *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncActividadesDocumentosSector();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 04 LISTADO TOTAL DE LEYENDAS DE FACTURAS", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncLeyendas();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 05 LISTADO TOTAL DE MENSAJES DE SERVICIOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncMensajes();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 06 LISTADO TOTAL DE PRODUCTOS Y SERVICIOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncProductos();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 07 LISTADO TOTAL DE EVENTOS SIGNIFICATIVOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncEventosSignificativos();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 08 LISTADO TOTAL DE MOTIVOS DE ANULACION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncMotivosAnulacion();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 09 LISTADO TOTAL DE PAISES *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncPaises();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 10 LISTADO TOTAL DE TIPOS DE DOCUMENTO DE IDENTIDAD *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposDocumentoIdentidad();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 11 LISTADO TOTAL DE TIPOS DE DOCUMENTO SECTOR *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposDocumentoSector();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 12 LISTADO TOTAL DE TIPOS DE EMISION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposEmision();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 13 LISTADO TOTAL DE TIPO HABITACION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposHabitacion();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 14 LISTADO TOTAL DE METODO DE PAGO *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposMetodoPago();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 15 LISTADO TOTAL DE TIPOS DE MONEDA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposMoneda();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 16 LISTADO TOTAL DE TIPOS DE PUNTO DE VENTA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposPuntoVenta();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 17 LISTADO TOTAL DE TIPOS DE FACTURA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncTiposFactura();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }

        $this->writeMessage("* 18 LISTADO TOTAL DE UNIDAD DE MEDIDA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $catalogService->syncUnidadesMedida();
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
    }

    private function etapaI($salePoint){
        $codeService = app(CodeService::class);
        $this->writeMessage("Etapa I: Obtencion de CUIS (punto de venta: $salePoint)", true, 'warning');
        $testLimit = 1;
        for($test = 1; $test<=$testLimit; $test++){
            try{
                $codeService->getCuisCode($salePoint, true);
                $passed = true;
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
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
