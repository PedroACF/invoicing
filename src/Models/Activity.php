<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'siat_activities';
    protected $primaryKey = 'codigo_caeb';
    public $incrementing = false;
}
