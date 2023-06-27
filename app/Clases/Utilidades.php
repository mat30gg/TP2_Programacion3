<?php

use Symfony\Component\Translation\Loader\CsvFileLoader;

class Utilidades{
    static function Listar( $arrayObjetos ){
        $stringListado = "";
        foreach( $arrayObjetos as $objeto ){
            $stringListado .= $objeto->__toString();
        }
        return $stringListado;
    }

    // static function LeerJson( $archivo ) {
    //     $jsonObj = [];
    //     if( file_exists( $archivo) ) {
    //         $jsonObj = json_decode( file_get_contents( $archivo ), true, 1 );
    //         var_dump( $jsonObj );
    //     }
    //     return $jsonObj;
    // }

    // static function SubirJson( $archivo, $array ) {
    //     $jsonObj = json_encode( $array, JSON_PRETTY_PRINT );
    //     file_put_contents($archivo, $jsonObj);
    // }

    // static function AgregarElemento( $archivo, $objeto ){
    //     $arrayElementos = self::LeerJson( $archivo );
    //     array_push( $arrayElementos, $objeto );
    //     self::SubirJson( $archivo, $arrayElementos );
    //     return $arrayElementos;
    // }

    // static function ObtenerDeValores( $dato ){
            
    //     $path = dirname(__FILE__)."\\datos";
    //     $archivo = $path . "\\valores.json";
    //     $datoReturn = 0;
        
    //     $arrayValores = self::LeerJson( $archivo );
    //     if( isset($arrayValores[$dato]) ) {
    //         $datoReturn = $arrayValores[$dato];
    //     } else {
    //         self::AgregarElemento( $archivo, [$dato => 0]);
    //     }

    //     return $datoReturn;
    // }

    // static function ModificarEnValores( $dato, $nuevoValor ){
    //     $path = dirname(__FILE__)."\\datos";
    //     $archivo = $path . "\\valores.json";
    //     $arrayValores = self::LeerJson( $archivo );

    //     if( isset($arrayValores[$dato]) ){
    //         $arrayValores[$dato] = $nuevoValor;
    //         self::SubirJson( $archivo, $arrayValores );
    //     }
    // }
}