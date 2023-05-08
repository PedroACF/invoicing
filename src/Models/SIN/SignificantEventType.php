<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class SignificantEventType extends Model
{
    protected $table = 'sin_significant_event_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
