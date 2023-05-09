<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'sys_configs';


    public static function getNitConfig(){
        return Config::firstOrCreate([
            'key' => 'NIT'
        ], [
            'data_type' => 'int'
        ]);
    }

    public static function setNitConfig(int $newVal){
        return Config::firstOrCreate([
            'key' => 'NIT'
        ], [
            'data_type' => 'int',
            'value' => (string) $newVal
        ]);
    }

    public static function getBusinessNameConfig(){
        return Config::firstOrCreate([
            'key' => 'BUSINESS_NAME'
        ], [
            'data_type' => 'string'
        ]);
    }

    public static function setBusinessNameConfig(string $newVal){
        return Config::firstOrCreate([
            'key' => 'BUSINESS_NAME'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
    }

    public static function getBusinessPhoneConfig(){
        return Config::firstOrCreate([
            'key' => 'BUSINESS_PHONE'
        ], [
            'data_type' => 'string'
        ]);
    }

    public static function setBusinessPhoneConfig(string $newVal){
        return Config::firstOrCreate([
            'key' => 'BUSINESS_PHONE'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
    }

    public static function getMunicipalityConfig(){
        return Config::firstOrCreate([
            'key' => 'Municipality'
        ], [
            'data_type' => 'string'
        ]);
    }

    public static function setMunicipalityConfig(string $newVal){
        return Config::firstOrCreate([
            'key' => 'Municipality'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
    }

    public static function getOfficeCodeConfig(){
        return Config::firstOrCreate([
            'key' => 'OFFICE_CODE'
        ], [
            'data_type' => 'int'
        ]);
    }

    public static function setOfficeCodeConfig(int $newVal = 0){ //0=>Casa matriz
        return Config::firstOrCreate([
            'key' => 'OFFICE_CODE'
        ], [
            'data_type' => 'int',
            'value' => $newVal
        ]);
    }

    public static function getOfficePhoneConfig(){
        return Config::firstOrCreate([
            'key' => 'OFFICE_PHONE'
        ], [
            'data_type' => 'string'
        ]);
    }

    public static function setOfficePhoneConfig(string $newVal = ''){
        return Config::firstOrCreate([
            'key' => 'OFFICE_PHONE'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
    }

    public static function getOfficeAddressConfig(){
        return Config::firstOrCreate([
            'key' => 'OFFICE_ADDRESS'
        ], [
            'data_type' => 'string'
        ]);
    }

    public static function setOfficeAddressConfig(string $newVal = ''){
        return Config::firstOrCreate([
            'key' => 'OFFICE_ADDRESS'
        ], [
            'data_type' => 'string',
            'value' => $newVal
        ]);
    }

    public static function getServerTimeDiffConfig(){
        return Config::firstOrCreate([
            'key' => 'SERVER_TIME_DIFF'
        ], [
            'data_type' => 'int'
        ]);
    }

    public static function setServerTimeDiffConfig(int $newVal = 0){
        return Config::firstOrCreate([
            'key' => 'SERVER_TIME_DIFF'
        ], [
            'data_type' => 'int',
            'value' => $newVal
        ]);
    }

    public static function getLastInvoiceNumberConfig(){
        return Config::firstOrCreate([
            'key' => 'LAST_INVOICE_NUMBER'
        ], [
            'data_type' => 'bigint'
        ]);
    }

    public static function setLastInvoiceNumberConfig(int $newVal = 0){
        return Config::firstOrCreate([
            'key' => 'LAST_INVOICE_NUMBER'
        ], [
            'data_type' => 'bigint',
            'value' => $newVal
        ]);
    }
}
