<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Models\Config;
use PedroACF\Invoicing\Models\DelegateToken;
use PedroACF\Invoicing\Repositories\DataSyncRepository;

class ConfigService extends BaseService
{
    public static function addDelegateToken(string $token = '', $expireDate = ''){
        DelegateToken::where('activo', true)->update(['activo'=> false]);
        $newToken = new DelegateToken();
        $newToken->token = $token;
        $newToken->fecha_expiracion = $expireDate;
        $newToken->activo = true;
        $newToken->save();
    }

    public static function getDelegateToken(): ?DelegateToken{
        return DelegateToken::where([
            ['activo', '=', true]
        ])->orderBy('created_at', 'desc')->first();
    }

    public static function setConfigs(string $nit, string $business_name, string $municipality, string $phone, int $office, string $office_address, int $sale_point){
        $config = Config::first();
        if(!$config){
            $config = new Config();
        }
        $config->nit = $nit;
        $config->business_name = $business_name;
        $config->municipality = $municipality;;
        $config->phone = $phone;
        $config->office = $office;
        $config->office_address = $office_address;
        $config->sale_point = $sale_point;
        $config->save();
    }

    public static function getConfigs(){
        return Config::first();
    }

    public static function setConfigSalePoint(int $salePoint){
        $config = Config::first();
        if($config){
            $config->sale_point = $salePoint;
            $config->save();
        }
    }

    public static function getAvailableInvoiceNumber(){
        $config = self::getConfigs();
        if($config){
            $availableNumber = $config->last_invoice_number;
            $availableNumber++;
            $config->last_invoice_number = $availableNumber;
            $config->save();
            return $availableNumber;
        }
        return -1;
    }

    public static function getTime(){
        $config = self::getConfigs();
        $difference = $config? $config->server_time_diff: 0;
        $now = Carbon::now();
        $now->addMilliseconds($difference);
        return $now;
    }

    public static function setTimeDiff($difference){
        $config = self::getConfigs();
        if($config){
            $config->server_time_diff = $difference;
            $config->save();
        }
    }
}
