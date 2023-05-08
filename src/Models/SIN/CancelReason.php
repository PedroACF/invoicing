<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class CancelReason extends Model
{
    protected $table = 'sin_cancel_reasons';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
