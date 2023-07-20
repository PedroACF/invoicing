<?php

namespace PedroACF\Invoicing\Commands;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SIN\EmissionType;
use PedroACF\Invoicing\Models\SIN\SalePointType;
use PedroACF\Invoicing\Models\SIN\SignificantEventType;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\Invoice;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Services\CatalogService;
use PedroACF\Invoicing\Services\CodeService;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Services\InvoicingService;
use PedroACF\Invoicing\Services\KeyService;
use PedroACF\Invoicing\Services\OperationService;
use PedroACF\Invoicing\Services\TokenService;
use PedroACF\Invoicing\Utils\Generator;

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
//        $salePoint = SalePoint::where('sin_code', 0)->first();
//        //$service = new InvoicingService();
//        $service = app(InvoicingService::class);
//        $service->validatePackageReception($salePoint, 'a790b878-f4e9-11ed-8fed-cf42a2df9bec');
//        dd("fin");
        //$service = new OperationService();
        $this->readAndSetDelegateToken();

        $this->readAndSetConfigs();

        $publicKey = $this->readPublicKey();
        $this->keyService->addPublicKeyFromPem($publicKey);

        $privateKey = $this->readPrivateKey();
        $this->keyService->addPrivateKeyFromPem($privateKey);

        $this->initOperations();
        $salePoints = SalePoint::where('state', 'ACTIVE')->get();
        foreach ($salePoints as $salePoint){
            //$this->etapaI($salePoint, 1);//OK
            //$this->etapaII($salePoint, 50);//OK
            //$this->etapaIII($salePoint, 100);//OK
            $this->etapaI($salePoint, 1);//OK
            $this->etapaII($salePoint, 1);//OK
            $this->etapaIII($salePoint, 1);//OK
            $this->etapaIV($salePoint, 10);
            //$this->etapaV_VI($salePoint, 1);
            //$this->etapaVII($salePoint);
        }
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
        $this->configService->setInvoiceTypeCode(1);
        $this->configService->setBusinessName($businessName);
        $this->configService->setMunicipality($municipality);
        $this->configService->setOfficeCode(0);
        $this->configService->setOfficePhone($phone);
        $this->configService->setOfficeAddress($office_address);

        //OTROS para seguir
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

    private function initOperations(){
        $opService = app(OperationService::class);
        //Master sale point
        $salePoint = SalePoint::where('sin_code', 0)->where('state', 'ACTIVE')->first();
        $this->etapaI($salePoint, 1);
        $this->etapaII($salePoint, 1);
        // Listar y buscar punto de venta 1
        $resp = $opService->checkSalePoints($salePoint);//Lista activos
        $list = $resp->salePoints;
        if(!$this->findInArray(1, $list)){
            //crear punto de venta 1 en caso de no existir
            //get Sale Point Type
            $salePointType = SalePointType::where('descripcion', 'PUNTO DE VENTA CAJEROS')->first();
            $name = 'PV_1';
            $description = 'PV_1 Descripcion';
            $salePointOne = $opService->addSalePoint($salePoint, $salePointType, $name, $description);
            if($salePointOne==null){
                dump("no se pudo crear punto de venta 1");
            }
        }
        /** PARA CERRAR **/
//        $salePointToClose = SalePoint::where('sin_code', 6)->first();
//        $salePointClosed = $opService->closeSalePoint($salePoint, $salePointToClose);
//        dd($salePointClosed);
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
    private function etapaV_VI(SalePoint $salePoint, $testLimit = 0){
        $this->writeMessage("Etapa V: Registro de Eventos Significativos (punto de venta: $salePoint->sin_code)", true, 'warning');
        $codeService = app(CodeService::class);
        $eventTypes = SignificantEventType::all();
        $test = 1;
        $faker = Faker::create('es_PE');
        foreach($eventTypes as $eventType){
            if($test>$testLimit){
                break;
            }
            try{
                //crear event
                $service = app(OperationService::class);
                $event = $service->createSignificantEvent($salePoint, $eventType, $faker->regexify('[A-Z]{5}[0-4]{3}'));
                $cufd = $codeService->getCufdModel($salePoint, true);//Solo forzar para pruebas
                $closedEvent = $service->closeSignificantEvent($event, $cufd);

                $invoiceLimit = $salePoint->sin_code != '0'? 500: $faker->numberBetween(1, 499);
                $invoices = [];
                $generator = new Generator();
                for($i=0;$i<$invoiceLimit; $i++){
                    $invoices[] = $generator->generateTestInvoice();
                }
                $passed = $service->finishAndSendSignificantEvent($salePoint, $closedEvent, $invoices);
            }catch (\Exception $e){
                dump($e);
                $passed = false;
            }
            $this->write($test++, $testLimit, $passed);
        }
    }

    private function etapaIV(SalePoint $salePoint, $testLimit = 0){
        $invoicingService = app(InvoicingService::class);
        $this->writeMessage("Etapa IV: Consumo de metodos de emision individual (punto de venta: $salePoint->sin_code)", true, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            try{
                //Ejemplo de como generar factura
                //GENERAR FACTURA
                $generator = new Generator();
                $emission = EmissionType::where("descripcion", "EN LINEA")->first();
                $sale = $generator->generateTestSale($salePoint, $emission);
                $passed = $invoicingService->sendElectronicInvoice($salePoint, $sale);
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
                $cufd = $codeService->getCufdModel($salePoint, true);//true=>forzar nuevo cufd
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

    private function findInArray($salePointCode, $salePointList): bool{
        foreach($salePointList as $salePoint){
            if($salePoint->codigoPuntoVenta == $salePointCode){
                return true;
            }
        }
        return false;
    }
}
