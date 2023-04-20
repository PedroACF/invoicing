<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class SalePointType extends Model
{
    protected $table = 'siat_sale_point_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
