<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class EmissionType extends Model
{
    protected $table = 'sin_emission_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
