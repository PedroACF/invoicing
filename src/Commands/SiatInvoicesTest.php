<?php

namespace PedroACF\Invoicing\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class SiatInvoicesTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siat:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tokenValidator = [ 'token' => 'required' ];
        $dateValidator = [ 'date' => 'required|date_format:Y-m-d' ];
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
            $showPrompt = false;
        }

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
        }

        $salePoint = 0;
        while($salePoint<=1){
            $this->writeMessage("Etapa I: Obtencion de CUIS (punto de venta: $salePoint)", true, 'warning');
            $testLimit = 1;
            for($test = 1; $test<=$testLimit; $test++){
                $passed = (bool)rand(0,1);
                $number = str_pad($test, 3, "0", STR_PAD_LEFT);
                $this->writeMessage("$number > ".($passed? 'passed': 'not pass'), false, $passed? 'info': 'error');
            }
            $this->writeMessage("Etapa II: Sincronizacion de catálogos (punto de venta: $salePoint)", true, 'warning');
            $this->writeMessage("Etapa III: Obtencion CUFD (punto de venta: $salePoint)", true, 'warning');
            $this->writeMessage("Etapa IV: Consumo de métodos de emisión individual (punto de venta: $salePoint)", true, 'warning');
            $this->writeMessage("Etapa V: Registro de Eventos Significativos (punto de venta: $salePoint)", true, 'warning');
            $this->writeMessage("Etapa VI: Consumo de métodos de emisión de paquetes (punto de venta: $salePoint)", true, 'warning');
            $this->writeMessage("Etapa VII: Anulación (punto de venta: $salePoint)", true, 'warning');
            $this->writeMessage("Etapa VIII: Firma digital (punto de venta: $salePoint)", true, 'warning');
            $salePoint++;
        }
    }

    private function writeMessage($message = "", $withBorder = false, $type = ''){
        $color = '';

        $borderLength = strlen($message) + 2;
        $border = '<info>+'.str_pad('-', $borderLength, '-').'+</info>';
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
                $rText = "<fg=yellow>$text</fg=yellow>";
                break;
            default:
                $rText = $text;
                break;
        }
        return $rText;
    }
}
