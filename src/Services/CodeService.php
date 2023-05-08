<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Models\SIN\Cufd;
use PedroACF\Invoicing\Models\SIN\Cuis;
use PedroACF\Invoicing\Repositories\CodeRepository;
use PedroACF\Invoicing\Requests\Code\CufdRequest;
use PedroACF\Invoicing\Requests\Code\CuisRequest;
use PedroACF\Invoicing\Requests\Code\VerificarNitRequest;
use PedroACF\Invoicing\Responses\Code\CodeComunicacionResponse;

class CodeService extends BaseService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new CodeRepository();
    }

    public function getCuisCode($forceNew = false): string{
        $cuisModel = $this->getValidCuisModel();
        if($cuisModel && !$forceNew){
            return $cuisModel->cuis;
        }
        // Solicitar cuis nuevo y desactivar los otros
        Cuis::where('activo', true)->update(['activo'=> false]);
        $cuisReq = new CuisRequest();
        $remote = $this->repo->cuis($cuisReq);
        $newModel = new Cuis();
        $newModel->cuis = $remote->codigo;
        $newModel->expired_date = $remote->fechaVigencia;
        $newModel->activo = true;
        $newModel->save();
        return $newModel->cuis;
    }

    public function getCufdCode($forceNew = false): string{
        $cufdModel = $this->getValidCufdModel();
        if($cufdModel && !$forceNew){
            return $cufdModel->cufd;
        }
        // Solicitar cufd nuevo y desactivar los otros
        Cufd::where('activo', true)->update(['activo'=> false]);
        $cuis = $this->getCuisCode();
        $cufdReq = new CufdRequest($cuis);
        $remote = $this->repo->cufd($cufdReq);
        $model = new Cufd();
        $model->cufd = $remote->codigo;
        $model->codigo_control = $remote->codigoControl;
        $model->direccion = $remote->direccion;
        $model->expired_date = $remote->fechaVigencia;
        $model->activo = true;
        $model->save();
        return $model->cufd;
    }

    public function checkNit($nitToVerify){
        $cuis = $this->getCuisCode();
        $request = new VerificarNitRequest($cuis, $nitToVerify);
        $response = $this->repo->verificarNit($request);
        //TODO Terminar
        dd($response);
    }

    public function getValidCuisModel(): ?Cuis{
        $now = Carbon::now();
        return Cuis::where('activo', true)->where('expired_date', '>', $now)->first();
    }

    public function getValidCufdModel(): ?Cufd{
        $now = Carbon::now();
        return Cufd::where('activo', true)->where('expired_date', '>', $now)->first();
    }

    public function verificarComunicacion(): CodeComunicacionResponse{
        return $this->repo->verificarComunicacion();
    }
}
