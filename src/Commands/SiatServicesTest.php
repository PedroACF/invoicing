<?php

namespace PedroACF\Invoicing\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use PedroACF\Invoicing\Invoices\DetailEInvoice;
use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Invoices\HeaderEInvoice;
use PedroACF\Invoicing\Services\KeyService;
use PedroACF\Invoicing\Utils\XmlSigner;

use Faker\Factory as Faker;

class SiatServicesTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siat:test';
    private $salePoint = 0;
    private $delegatedToken = '';
    private $dateToken = '';
    private $privateKeyContent = '';
    private $privateKeyExtension = '';
    private $privateKeyPassword = '';
    private $publicKeyContent = '';
    private $publicKeyExtension = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test invoice package';

    private $keyService;
    private $tokenService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->keyService = new KeyService();
        //$this->tokenService = new Service
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->readDelegateToken();
        $this->readDateToken();
        $this->readPublicKey();
        $this->readPrivateKey();

        if($this->publicKeyExtension == 'crt'){
            $this->keyService->addPublicKeyFromCrt($this->publicKeyContent);
        }else{//pem
            $this->keyService->addPublicKeyFromPem($this->publicKeyContent);
        }

        if($this->privateKeyExtension == 'p12'){
            $this->keyService->addPrivateKeyFromP12($this->privateKeyContent, $this->privateKeyPassword);
        }else{//pem
            $this->keyService->addPrivateKeyFromPem($this->privateKeyContent);
        }

        //dd($eInvoice->getSignedInvoiceXml());

        $this->salePoint = 0;
        while($this->salePoint<=1){
            $this->etapaI();
            $this->etapaII();
            $this->etapaIII();
            $this->etapaIV();
            $this->etapaV();
            $this->etapaVI();
            $this->etapaVII();
            $this->etapaVIII();
            $this->salePoint++;
        }
    }

    private function readDelegateToken(){
        $tokenValidator = [ 'token' => 'required' ];
        $showPrompt = true;

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
                if($data->codigoSistema != config("siat_invoicing.system_code")){
                    $validator->errors()->add('token', "Codigo de sistema incorrecto para el token ingresado");
                    $errors = $validator->errors()->messages()['token'];
                    $errors = implode(', ', $errors);
                    $this->writeMessage("> Error: $errors", false, 'error');
                    continue;
                }
                $showPrompt = false;
                $this->delegatedToken = $dToken;
            }catch(\Exception $e){
                $validator->errors()->add('token', $e->getMessage());
                $errors = $validator->errors()->messages()['token'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
            }
        }
    }

    private function readDateToken(){
        $dateValidator = [ 'date' => 'required|date_format:Y-m-d' ];
        $showPrompt = true;
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
    }

    private function readPublicKey(){
        $showPrompt = true;
        while($showPrompt){
            $keyPath = $this->ask('Ruta del certificado publico (.crt, .pem): ');
            $validator = Validator::make([], []);
            try{
                $content = file_get_contents($keyPath);
                $this->publicKeyContent = $content;
                $this->publicKeyExtension = last(explode(".", $keyPath));
                $showPrompt = false;
            }catch(\Exception $e){
                $validator->errors()->add('key', $e->getMessage());
                $errors = $validator->errors()->messages()['key'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
            }
        }
    }

    private function readPrivateKey(){
        $showPrompt = true;
        while($showPrompt){
            $keyPath = $this->ask('Ruta del certificado privado (.pem, .p12): ');
            $keyPassword = $this->ask('Password para el certificado privado: ');
            $validator = Validator::make([], []);
            try{
                $content = file_get_contents($keyPath);
                $this->privateKeyContent = $content;
                $this->privateKeyExtension = last(explode(".", $keyPath));
                $this->privateKeyPassword = $keyPassword;
                $showPrompt = false;
            }catch(\Exception $e){
                $validator->errors()->add('key', $e->getMessage());
                $errors = $validator->errors()->messages()['key'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
            }
        }
    }

    private function etapaVIII(){
        $testLimit = 115;
        $this->writeMessage("Etapa VIII: Firma digital (punto de venta: $this->salePoint)", true, 'warning');
    }

    private function etapaVII(){
        $testLimit = 125;
        $this->writeMessage("Etapa VII: Anulacion (punto de venta: $this->salePoint)", true, 'warning');
    }
    private function etapaVI(){
        $testLimit = 10;
        $this->writeMessage("Etapa VI: Consumo de metodos de emision de paquetes (punto de venta: $this->salePoint)", true, 'warning');
    }
    private function etapaV(){
        $testLimit = 5;
        $this->writeMessage("Etapa V: Registro de Eventos Significativos (punto de venta: $this->salePoint)", true, 'warning');
    }

    private function etapaIV(){
        $this->writeMessage("Etapa IV: Consumo de metodos de emision individual (punto de venta: $this->salePoint)", true, 'warning');
        $testLimit = 125;
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
    }

    private function etapaIII(){
        $this->writeMessage("Etapa III: Obtencion CUFD (punto de venta: $this->salePoint)", true, 'warning');
        $testLimit = 100;
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
    }

    private function etapaII(){
        $this->writeMessage("Etapa II: Sincronizacion de catalogos (punto de venta: $this->salePoint)", true, 'warning');
        $testLimit = 50;//para todos los casos
        $this->writeMessage("* 01 LISTADO TOTAL DE ACTIVIDADES *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 02 FECHA Y HORA ACTUAL *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 03 LISTADO TOTAL DE ACTIVIDADES DOCUMENTO SECTOR *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 04 LISTADO TOTAL DE LEYENDAS DE FACTURAS", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 05 LISTADO TOTAL DE MENSAJES DE SERVICIOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 06 LISTADO TOTAL DE PRODUCTOS Y SERVICIOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 07 LISTADO TOTAL DE EVENTOS SIGNIFICATIVOS *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 08 LISTADO TOTAL DE MOTIVOS DE ANULACION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 09 LISTADO TOTAL DE PAISES *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 10 LISTADO TOTAL DE TIPOS DE DOCUMENTO DE IDENTIDAD *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 11 LISTADO TOTAL DE TIPOS DE DOCUMENTO SECTOR *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 12 LISTADO TOTAL DE TIPOS DE EMISION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 13 LISTADO TOTAL DE TIPO HABITACION *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 14 LISTADO TOTAL DE METODO DE PAGO *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 15 LISTADO TOTAL DE TIPOS DE MONEDA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 16 LISTADO TOTAL DE TIPOS DE PUNTO DE VENTA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 17 LISTADO TOTAL DE TIPOS DE FACTURA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
        $this->writeMessage("* 18 LISTADO TOTAL DE UNIDAD DE MEDIDA *", false, 'warning');
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
            $number = str_pad($test, 3, "0", STR_PAD_LEFT);
            $limit = str_pad($testLimit, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number/$limit > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
        }
    }

    private function etapaI(){
        $this->writeMessage("Etapa I: Obtencion de CUIS (punto de venta: $this->salePoint)", true, 'warning');
        $testLimit = 1;
        for($test = 1; $test<=$testLimit; $test++){
            $passed = (bool)rand(0,1);
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
