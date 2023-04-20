<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'siat_messages';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
