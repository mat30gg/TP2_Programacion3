<?php

use Illuminate\Support\Arr;
use JetBrains\PhpStorm\ArrayShape;
use Producto as GlobalProducto;

include_once "ManejoDB.php";
include_once "Puesto.php";

    class Producto{

        public $nombre;
        public $id_puesto;
        public $id_producto;
        public $activo;
        public $precio;

        function __construct($nombre = null, $id_puesto = null, $id_producto = null, $activo = null, $precio = null)
        {
            if( $nombre != null ){
                $this->nombre = $nombre;
            }
            if( $id_puesto != null ){
                $this->id_puesto = $id_puesto;
            }
            if( $id_producto != null ) {
                $this->id_producto = $id_producto;
            } 
            if( $activo != null ) {
                $this->activo = $activo;
            }
            if( $precio != null ){
                $this->precio = $precio;
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

        static function ObtenerListadoPorPopularidad( $ascendente ){
            try{
                $orden = "DESC";
                if( $ascendente )
                    $orden = "ASC";
                $objetoPdo = ManejoDB::CrearAcceso();
                $query = "SELECT productos.id_producto, productos.nombre, COUNT(*) AS cantidad_vendido 
                FROM (productos JOIN pedidos ON productos.nombre = pedidos.encargo) 
                GROUP BY productos.id_producto 
                ORDER BY cantidad_vendido ".$orden.
                " WHERE `logs`.`fecha_accion` > :fecha_desde ".
                "AND `logs`.`fecha_accion` <= :fecha_hasta";
                
                $consulta = $objetoPdo->RetornarConsulta( $query );
                $consulta->execute();
                $arrayReturn = $consulta->fetchAll( PDO::FETCH_OBJ );
                return $arrayReturn;
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }
        
        static function IDPuestoQCorresponde( $nombreProducto ){
            try{
                $idPuesto = 0;
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT puestos.id_puesto FROM `productos` INNER JOIN puestos
                 ON productos.id_puesto = puestos.id_puesto WHERE productos.nombre = '".$nombreProducto."'" );
                $consulta->bindColumn( "id_puesto", $idPuesto);
                $consulta->execute();
                $consulta->fetch( PDO::FETCH_BOUND );
                return $idPuesto;
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        static function Alta( $producto ){
            try{
                // $nId = self::UltimoId()+1;
                // $nProducto = new self( $nombre, $id_puesto, $nId, true, $precio);
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO productos (id_producto, nombre, id_puesto, precio, activo) VALUES (:idProducto, :nombre, :puestoAsignado, :precio, :activo)' );
                $consulta->bindValue( ':idProducto', $producto->id_producto );
                $consulta->bindParam( ':nombre', $producto->nombre );
                $consulta->bindParam( ':puestoAsignado', $producto->id_puesto );
                $consulta->bindValue( ':precio', $producto->precio );
                $consulta->bindValue( ':activo', $producto->activo );
                $consulta->execute();
                // return $nProducto;
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        // static function Alta( $nombre, $id_puesto, $precio ){
        //     try{
        //         $nId = self::UltimoId()+1;
        //         $nProducto = new self( $nombre, $id_puesto, $nId, true, $precio);
        //         $objetoPdo = ManejoDB::CrearAcceso();
        //         $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO productos (id_producto, nombre, id_puesto, precio) VALUES (:idProducto, :nombre, :puestoAsignado, :precio)' );
        //         $consulta->bindValue( ':idProducto', $nId );
        //         $consulta->bindParam( ':nombre', $nombre );
        //         $consulta->bindParam( ':puestoAsignado', $id_puesto );
        //         $consulta->bindValue( ':precio', $precio );
        //         $consulta->execute();
        //         return $nProducto;
        //     }catch(Exception $e){
        //         echo $e->getMessage();
        //     }
        // }

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

        public static function UltimoId( ){
            try{
                $ultimoNumero = 0;
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'SELECT id_producto FROM productos ORDER BY id_producto DESC LIMIT 1' );
                $consulta->bindColumn('id_producto', $ultimoNumero );
                $consulta->execute();
                $consulta->fetch(PDO::FETCH_BOUND);
                return $ultimoNumero;
            }catch( Exception $e){
                echo $e->getMessage();
            }
        }

        static function Existe( $producto ) {
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT activo FROM productos WHERE nombre = :nombre" );
                $consulta->bindParam( ':nombre', $producto );
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

        static function ObtenerPorNombre( $nombre ){
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM productos WHERE nombre = :nombre' );
                $consulta->bindValue( ":nombre", $nombre);
                $consulta->setFetchMode(PDO::FETCH_CLASS, 'Producto');
                $consulta->execute();
                return $consulta->fetch();
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        function __toString()
        {
            return "[" . sprintf("%03d" ,$this->id_producto) . "] " . str_pad( $this->nombre, 20, "." ) . " $". number_format( $this->precio, 2 ).". Puesto: :[" . Puesto::ObtenerNombreDePuestoID($this->id_puesto) . "]:\n";
        }
    }

?>