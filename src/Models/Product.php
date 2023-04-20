<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'siat_products';
    protected $primaryKey = 'codigo_producto';
    public $incrementing = false;
}
