<?php

namespace PedroACF\Invoicing\Models\SYS;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DelegateToken extends Model
{
    protected $table = 'sys_delegate_tokens';
    public function getRemainingDaysAttribute(){
        $now = new Carbon();
        $now->startOfDay();
        $expired = new Carbon($this->fecha_expiracion);
        $expired->startOfDay();
        return $now->diffInDays($expired);
    }

    public static function getActiveToken(){
        return DelegateToken::where('activo', 1)
            ->whereDate('fecha_expiracion', '>', date('Y-m-d'))->first();
    }
}
