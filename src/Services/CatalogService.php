<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Repositories\DataSyncRepository;

class CatalogService
{
    private $repo;

    public function __construct()
    {
        $repo = new DataSyncRepository();
    }

    public function syncActividades(){

    }

    public function syncFechaHora(){

    }

    public function syncActividadesDocumentosSector(){

    }

    public function syncLeyendas(){

    }
}
