<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public const ENUM_VALID = 'VALID';
    public const ENUM_REJECTED = 'REJECTED';
    public const ENUM_SENT = 'SENT';
    public const ENUM_PENDANT = 'PENDANT';

    protected $table = 'sys_packages';

    public static function getEnumTypes(): array{
        return [self::ENUM_VALID, self::ENUM_REJECTED, self::ENUM_SENT, self::ENUM_PENDANT];
    }
}
