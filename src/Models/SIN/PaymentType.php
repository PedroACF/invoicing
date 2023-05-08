<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $table = 'sin_payment_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
