<?php

use Illuminate\Support\Arr;
use JetBrains\PhpStorm\ArrayShape;
use Producto as GlobalProducto;

    include "Entidad.php";
    include_once "ManejoDB.php";


    class Producto extends Entidad{

        public $id;
        public $nombre;
        public $tipo;
        public $activo;

        function __construct($nombre = null, $tipo = null, $id = null, $activo = null)
        {
            if( $nombre != null ){
                $this->nombre = $nombre;
            }
            if( $tipo != null ){
                $this->tipo = $tipo;
            }
            if( $id != null ) {
                $this->id = $id;
            } 
            if( $activo != null ) {
                $this->activo = $activo;
            }
        }

        static function ObtenerListado(){
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM productos' );
                $consulta->execute();
                $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Producto' );
                return $arrayReturn;
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }
        
        static function Alta( $producto ){
            try{
                if( $producto instanceof Producto ){
                    if( self::Existe( $producto ) ) {
                        return false;
                    } else {
                        $objetoPdo = ManejoDB::CrearAcceso();
                        $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO productos (nombre, tipo) VALUES (:nombre, :tipo)' );
                        $consulta->bindParam( ':nombre', $producto->nombre, PDO::PARAM_STR_CHAR );
                        $consulta->bindParam( ':tipo', $producto->tipo, PDO::PARAM_STR_CHAR );
                        $consulta->execute();
                        return $producto;
                    }
                }
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        static function Baja( $producto )
        {
            try{
                if( $producto instanceof Producto ) {
                    $objetoBDO = ManejoDB::CrearAcceso();
                    $consulta = $objetoBDO->RetornarConsulta( 'UPDATE productos SET activo = 0 WHERE id = :idProd' );
                    $consulta->bindParam( ':idProd', $producto->id );
                    $consulta->execute();
                }
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        static function Modificacion($actual, $reemplazo)
        {
            try{
                if( $reemplazo instanceof Producto ){
                    $objetoBDO = ManejoDB::CrearAcceso();
                    $consulta = $objetoBDO->RetornarConsulta( 'UPDATE productos SET nombre=:nNombre,tipo=:nTipo WHERE id=:idActual' );
                    $consulta->bindParam( ':nNombre', $reemplazo->nombre);
                    $consulta->bindParam( ':nTipo', $reemplazo->tipo);
                    $consulta->bindParam( ':idActual', $actual->id);
                    $consulta->execute();
                }
            } catch(Exception $e){
                echo $e->getMessage();
            }
        }

        static function Existe( $producto ) {
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT activo FROM productos WHERE nombre = :nombre" );
                $consulta->bindParam( ':nombre', $producto->nombre );
                $consulta->execute();
                $resultado = $consulta->fetch();
                if( empty( $resultado ) ) {
                    return false;
                } else {
                    return true;
                }
            }catch( Exception $e ){
                echo $e->getMessage();
            }
        }

        function __toString()
        {
            return "[" . $this->id . "] " . $this->nombre . ". Tipo: " . $this->tipo . "\n";
        }
    }

?>