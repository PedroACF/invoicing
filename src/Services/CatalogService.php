<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Models\SIN\Activity;
use PedroACF\Invoicing\Models\SIN\ActivityDocSector;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SIN\CurrencyType;
use PedroACF\Invoicing\Models\SIN\EmissionType;
use PedroACF\Invoicing\Models\SIN\IdentityDocType;
use PedroACF\Invoicing\Models\SIN\InvoiceType;
use PedroACF\Invoicing\Models\SIN\Legend;
use PedroACF\Invoicing\Models\SIN\Measurement;
use PedroACF\Invoicing\Models\SIN\Message;
use PedroACF\Invoicing\Models\SIN\PaymentType;
use PedroACF\Invoicing\Models\SIN\Product;
use PedroACF\Invoicing\Models\SIN\RoomType;
use PedroACF\Invoicing\Models\SIN\SalePointType;
use PedroACF\Invoicing\Models\SIN\SectorDocType;
use PedroACF\Invoicing\Models\SIN\SignificantEventType;
use PedroACF\Invoicing\Models\SIN\SourceCountry;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Repositories\DataSyncRepository;
use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;

class CatalogService
{
    private $dataRepo;
    private $configService;

    public function __construct(DataSyncRepository $dataRepo, ConfigService $configService)
    {
        $this->dataRepo = $dataRepo;
        $this->configService = $configService;
    }

