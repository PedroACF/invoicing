<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class SslKey extends Model
{
    public const PUBLIC_KEY = 'PUBLIC';
    public const PRIVATE_KEY = 'PRIVATE';

    protected $table = 'sys_ssl_keys';

    public static function getEnumTypes(): array{
        return [self::PUBLIC_KEY, self::PRIVATE_KEY];
    }
}
