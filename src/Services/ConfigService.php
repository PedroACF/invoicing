<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Exceptions\BadConfigException;
use PedroACF\Invoicing\Exceptions\ConfigException;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\DelegateToken;

class ConfigService extends BaseService
{
    private $nit = null;
    private $businessName = null;
    private $municipality = null;
    private $officeCode = null;
    private $officePhone = null;
    private $officeAddress = null;
    private $environment = null;//1 => PRODUCCION, 2 => PRUEBAS
    private $invoiceMode = null;//1 => ELECTRONICA, 2 => COMPUTARIZADA
    private $systemCode = null;
    private $sectorDocumentCode = null;

    public function getAvailableInvoiceNumber(): int{
        $model = $this->getLastInvoiceNumber();
        $lastNumber = (int)($model->value);
        $lastNumber++;
        $this->setLastInvoiceNumber($lastNumber);
        return $lastNumber;
    }

    public function getTime(): Carbon{
        $model = $this->getServerTimeDiff();
        $difference = (int)($model->value);
        $now = Carbon::now();
        $now->addMilliseconds($difference);
        return $now;
    }

    public function getNit(){
        if($this->nit == null){
            $model = Config::where('key', 'NIT')->first();
            if($model && strlen($model->value)>0){
                $this->nit = $model->value;
            }else{
                throw new ConfigException('NIT no configurado');
            }
        }
        return $this->nit;
    }

    public function setNit(string $newVal){
        Config::firstOrCreate([
            'key' => 'NIT'
        ], [
            'data_type' => 'int',
            'value' => $newVal
        ]);
        $this->nit = null;
    }

    public function getBusinessName(){
        if($this->businessName == null){
            $model = Config::where('key', 'BUSINESS_NAME')->first();
            if($model && strlen($model->value)>0){
                $this->businessName = $model->value;
            }else{
                throw new ConfigException('Razon social no configurada');
            }
        }
        return $this->businessName;
    }

    public function setBusinessName(string $newVal){
        Config::firstOrCreate([
            'key' => 'BUSINESS_NAME'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
        $this->businessName = null;
    }

    public function getMunicipality(){
        if($this->municipality == null){
            $model = Config::where('key', 'BUSINESS_NAME')->first();
            $this->municipality = $model? $model->value: '';
        }
        return $this->municipality;
    }

    public function setMunicipality(string $newVal){
        Config::firstOrCreate([
            'key' => 'MUNICIPALITY'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
        $this->municipality = null;
    }

    public function getOfficeCode(){
        if($this->officeCode == null){
            $model = Config::where('key', 'OFFICE_CODE')->first();
            if($model && strlen($model->value)>0 ){
                $this->officeCode = (int)$model->value;
            }else{
                throw new ConfigException('Sucursal no configurada');
            }
        }
        return $this->officeCode;
    }

    public function setOfficeCode(int $newVal = 0){ //0=>Casa matriz
        Config::firstOrCreate([
            'key' => 'OFFICE_CODE'
        ], [
            'data_type' => 'int',
            'value' => $newVal
        ]);
        $this->officeCode = null;
    }

    public function getOfficePhone(){
        if($this->officePhone == null){
            $model = Config::where('key', 'OFFICE_PHONE')->first();
            $this->officePhone = $model? $model->value: '';
        }
        return $this->officePhone;
    }

    public function setOfficePhone(string $newVal = ''){
        Config::firstOrCreate([
            'key' => 'OFFICE_PHONE'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
        $this->officePhone = null;
    }

    public function getOfficeAddress(){
        if($this->officeAddress == null){
            $model = Config::where('key', 'OFFICE_ADDRESS')->first();
            $this->officeAddress = $model? $model->value: '';
        }
        return $this->officeAddress;
    }

    public function setOfficeAddress(string $newVal = ''){
        Config::firstOrCreate([
            'key' => 'OFFICE_ADDRESS'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
        $this->officeAddress = null;
    }

    public function getEnvironment(){
        if($this->environment == null){
            $model = Config::where('key', 'ENVIRONMENT')->first();
            if($model && strlen($model->value)>0 ){
                $this->environment = (int)$model->value;
            }else{
                throw new ConfigException('Codigo de entorno no configurada');
            }
        }
        return $this->environment;
    }

    public function setEnvironment(int $newVal = 2){//2 es pruebas
        if(!($newVal == 1 || $newVal == 2)){
            throw new BadConfigException("Configuracion de entorno incorrecta");
        }
        Config::firstOrCreate([
            'key' => 'ENVIRONMENT'
        ], [
            'data_type' => 'int',
            'value' => (string)$newVal
        ]);
        $this->environment = null;
    }

    public function getSystemCode(){
        if($this->systemCode == null){
            $model = Config::where('key', 'SYSTEM_CODE')->first();
            if($model && strlen($model->value)>0 ){
                $this->systemCode = $model->value;
            }else{
                throw new ConfigException('Codigo de Sistema no configurada');
            }
        }
        return $this->systemCode;
    }

    public function setSystemCode(string $newVal){
        Config::firstOrCreate([
            'key' => 'SYSTEM_CODE'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
        $this->systemCode = null;
    }

    public function getInvoiceMode(){//Modalidad
        if($this->invoiceMode == null){
            $model = Config::where('key', 'INVOICE_MODE')->first();
            if($model && strlen($model->value)>0 ){
                $this->invoiceMode = $model->value;
            }else{
                throw new ConfigException('Modalidad no configurada');
            }
        }
        return $this->invoiceMode;
    }

    public function setInvoiceMode(int $newVal){
        Config::firstOrCreate([
            'key' => 'INVOICE_MODE'
        ], [
            'data_type' => 'int',
            'value' => (string)$newVal
        ]);
        $this->invoiceMode = null;
    }

    public function getSectorDocumentCode(){//Codigo Documento sector
        if($this->sectorDocumentCode == null){
            $model = Config::where('key', 'SECTOR_DOCUMENT_CODE')->first();
            if($model && strlen($model->value)>0 ){
                $this->sectorDocumentCode = (int)$model->value;
            }else{
                throw new ConfigException('Documento sector no configurado');
            }
        }
        return $this->sectorDocumentCode;
    }

    public function setSectorDocumentCode(int $newVal){
        Config::firstOrCreate([
            'key' => 'SECTOR_DOCUMENT_CODE'
        ], [
            'data_type' => 'int',
            'value' => (string)$newVal
        ]);
        $this->sectorDocumentCode = null;
    }

    public function getServerTimeDiff(){
        return Config::firstOrCreate([
            'key' => 'SERVER_TIME_DIFF'
        ], [
            'data_type' => 'int',
            'value' => '0'
        ]);
    }

    public function setServerTimeDiff(int $newVal = 0){
        Config::firstOrCreate([
            'key' => 'SERVER_TIME_DIFF'
        ], [
            'data_type' => 'int',
            'value' => $newVal
        ]);
    }

    public function getLastInvoiceNumber(){
        return Config::firstOrCreate([
            'key' => 'LAST_INVOICE_NUMBER'
        ], [
            'data_type' => 'bigint',
            'value' => '0'
        ]);
    }

    public function setLastInvoiceNumber(int $newVal = 0){
        Config::firstOrCreate([
            'key' => 'LAST_INVOICE_NUMBER'
        ], [
            'data_type' => 'bigint',
            'value' => (string)$newVal
        ]);
    }
}
