<?php

namespace PedroACF\Invoicing\Utils;

use PedroACF\Invoicing\Exceptions\TokenNotFoundException;
use PedroACF\Invoicing\Models\SYS\DelegateToken;
use PedroACF\Invoicing\Services\ConfigService;

class TokenUtils
{
    public static function getValidTokenReg(): DelegateToken{
        $lastToken = ConfigService::getDelegateToken();
        if(!$lastToken){
            throw new TokenNotFoundException();
        }
        return $lastToken;
    }
}
