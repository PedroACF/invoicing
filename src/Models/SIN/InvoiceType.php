<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class InvoiceType extends Model
{
    protected $table = 'sin_invoice_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
