<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    protected $table = 'siat_measurement';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
