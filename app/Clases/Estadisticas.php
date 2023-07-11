<?php

include_once "Logs.php";

class Stats{
    static function PedidoMasVendido( $desde, $hasta){
        $lista = Logs::ObtenerProductosOrdenadoPorPopularidad( false, $desde, $hasta );
        return $lista[0];
    }

    static function PedidoMenosVendido( $desde, $hasta){
        $lista = Logs::ObtenerProductosOrdenadoPorPopularidad( true, $desde, $hasta );
        return $lista[0];
    }
}