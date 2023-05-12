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

    public function getCuisCode($salePoint = 0, $forceNew = false): ?string{
        $cuisModel = $this->getValidCuisModel($salePoint);
        if($cuisModel && !$forceNew){
            return $cuisModel->cuis;
        }

        //Verificar conexion
        $conn = $this->codeRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            // Solicitar cuis nuevo y desactivar los otros
            $request = new CuisRequest($salePoint);
            $response = $this->codeRepo->cuis($request);
            if($response->hasCodes([])){
                throw new BaseException("Error CUIS");//TODO: Definir que codigo genera error
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
        return null;
    }

    public function getCufdCode($salePoint = 0, $forceNew = false): ?string{
        $cufdModel = $this->getValidCufdModel($salePoint);
        if($cufdModel && !$forceNew){
            return $cufdModel->cufd;
        }
        //Verificar conexion
        $conn = $this->codeRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $cuis = $this->getCuisCode($salePoint);
            $request = new CufdRequest($salePoint, $cuis);
            $response = $this->codeRepo->cufd($request);
            if($response->hasCodes([])){
                throw new BaseException("Error CUFD");//TODO: Definir que codigo genera error
            }
            $model = Cufd::where([
                ['activo', '=', true],
                ['cufd', '=', $response->codigo],
            ])->first();
            Cufd::where('activo', true)->where('sale_point', $salePoint)->update(['activo'=> false]);
            if(!$model){
                $model = new Cufd();
                $model->cufd = $response->codigo;
                $model->codigo_control = $response->codigoControl;
                $model->sale_point = $salePoint;
                $model->expired_date = $response->fechaVigencia;
            }
            $model->activo = true;
            $model->save();
            return $model->cufd;
        }
        return null;
    }

    public function checkNit($nitToVerify){
        $cuis = $this->getCuisCode();
        $request = new VerificarNitRequest($cuis, $nitToVerify);
        $response = $this->codeRepo->verificarNit($request);
        //TODO Terminar
        dd($response);
    }

    public function getValidCuisModel(int $salePoint): ?Cuis{
        $now = Carbon::now();
        return Cuis::where('activo', true)->where('expired_date', '>', $now)->where('sale_point', $salePoint)->first();
    }

    public function getValidCufdModel(int $salePoint): ?Cufd{
        $now = Carbon::now();
        return Cufd::where('activo', true)->where('expired_date', '>', $now)->where('sale_point', $salePoint)->first();
    }

}
