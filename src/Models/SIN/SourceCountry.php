<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class SourceCountry extends Model
{
    protected $table = 'sin_source_countries';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
