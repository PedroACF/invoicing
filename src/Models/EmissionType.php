<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class EmissionType extends Model
{
    protected $table = 'siat_emission_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
