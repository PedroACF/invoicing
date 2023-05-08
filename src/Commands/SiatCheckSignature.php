<?php

namespace PedroACF\Invoicing\Commands;

use Carbon\Carbon;
use Faker\Factory as Faker;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use PedroACF\Invoicing\Invoices\DetailEInvoice;
use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Invoices\HeaderEInvoice;
use PedroACF\Invoicing\Models\SYS\Invoice;
use PedroACF\Invoicing\Services\KeyService;
use PedroACF\Invoicing\Utils\XmlSigner;

class SiatCheckSignature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siat:signature';
    private $invoicesCount = 0;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test signature invoices';

    private $keyService;
    //private $tokenService;

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

        $this->readPublicKey();
        $this->readPrivateKey();
        $this->readInvoicesCount();

        //utils
        $faker = Faker::create('es_PE');

        for($count=1; $count<=$this->invoicesCount; $count++){
            $now = Carbon::now();
            $invoiceHeader = new HeaderEInvoice();
            $invoiceHeader->nitEmisor = '1023757028';
            $invoiceHeader->razonSocialEmisor = 'GOBIERNO AUTÓNOMO MUNICIPAL DE POTOSÍ';
            $invoiceHeader->municipio = 'POTOSI';
            $invoiceHeader->telefono = '6223142';
            $invoiceHeader->numeroFactura = $count;
            //$invoiceHeader->cuf = 'ASDQW12';
            //$invoiceHeader->cufd = 'ASDQW12';
            $invoiceHeader->codigoSucursal = 0;
            $invoiceHeader->direccion = 'Plaza 10 de noviembre';
            $invoiceHeader->fechaEmision = $now->format("Y-m-d\TH:i:s.v");
            $invoiceHeader->nombreRazonSocial = $faker->name;
            $invoiceHeader->codigoTipoDocumentoIdentidad = $faker->numberBetween(1, 5);
            $invoiceHeader->numeroDocumento = $faker->randomNumber(8, true);
            $hasComplement = rand(0,1) == 1;
            if($hasComplement){
                $invoiceHeader->complemento = ($faker->randomLetter()).($faker->randomDigit());
            }
            $invoiceHeader->codigoCliente = $faker->randomNumber();
            $invoiceHeader->codigoMetodoPago = 1;
            $invoiceHeader->montoTotal = 0;
            $invoiceHeader->montoTotalSujetoIva = 0;
            $invoiceHeader->codigoMoneda = 1;
            $invoiceHeader->tipoCambio = 1.00000;
            $invoiceHeader->montoTotalMoneda = 0;
            $invoiceHeader->montoGiftCard = 0;
            $invoiceHeader->descuentoAdicional = 0.00;
            $invoiceHeader->codigoExcepcion = 0;//para nit invalidos 0=>registro normal, 1=> se autoriza el registro
            $invoiceHeader->leyenda = $faker->sentence(10);
            $invoiceHeader->usuario = $faker->regexify('[A-Z]{5}[0-4]{3}');
            $invoiceHeader->codigoDocumentoSector = 1;

            $eInvoice = new EInvoice('facturaElectronicaCompraVenta', $invoiceHeader);
            for($i=0;$i<$faker->numberBetween(1, 3); $i++){
                $detail = new DetailEInvoice();
                $detail->actividadEconomica = $faker->randomNumber();
                $detail->codigoProductoSin = $faker->randomNumber();
                $detail->codigoProducto = $faker->randomNumber();
                $detail->descripcion = $faker->sentence(10);
                $qty = $faker->randomNumber(1, 10);
                $detail->cantidad = $qty;
                $detail->unidadMedida = $faker->randomNumber();
                $price = $faker->randomFloat(5, 1, 100);
                $detail->precioUnitario = $price;
                $detail->montoDescuento = 0.0;
                $detail->subTotal = $qty*$price;
                $detail->numeroImei = 0.0;
                $eInvoice->addDetail($detail);
            }
            $signer = new XmlSigner();
            $signedXml = $signer->sign($eInvoice->toXml()->saveXML());

            $model = new Invoice();
            $model->content = $signedXml;
            $model->user_id = 0;
            $model->save();
            $model->refresh();
            $stream = stream_get_contents($model->content);

            $client = new Client();
            $response = $client->post('https://validar.firmadigital.bo/rest/validar/', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'tipo' => 'text/xml',
                    'base64' => base64_encode($stream)
                ]
            ]);

            $resp = $response->getBody()->getContents();
            $jsonResp = json_decode($resp);

            $object = $jsonResp[0];
            $number = str_pad($count, 3, "0", STR_PAD_LEFT);
            $this->writeMessage("$number: $model->id > ".($object->cadenaConfianza? 'correcto': 'incorrecto').' | '.($object->noModificado? 'sin modificacion': 'modificado'), false, $object->cadenaConfianza && $object->noModificado? 'info': 'error');
        }
    }

    private function readInvoicesCount(){
        $rules = [ 'counter' => 'required|numeric' ];
        $showPrompt = true;
        while($showPrompt){
            $counter = $this->ask('Cantidad de facturas a generar: ');
            $validator = Validator::make(['counter' => $counter], $rules);
            if($validator->fails()){
                $errors = $validator->errors()->messages()['counter'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
                continue;
            }
            $showPrompt = false;
            $this->invoicesCount = $counter;
        }
    }

    private function readPublicKey(){
        $showPrompt = true;
        while($showPrompt){
            $keyPath = $this->ask('Ruta del certificado publico (.pem): ');
            $validator = Validator::make([], []);
            try{
                $content = file_get_contents($keyPath);
                $this->keyService->addPublicKeyFromPem($content);
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
            $keyPath = $this->ask('Ruta del certificado privado (.pem): ');
            $validator = Validator::make([], []);
            try{
                $content = file_get_contents($keyPath);
                //$this->privateKeyContent = $content;
                $this->keyService->addPrivateKeyFromPem($content);
              //  $this->privateKeyPassword = $keyPassword;
                $showPrompt = false;
            }catch(\Exception $e){
                $validator->errors()->add('key', $e->getMessage());
                $errors = $validator->errors()->messages()['key'];
                $errors = implode(', ', $errors);
                $this->writeMessage("> Error: $errors", false, 'error');
            }
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
