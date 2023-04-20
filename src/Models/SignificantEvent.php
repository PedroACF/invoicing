<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class SignificantEvent extends Model
{
    protected $table = 'siat_significant_events';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
