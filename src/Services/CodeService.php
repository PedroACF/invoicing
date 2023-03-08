<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Models\Cufd;
use PedroACF\Invoicing\Models\Cuis;
use PedroACF\Invoicing\Repositories\CodeRepository;
use PedroACF\Invoicing\Requests\Code\CufdRequest;
use PedroACF\Invoicing\Requests\Code\CuisRequest;
use PedroACF\Invoicing\Responses\Code\CodeComunicacionResponse;
use Carbon\Carbon;
class CodeService extends BaseService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new CodeRepository();
    }

    public function getCuisCode(): string{
        $cuisModel = $this->getValidCuisModel();
        if($cuisModel){
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

    public function getCufdCode(): string{
        $cuis = $this->getCuisCode();
        $cufdModel = $this->getValidCufdModel();
        if($cufdModel){
            return $cufdModel->cufd;
        }
        // Solicitar cufd nuevo y desactivar los otros
        Cufd::where('activo', true)->update(['activo'=> false]);
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
