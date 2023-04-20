<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class SourceCountry extends Model
{
    protected $table = 'siat_source_countries';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
