<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    protected $table = 'sys_buyers';

    public function sales(){
        return $this->hasMany(Sale::class);
    }
}
