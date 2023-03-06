<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Repositories\CodeRepository;
use PedroACF\Invoicing\Requests\Code\CuisRequest;
use PedroACF\Invoicing\Responses\Code\CodeComunicacionResponse;
use PedroACF\Invoicing\Responses\Code\CuisResponse;

class CodeService
{
    private $repo;

    public function __construct()
    {
        $repo = new CodeRepository();
    }

    public function getCuisCode(){
        $cuisReq = new CuisRequest();
        $response = $this->repo->cuis($cuisReq);
        return CuisResponse::build($response);
    }

    public function verificarComunicacion(){
        $response = $this->repo->verificarComunicacion();
        return CodeComunicacionResponse::build($response);
    }
}
