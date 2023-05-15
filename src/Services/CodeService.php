<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Exceptions\BaseException;
use PedroACF\Invoicing\Models\SIN\Cufd;
use PedroACF\Invoicing\Models\SIN\Cuis;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Repositories\CodeRepository;
use PedroACF\Invoicing\Requests\Code\CufdRequest;
use PedroACF\Invoicing\Requests\Code\CuisRequest;
use PedroACF\Invoicing\Requests\Code\VerificarNitRequest;
use PedroACF\Invoicing\Responses\Code\CodeComunicacionResponse;
use PedroACF\Invoicing\Responses\Code\CufdResponse;

class CodeService extends BaseService
{
    private $codeRepo;
    private $configService;

    public function __construct(CodeRepository $codeRepository, ConfigService $configService){
        $this->codeRepo = $codeRepository;
        $this->configService = $configService;
    }

    public function getCuisCode(SalePoint $salePoint, $forceNew = false): ?string{
        $cuisModel = $this->getValidCuisModel($salePoint);
        if($cuisModel && !$forceNew){
            return $cuisModel->code;
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
            $salePoint->cuisCodes()->update(['state'=> 'INACTIVE']);
            $cuisModel = new Cuis();
            $cuisModel->code = $response->codigo;
            $cuisModel->expired_date = $response->fechaVigencia;
            $cuisModel->sale_point = $salePoint->sin_code;
            $cuisModel->state = 'ACTIVE';
            $cuisModel->save();
            return $cuisModel->code;
        }
        return null;
    }

    public function getCufdModel(SalePoint $salePoint, $forceNew = false ): ?Cufd{
        $cufdModel = $this->getActiveCufdModel($salePoint);
        if($cufdModel){
            $expiredDate = new Carbon($cufdModel->expired_date);
            if($forceNew || $this->configService->getTime()->greaterThan($expiredDate)){
                $conn = $this->codeRepo->checkConnection();
                if($conn->transaccion){
                    $request = new CufdRequest($salePoint);
                    $response = $this->codeRepo->cufd($request);
                    if($response->hasCodes([])){
                        throw new BaseException("Error CUFD");//TODO: Definir que codigo genera error
                    }
                    $salePoint->cufdCodes()->update(['state'=>'INACTIVE']);
                    $cufdModel = new Cufd();
                    $cufdModel->code = $response->codigo;
                    $cufdModel->codigo_control = $response->codigoControl;
                    $cufdModel->sale_point = $salePoint->sin_code;
                    $cufdModel->expired_date = $response->fechaVigencia;
                    $cufdModel->state = 'ACTIVE';
                    $cufdModel->save();
                }else{
                    return null;
                }
            }
            return $cufdModel;
        }else{
            $conn = $this->codeRepo->checkConnection();
            if($conn->transaccion){
                $request = new CufdRequest($salePoint);
                $response = $this->codeRepo->cufd($request);
                if($response->hasCodes([])){
                    throw new BaseException("Error CUFD");//TODO: Definir que codigo genera error
                }
                $salePoint->cufdCodes()->update(['state'=>'INACTIVE']);
                $cufdModel = new Cufd();
                $cufdModel->code = $response->codigo;
                $cufdModel->codigo_control = $response->codigoControl;
                $cufdModel->sale_point = $salePoint->sin_code;
                $cufdModel->expired_date = $response->fechaVigencia;
                $cufdModel->state = 'ACTIVE';
                $cufdModel->save();
                return $cufdModel;
            }else{
                return null;
            }
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

    public function getValidCuisModel(SalePoint $salePoint): ?Cuis{
        $now = $this->configService->getTime();
        return Cuis::where('state', 'ACTIVE')->where('expired_date', '>', $now)->where('sale_point', $salePoint->sin_code)->first();
    }

    public function getActiveCufdModel(SalePoint $salePoint): ?Cufd{
        //$now = $this->configService->getTime();
        return Cufd::where('state', 'ACTIVE')->where('sale_point', $salePoint->sin_code)->first();
    }

}
