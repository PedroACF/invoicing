<?php

namespace PedroACF\Invoicing\Services;

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
}