    public function syncFechaHora(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $request = new SincronizacionRequest($salePoint);
            $response = $this->dataRepo->getFechaHora($request);
            $now = new Carbon();
            $diff = $now->diffInMilliseconds($response->date, false);
            $this->configService->setServerTimeDiff($diff);
            return true;
        }
        return false;
    }

    public function syncActividades(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $request = new SincronizacionRequest($salePoint);
            $response = $this->dataRepo->getActividades($request);
            $activity_ids = [];
            foreach($response->items as $activity){
                $old = Activity::where('codigo_caeb', $activity->codigoCaeb)->first();
                if($old){
                    $old->descripcion = $activity->descripcion;
                    $old->tipo_actividad = $activity->tipoActividad;
                    $old->state = 'ACTIVE';
                    $old->save();
                    $activity_ids[] = $old->codigo_caeb;
                }else{
                    $new = new Activity();
                    $new->codigo_caeb = $activity->codigoCaeb;
                    $new->descripcion = $activity->descripcion;
                    $new->tipo_actividad = $activity->tipoActividad;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $activity_ids[] = $new->id;
                }
            }
            Activity::whereNotIn('codigo_caeb', $activity_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncActividadesDocumentosSector(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $request = new SincronizacionRequest($salePoint);
            $response = $this->dataRepo->getActividadesDocumentSector($request);
            $item_ids = [];
            foreach($response->items as $item){
                $old = ActivityDocSector::where([
                    ['codigo_actividad', '=', $item->codigoActividad],
                    ['codigo_documento_sector', '=', $item->codigoDocumentoSector],
                    ['tipo_documento_sector', '=', $item->tipoDocumentoSector],
                ])->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->save();
                    $item_ids[] = $old->id;
                }else{
                    $new = new ActivityDocSector();
                    $new->codigo_actividad = $item->codigoActividad;
                    $new->codigo_documento_sector = $item->codigoDocumentoSector;
                    $new->tipo_documento_sector = $item->tipoDocumentoSector;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->id;
                }
            }
            ActivityDocSector::whereNotIn('id', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncLeyendas(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $request = new SincronizacionRequest($salePoint);
            $response = $this->dataRepo->getParamLeyendas($request);
            $item_ids = [];
            foreach($response->items as $item){
                $old = Legend::where([
                    ['codigo_actividad', '=', $item->codigoActividad],
                    ['descripcion_leyenda', '=', $item->descripcionLeyenda]
                ])->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->save();
                    $item_ids[] = $old->id;
                }else{
                    $new = new Legend();
                    $new->codigo_actividad = $item->codigoActividad;
                    $new->descripcion_leyenda = $item->descripcionLeyenda;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->id;
                }
            }
            Legend::whereNotIn('id', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncMensajes(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $request = new SincronizacionRequest($salePoint);
            $response = $this->dataRepo->getParamMensajes($request);
            $item_ids = [];
            foreach($response->items as $item){
                $old = Message::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->descripcion = $item->descripcion;
                    $old->state = 'ACTIVE';
                    $old->save();
                    $item_ids[] = $old->clasificador;
                }else{
                    $new = new Message();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            Message::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncProductos(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $request = new SincronizacionRequest($salePoint);
            $response = $this->dataRepo->getProductos($request);
            $item_ids = [];
            foreach($response->items as $item){
                $old = Product::where([
                    ['codigo_actividad', '=', $item->codigoActividad],
                    ['codigo_producto', '=', $item->codigoProducto],
                ])->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion_producto = $item->descripcionProducto;
                    $old->save();
                    $item_ids[] = $old->id;
                }else{
                    $new = new Product();
                    $new->codigo_actividad = $item->codigoActividad;
                    $new->codigo_producto = $item->codigoProducto;
                    $new->descripcion_producto = $item->descripcionProducto;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->id;
                }
            }
            Product::whereNotIn('id', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncEventosSignificativos(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $request = new SincronizacionRequest($salePoint);
            $response = $this->dataRepo->getParamEventosSignificativos($request);
            $item_ids = [];
            foreach($response->items as $item){
                $old = SignificantEventType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new SignificantEventType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            SignificantEventType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncPaises(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamPaises($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = SourceCountry::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new SourceCountry();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            SourceCountry::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncMotivosAnulacion(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamMotivosAnulacion($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = CancelReason::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new CancelReason();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            CancelReason::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposDocumentoIdentidad(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposDocumentoIdentidad($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = IdentityDocType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new IdentityDocType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            IdentityDocType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposDocumentoSector(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposDocumentoSector($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = SectorDocType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new SectorDocType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            SectorDocType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposEmision(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposEmision($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = EmissionType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new EmissionType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            EmissionType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposHabitacion(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposHabitacion($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = RoomType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new RoomType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            RoomType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposMetodoPago(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposMetodoPago($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = PaymentType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new PaymentType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            PaymentType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposMoneda(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposMoneda($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = CurrencyType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new CurrencyType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            CurrencyType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposPuntoVenta(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposPuntoVenta($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = SalePointType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->descripcion = $item->descripcion;
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new SalePointType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            SalePointType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncTiposFactura(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamTiposFactura($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = InvoiceType::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new InvoiceType();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            InvoiceType::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncUnidadesMedida(SalePoint $salePoint): bool{
        $conn = $this->dataRepo->checkConnection();
        if($conn->transaccion && $conn->hasCodes([926])){
            $syncReq = new SincronizacionRequest($salePoint);
            $remote = $this->dataRepo->getParamUnidadMedida($syncReq);
            $item_ids = [];
            foreach($remote->items as $item){
                $old = Measurement::where('codigo_clasificador', $item->codigoClasificador)->first();
                if($old){
                    $old->state = 'ACTIVE';
                    $old->save();
                    $item_ids[] = $old->codigo_clasificador;
                }else{
                    $new = new Measurement();
                    $new->codigo_clasificador = $item->codigoClasificador;
                    $new->descripcion = $item->descripcion;
                    $new->state = 'ACTIVE';
                    $new->save();
                    $item_ids[] = $new->codigo_clasificador;
                }
            }
            Measurement::whereNotIn('codigo_clasificador', $item_ids)->update(['state'=>'INACTIVE']);
            return true;
        }
        return false;
    }

    public function syncAll(SalePoint $salePoint){
        $this->syncFechaHora($salePoint);
        $this->syncActividades($salePoint);
        $this->syncActividadesDocumentosSector($salePoint);
        $this->syncLeyendas($salePoint);
        $this->syncMensajes($salePoint);
        $this->syncProductos($salePoint);
        $this->syncEventosSignificativos($salePoint);
        $this->syncPaises($salePoint);
        $this->syncMotivosAnulacion($salePoint);
        $this->syncTiposDocumentoIdentidad($salePoint);
        $this->syncTiposDocumentoSector($salePoint);
        $this->syncTiposEmision($salePoint);
        $this->syncTiposHabitacion($salePoint);
        $this->syncTiposMetodoPago($salePoint);
        $this->syncTiposMoneda($salePoint);
        $this->syncTiposPuntoVenta($salePoint);
        $this->syncTiposFactura($salePoint);
        $this->syncUnidadesMedida($salePoint);
    }
}
