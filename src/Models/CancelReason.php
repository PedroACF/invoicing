<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class CancelReason extends Model
{
    protected $table = 'siat_cancel_reasons';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
