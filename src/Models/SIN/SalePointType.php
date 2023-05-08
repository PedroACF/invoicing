<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class SalePointType extends Model
{
    protected $table = 'sin_sale_point_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
