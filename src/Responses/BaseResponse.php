<?php
namespace PedroACF\Invoicing\Responses;
use Illuminate\Support\Arr;

class BaseResponse
{
    //protected $responseName = "_";
    public $mensajes = [];
    public $transaccion = false;

    public function buildBase($data){//
        $this->transaccion = $data->transaccion ?? false;
        $messageData = $data->mensajesList??[];
        $messages = is_array($messageData)? $messageData: [ $messageData ];
        foreach ($messages as $message){
            $this->mensajes[] = new Message($message->codigo??0, $message->descripcion??'', "");
        }
    }

    public function hasCodes($codes){
        $list = is_array($codes)? $codes: [$codes];
        $filtered = Arr::where($this->mensajes, function(Message $message) use ($list) {
            return in_array($message->code, $list);
        });
        return count($filtered)>0;
    }
}
