<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class Legend extends Model
{
    protected $table = 'siat_legends';
    protected $primaryKey = 'codigo_actividad';
    public $incrementing = false;
}
