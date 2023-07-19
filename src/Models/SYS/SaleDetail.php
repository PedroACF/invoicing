<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{

    protected $table = 'sys_sale_details';

    public function sale(){
        return $this->belongsTo(Sale::class);
    }
}
