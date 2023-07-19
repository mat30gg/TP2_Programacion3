<?php

use Empleado as GlobalEmpleado;
use Illuminate\Support\Arr;
use JetBrains\PhpStorm\ArrayShape;

include_once "ManejoDB.php";
include_once "Puesto.php";

    class Empleado{

        public $nombre;
        public $email;
        public $id_puesto;
        public $fechaRegistro;
        public $activo;
        public $id_empleado;
        
        public function __construct($nombre = null, $email = null, $puesto = null, $fechaRegistro = null, $activo = true, $id = null)
        {
            if( $id != null ) {
                $this->id_empleado = $id;
            }
            if( $email != null ){
                $this->email = $email;
            }
            if( $nombre != null ){
                $this->nombre = $nombre;
            }
            if( $puesto != null ){
                $this->id_puesto = $puesto;
            }
            if( $fechaRegistro != null ){
                $this->fechaRegistro = $fechaRegistro;
            }
            if( $activo != null ){
                $this->activo = $activo;
            }
        }

        static function ObtenerListado(){
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM empleados' );
                $consulta->execute();
                $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Empleado' );
                return $arrayReturn;
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        
        
        public static function Alta( $nombre, $email, $puesto, $fechaRegistro ){
            try{
                $nId = self::UltimoId()+1;
                $empleado = new Empleado( $nombre, $email, $puesto, $fechaRegistro, true, $nId );
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO empleados (id_empleado, email, nombre, id_puesto, fechaRegistro) VALUES (:id, :email, :nombre, :puesto, :fechaRegistro)' );
                $consulta->bindValue( ':id', $nId );
                $consulta->bindValue( ':email', $email, PDO::PARAM_STR_CHAR );
                $consulta->bindValue( ':nombre', $nombre, PDO::PARAM_STR_CHAR );
                $consulta->bindValue( ':puesto', $puesto );
                $consulta->bindValue( ':fechaRegistro', $fechaRegistro );
                $consulta->execute();
                return $empleado;
                // if( !Empleado::Existe( $empleado) ){
                //     return $empleado;
                // }
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        public static function Baja( $id_empleado ){
            try{
                $objetoBDO = ManejoDB::CrearAcceso();
                $consulta = $objetoBDO->RetornarConsulta( 'UPDATE empleados SET activo = 0 WHERE id_empleado = :idEmpleado' );
                $consulta->bindParam( ':idEmpleado', $id_empleado );
                $consulta->execute();
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        public static function Modificacion($actual, $reemplazo)
        {
            try{
                if( $reemplazo instanceof Empleado ){
                    $objetoBDO = ManejoDB::CrearAcceso();
                    $consulta = $objetoBDO->RetornarConsulta( 'UPDATE empleados SET nombre=:nNombre,puesto=:nPuesto WHERE id_emplado=:idActual' );
                    $consulta->bindParam( ':nNombre', $reemplazo->nombre);
                    $consulta->bindParam( ':nPuesto', $reemplazo->id_puesto);
                    $consulta->bindParam( ':idActual', $actual->id);
                    $consulta->execute();
                }
            } catch(Exception $e){
                echo $e->getMessage();
            }
        }

        static function BorrarEmpleado( $id_empleado ){
            try{
                $objetoBDO = ManejoDB::CrearAcceso();
                $consulta = $objetoBDO->RetornarConsulta( 'DELETE FROM empleados WHERE id_empleado = :idEmpleado' );
                $consulta->bindParam( ':idEmpleado', $id_empleado );
                $consulta->execute();
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        static function Existe( $email ) {
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT activo FROM empleados WHERE email = :email" );
                $consulta->bindParam( ':email', $email );
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

        // static function ObtenerPuesto( $idEmpleado ){
        //     $empleado = self::ObtenerPorid( $idEmpleado );
        //     return $empleado->puesto ?? null;
        // }

        static function ObtenerPorid( $idEmpleado ){
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM empleados WHERE id_empleado = :id" );
                $consulta->bindParam( ':id', $idEmpleado );
                $consulta->setFetchMode(PDO::FETCH_CLASS, 'Empleado');
                $consulta->execute();
                return $consulta->fetch();
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        static function ObtenerPorEmail( $email ){
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM empleados WHERE email = :email" );
                $consulta->bindParam( ':email', $email );
                $consulta->setFetchMode(PDO::FETCH_CLASS, 'Empleado');
                $consulta->execute();
                return $consulta->fetch( );
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        public static function UltimoId( ){
            try{
                $ultimoNumero = 0;
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'SELECT id_empleado FROM empleados ORDER BY id_empleado DESC LIMIT 1' );
                $consulta->bindColumn('id_empleado', $ultimoNumero );
                $consulta->execute();
                $consulta->fetch(PDO::FETCH_BOUND);
                return $ultimoNumero;
            }catch( Exception $e){
                echo $e->getMessage();
            }
        }


        function __toString()
        {
            if( $this->activo == true ){
                return "[" . $this->id_empleado . "] " . $this->nombre . ". Puesto: " . Puesto::ObtenerNombreDePuestoID( $this->id_puesto ) . " Fecha de registro ". $this->fechaRegistro . "\n";
            } else {
                return "";
            }
        }
    }

?>