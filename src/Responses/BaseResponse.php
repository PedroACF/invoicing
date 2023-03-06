<?php
namespace PedroACF\Invoicing\Responses;
use Illuminate\Support\Arr;

class BaseResponse
{
    //protected $responseName = "_";
    public $mensaje = [];
    public $transaccion = false;

    public function buildBase($resp){
        $this->transaccion = Arr::get($resp, 'transaccion', false);
        $list = Arr::get($resp, 'mensajesList', []);
        $mensaje = new Mensaje(Arr::get($list, 'codigo', 0), Arr::get($list, 'descripcion', ''));
        $this->mensaje = $mensaje;
    }
}
