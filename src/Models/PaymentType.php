<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    protected $table = 'siat_payment_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
