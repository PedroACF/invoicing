<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\DelegateToken;

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

//    public static function setConfigs(string $nit, string $business_name, string $municipality, string $phone, int $office, string $office_address){
//        $config = Config::first();
//        if(!$config){
//            $config = new Config();
//        }
//        $config->nit = $nit;
//        $config->business_name = $business_name;
//        $config->municipality = $municipality;;
//        $config->phone = $phone;
//        $config->office = $office;
//        $config->office_address = $office_address;
//        $config->save();
//    }
//
//    public static function getConfigs(){
//        return Config::first();
//    }

    public static function getAvailableInvoiceNumber(){
        $config = Config::getLastInvoiceNumberConfig();
        $lastNumberModel = (int)$config->value;
        $lastNumberModel++;
        Config::setLastInvoiceNumberConfig($lastNumberModel);
        return $lastNumberModel;
    }

    public static function getTime(){
        $config = Config::getServerTimeDiffConfig();
        $difference = (int)$config->value;
        $now = Carbon::now();
        $now->addMilliseconds($difference);
        return $now;
    }

    public static function setTimeDiff($difference){
        Config::setLastInvoiceNumberConfig($difference);
    }
}
