<?php

namespace PedroACF\Invoicing\Models\SIN;

use Illuminate\Database\Eloquent\Model;

class IdentityDocType extends Model
{
    protected $table = 'sin_identity_doc_types';
    protected $primaryKey = 'codigo_clasificador';
    public $incrementing = false;
}
