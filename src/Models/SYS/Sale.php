<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    public const ENUM_VALID = 'VALID';
    public const ENUM_REJECTED = 'REJECTED';
    public const ENUM_SENT = 'SENT';
    public const ENUM_PENDANT = 'PENDANT';

    protected $table = 'sys_sales';

    public function details(){
        return $this->hasMany(SaleDetail::class);
    }

    public function buyer(){
        return $this->belongsTo(Buyer::class);
    }

    public static function getEnumTypes(): array{
        return [self::ENUM_VALID, self::ENUM_REJECTED, self::ENUM_SENT, self::ENUM_PENDANT];
    }
}
