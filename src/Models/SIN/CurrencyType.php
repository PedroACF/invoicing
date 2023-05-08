<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class CurrencyType extends Model
{
    protected $table = 'sin_currency_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
