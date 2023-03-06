<?php
return [
    'enviroment' => 0,//1 => PRODUCCION, 2 => PRUEBAS
    'office' => 0, //SUCURSAL: 0=>Casa Matriz
    'nit' => 0,
    'mode' => 0, //1 => ELECTRONICA, 2 => COMPUTARIZADA
    'system_code' => '',
    'token' => '',//revisar
    'endpoints' => [
        'sincronizacion_datos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'recepcion_compras' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioRecepcionCompras?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'obtencion_codigos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl'
    ]
];
