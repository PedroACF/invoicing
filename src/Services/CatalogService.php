<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Models\Activity;
use PedroACF\Invoicing\Models\ActivityDocSector;
use PedroACF\Invoicing\Models\CancelReason;
use PedroACF\Invoicing\Models\CurrencyType;
use PedroACF\Invoicing\Models\EmissionType;
use PedroACF\Invoicing\Models\IdentityDocType;
use PedroACF\Invoicing\Models\InvoiceType;
use PedroACF\Invoicing\Models\Legend;
use PedroACF\Invoicing\Models\Measurement;
use PedroACF\Invoicing\Models\Message;
use PedroACF\Invoicing\Models\PaymentType;
use PedroACF\Invoicing\Models\Product;
use PedroACF\Invoicing\Models\RoomType;
use PedroACF\Invoicing\Models\SalePointType;
use PedroACF\Invoicing\Models\SectorDocType;
use PedroACF\Invoicing\Models\SignificantEvent;
use PedroACF\Invoicing\Models\SourceCountry;
use PedroACF\Invoicing\Repositories\DataSyncRepository;
use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;
use PedroACF\Invoicing\Responses\Mensaje;

class CatalogService
{
    private $repo;
    private $codeService;

    private $cuis;

    public function __construct()
    {
        $this->codeService = new CodeService();
        $this->repo = new DataSyncRepository();
        $this->cuis = $this->codeService->getCuisCode();
    }

    public function syncActividades(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getActividades($syncReq);
        $activity_ids = [];
        foreach($remote->items as $activity){
            $old = Activity::where([
                ['codigo_caeb', '=', $activity->codigoCaeb],
                ['descripcion', '=', $activity->descripcion],
                ['tipo_actividad', '=', $activity->tipoActividad],
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $activity_ids[] = $old->id;
            }else{
                $new = new Activity();
                $new->codigo_caeb = $activity->codigoCaeb;
                $new->descripcion = $activity->descripcion;
                $new->tipo_actividad = $activity->tipoActividad;
                $new->activo = true;
                $new->save();
                $activity_ids[] = $new->id;
            }
        }
        Activity::whereNotIn('id', $activity_ids)->update(['activo'=>false]);
    }

    public function syncFechaHora(){

    }

    public function syncActividadesDocumentosSector(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getActividadesDocumentSector($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = ActivityDocSector::where([
                ['codigo_actividad', '=', $item->codigoActividad],
                ['codigo_documento_sector', '=', $item->codigoDocumentoSector],
                ['tipo_documento_sector', '=', $item->tipoDocumentoSector],
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new ActivityDocSector();
                $new->codigo_actividad = $item->codigoActividad;
                $new->codigo_documento_sector = $item->codigoDocumentoSector;
                $new->tipo_documento_sector = $item->tipoDocumentoSector;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        ActivityDocSector::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncLeyendas(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamLeyendas($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = Legend::where([
                ['codigo_actividad', '=', $item->codigoActividad],
                ['descripcion_leyenda', '=', $item->descripcionLeyenda]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new Legend();
                $new->codigo_actividad = $item->codigoActividad;
                $new->descripcion_leyenda = $item->descripcionLeyenda;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        Legend::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncMensajes(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamMensajes($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = Message::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new Message();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        Message::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncProductos(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getProductos($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = Product::where([
                ['codigo_actividad', '=', $item->codigoActividad],
                ['codigo_producto', '=', $item->codigoProducto],
                ['descripcion_producto', '=', $item->descripcionProducto]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new Product();
                $new->codigo_actividad = $item->codigoActividad;
                $new->codigo_producto = $item->codigoProducto;
                $new->descripcion_producto = $item->descripcionProducto;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        Product::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncEventosSignificativos(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamEventosSignificativos($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = SignificantEvent::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new SignificantEvent();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        SignificantEvent::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncPaises(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamPaises($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = SourceCountry::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new SourceCountry();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        SourceCountry::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncMotivosAnulacion(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamMotivosAnulacion($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = CancelReason::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new CancelReason();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        CancelReason::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposDocumentoIdentidad(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposDocumentoIdentidad($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = IdentityDocType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new IdentityDocType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        IdentityDocType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposDocumentoSector(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposDocumentoSector($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = SectorDocType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new SectorDocType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        SectorDocType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposEmision(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposEmision($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = EmissionType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new EmissionType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        EmissionType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposHabitacion(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposHabitacion($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = RoomType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new RoomType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        RoomType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposMetodoPago(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposMetodoPago($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = PaymentType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new PaymentType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        PaymentType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposMoneda(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposMoneda($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = CurrencyType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new CurrencyType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        CurrencyType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposPuntoVenta(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposPuntoVenta($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = SalePointType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new SalePointType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        SalePointType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncTiposFactura(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamTiposFactura($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = InvoiceType::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new InvoiceType();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        InvoiceType::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncUnidadesMedida(){
        $syncReq = new SincronizacionRequest($this->cuis);
        $remote = $this->repo->getParamUnidadMedida($syncReq);
        $item_ids = [];
        foreach($remote->items as $item){
            $old = Measurement::where([
                ['codigo_clasificador', '=', $item->codigoClasificador],
                ['descripcion', '=', $item->descripcion]
            ])->first();
            if($old){
                $old->activo = true;
                $old->save();
                $item_ids[] = $old->id;
            }else{
                $new = new Measurement();
                $new->codigo_clasificador = $item->codigoClasificador;
                $new->descripcion = $item->descripcion;
                $new->activo = true;
                $new->save();
                $item_ids[] = $new->id;
            }
        }
        Measurement::whereNotIn('id', $item_ids)->update(['activo'=>false]);
    }

    public function syncAll(){
        $this->syncActividades();
        $this->syncActividadesDocumentosSector();
        $this->syncLeyendas();
        $this->syncMensajes();
        $this->syncProductos();
        $this->syncEventosSignificativos();
        $this->syncPaises();
        $this->syncMotivosAnulacion();
        $this->syncTiposDocumentoIdentidad();
        $this->syncTiposDocumentoSector();
        $this->syncTiposEmision();
        $this->syncTiposHabitacion();
        $this->syncTiposMetodoPago();
        $this->syncTiposMoneda();
        $this->syncTiposPuntoVenta();
        $this->syncTiposFactura();
        $this->syncUnidadesMedida();
    }
}
