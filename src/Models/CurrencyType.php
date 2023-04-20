<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyType extends Model
{
    protected $table = 'siat_currency_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
