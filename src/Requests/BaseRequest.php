<?php
namespace PedroACF\Invoicing\Requests;

class BaseRequest
{
    protected $requestName = "_";

    public function toArray(){
        $array = [];
        foreach($this as $key => $value){
            if($key!='requestName'){
                $array[$key] = $value;
            }
        }
        return [
            $this->requestName => $array
        ];
    }
}
