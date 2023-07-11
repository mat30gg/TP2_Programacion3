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

    static function FormatearEnCSV( $elemento ){
        $fp = fopen("php://temp", "w");
        fputcsv( $fp, get_object_vars($elemento) );
        rewind( $fp );
        $stringCsv = stream_get_contents($fp);
        fclose($fp);
        return $stringCsv;
    }
}