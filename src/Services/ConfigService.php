<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Models\Config;
use PedroACF\Invoicing\Models\DelegateToken;
use PedroACF\Invoicing\Repositories\DataSyncRepository;

class ConfigService extends BaseService
{
    public static function addDelegateToken(string $nit = '', int $sucursal = 0, string $token = '', $expireDate = ''){
        DelegateToken::where('activo', true)->update(['activo'=> false]);
        $newToken = new DelegateToken();
        $newToken->nit = $nit;
        $newToken->sucursal = $sucursal;
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

    public static function setConfigs(string $nit, string $business_name, string $municipality, string $phone, int $office, int $sale_point){
        $config = Config::first();
        if(!$config){
            $config = new Config();
        }
        $config->nit = $nit;
        $config->business_name = $business_name;
        $config->municipality = $municipality;;
        $config->phone = $phone;
        $config->office = $office;
        $config->sale_point = $sale_point;
        $config->save();
    }

    public static function getConfigs(){
        return Config::first();
    }
}
