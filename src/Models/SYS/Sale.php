<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    public const ENUM_VALID = 'VALIDA';
    public const ENUM_REJECTED = 'RECHAZADO';
    public const ENUM_PENDANT = 'PENDIENTE DE ENVIO';

    protected $table = 'sys_sales';

    public function details(){
        return $this->hasMany(SaleDetail::class);
    }

    public function buyer(){
        return $this->belongsTo(Buyer::class);
    }

    public static function getEnumTypes(): array{
        return [self::ENUM_VALID, self::ENUM_REJECTED, self::ENUM_PENDANT];
    }
}
