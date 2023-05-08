<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'sin_messages';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
