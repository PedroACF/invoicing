<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Exceptions\TokenNotFoundException;
use PedroACF\Invoicing\Models\SYS\DelegateToken;

class TokenService
{
    public function addDelegateToken(string $token, Carbon $expiredDate){
        DelegateToken::where('activo', true)->update(['activo'=> false]);
        $newToken = new DelegateToken();
        $newToken->token = $token;
        $newToken->fecha_expiracion = $expiredDate;
        $newToken->activo = true;
        $newToken->save();
    }

    public function getDelegateToken(): DelegateToken{
        $tokenModel = DelegateToken::where([
            ['activo', '=', true]
        ])->orderBy('created_at', 'desc')->first();
        if(!$tokenModel){
            throw new TokenNotFoundException();
        }
        return $tokenModel;
    }
}
