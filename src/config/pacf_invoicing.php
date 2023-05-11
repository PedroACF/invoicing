<?php
return [
    'endpoints' => [
        'sincronizacion_datos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'recepcion_compras' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioRecepcionCompras?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'obtencion_codigos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl'
    ],
    'main_schema' => 'facturaElectronicaCompraVenta'
];
