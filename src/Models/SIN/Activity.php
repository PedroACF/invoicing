<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'sin_activities';
    protected $primaryKey = 'codigo_caeb';
    public $incrementing = false;
}
