<?php

include_once "ManejoDB.php";

class Puesto{

    const SOCIO = 1;
    const MOZO = 2;
    const BARTENDER = 3;
    const COCINERO = 4;
    const CERVECERO = 5;

    static function ObtenerNombreDePuestoID( $id_puesto ){
        try{
            $nombrePuesto = "";
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT nombre FROM puestos WHERE id_puesto = "'.$id_puesto.'"' );
            $consulta->bindColumn( "nombre", $nombrePuesto);
            $consulta->execute();
            $consulta->fetch( PDO::FETCH_BOUND );
            

            return $nombrePuesto;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function IdPuestoPorNombre( $nombre ){
        try{
            $idPuesto = -1;
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT id_puesto FROM puestos WHERE nombre = :nombre' );
            $consulta->bindValue( ":nombre", $nombre);
            $consulta->bindColumn( 'id_puesto', $idPuesto);
            $consulta->execute();
            $consulta->fetch( PDO::FETCH_BOUND);
            return $idPuesto;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
}