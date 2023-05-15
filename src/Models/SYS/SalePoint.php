<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;
use PedroACF\Invoicing\Models\SIN\Cufd;
use PedroACF\Invoicing\Models\SIN\Cuis;

class SalePoint extends Model
{
    protected $table = 'sys_sale_points';

    public function cuisCodes(){
        return $this->hasMany(Cuis::class, 'sale_point', 'sin_code');
    }

    public function cufdCodes(){
        return $this->hasMany(Cufd::class, 'sale_point', 'sin_code');
    }

    public function getActiveCuisAttribute(){
        $active = $this->cuisCodes()->where('state', 'ACTIVE')->first();
        return $active? $active->code: null;
    }
}
