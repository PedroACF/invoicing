<?php

namespace PedroACF\Invoicing\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $table = 'siat_room_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
