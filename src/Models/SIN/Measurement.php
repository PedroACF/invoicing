<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    protected $table = 'sin_measurement';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
