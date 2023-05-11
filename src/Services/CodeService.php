<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Exceptions\BaseException;
use PedroACF\Invoicing\Models\SIN\Cufd;
use PedroACF\Invoicing\Models\SIN\Cuis;
use PedroACF\Invoicing\Repositories\CodeRepository;
use PedroACF\Invoicing\Requests\Code\CufdRequest;
use PedroACF\Invoicing\Requests\Code\CuisRequest;
use PedroACF\Invoicing\Requests\Code\VerificarNitRequest;
use PedroACF\Invoicing\Responses\Code\CodeComunicacionResponse;

class CodeService extends BaseService
{
    private $codeRepo;

    public function __construct(CodeRepository $codeRepository){
        $this->codeRepo = $codeRepository;
    }

    public function getCuisCode($salePoint = 0, $forceNew = false): string{
        $cuisModel = $this->getValidCuisModel($salePoint);
        if($cuisModel && !$forceNew){
            return $cuisModel->cuis;
        }
        // Solicitar cuis nuevo y desactivar los otros
        $request = new CuisRequest($salePoint);
        $response = $this->codeRepo->cuis($request);
        if($response->hasCodes([])){
            throw new BaseException("Error");//TODO: Definir que codigo genera error
        }
        $cuisModel = Cuis::where([
            ['activo', '=', true],
            ['cuis', '=', $response->codigo],
        ])->first();
        Cuis::where('activo', true)->where('sale_point', $salePoint)->update(['activo'=> false]);
        if(!$cuisModel) {
            $cuisModel = new Cuis();
            $cuisModel->cuis = $response->codigo;
            $cuisModel->expired_date = $response->fechaVigencia;
            $cuisModel->sale_point = $salePoint;
        }
        $cuisModel->activo = true;
        $cuisModel->save();
        return $cuisModel->cuis;
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
        $remote = $this->codeRepo->cufd($cufdReq);
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
        $response = $this->codeRepo->verificarNit($request);
        //TODO Terminar
        dd($response);
    }

    public function getValidCuisModel($salePoint = 0): ?Cuis{
        $now = Carbon::now();
        return Cuis::where('activo', true)->where('expired_date', '>', $now)->where('sale_point', $salePoint)->first();
    }

    public function getValidCufdModel(): ?Cufd{
        $now = Carbon::now();
        return Cufd::where('activo', true)->where('expired_date', '>', $now)->first();
    }

}
