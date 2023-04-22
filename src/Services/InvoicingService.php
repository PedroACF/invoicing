<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;

class InvoicingService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new PurchaseSaleRepository();
    }
    public function sendInvoice(RecepcionFacturaRequest $req){
        $this->repo->sendInvoice($req);
    }
}
