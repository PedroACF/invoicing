<?php

namespace PedroACF\Invoicing\Models\SYS;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public static $ENV_TEST = 2;
    public static $ENV_PROD = 1;

    public static $MODE_ELEC = 1;
    public static $MODE_COMP = 2;

    protected $table = 'sys_configs';

    protected $fillable = ['key', 'value', 'data_type'];
}
