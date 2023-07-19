<?php

include_once "Logs.php";

class Stats{
    static function PedidoMasVendido( $desde = null, $hasta = null){
        $lista = Logs::ObtenerProductosOrdenadoPorPopularidad( false, $desde, $hasta );
        return $lista[0];
    }

    static function PedidoMenosVendido( $desde = null, $hasta = null){
        $lista = Logs::ObtenerProductosOrdenadoPorPopularidad( true, $desde, $hasta );
        return $lista[0];
    }

    static function MesaMasUsada( $desde = null, $hasta = null ){
        $lista = Logs::MesasOrdenadasPorUso( false, $desde, $hasta );
        return $lista[0];
    }

    static function MesaMenosUsada( $desde = null, $hasta = null ){
        $lista = Logs::MesasOrdenadasPorUso( true, $desde, $hasta );
        return $lista[0];
    }
}