<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class SectorDocType extends Model
{
    protected $table = 'sin_sector_doc_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
