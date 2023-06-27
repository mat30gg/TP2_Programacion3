<?php

use Empleado as GlobalEmpleado;
use Illuminate\Support\Arr;
use JetBrains\PhpStorm\ArrayShape;

include_once "ManejoDB.php";


    class Empleado{

        public $id;
        public $email;
        public $nombre;
        public $puesto;
        public $fechaRegistro;
        public $activo;
        
        private function __construct($nombre = null, $email = null, $puesto = null, $fechaRegistro = null, $activo = true, $id = null)
        {
            if( $id != null ) {
                $this->id = $id;
            }
            if( $email != null ){
                $this->email = $email;
            }
            if( $nombre != null ){
                $this->nombre = $nombre;
            }
            if( $puesto != null ){
                $this->puesto = $puesto;
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
                if( !Empleado::Existe( $empleado) ){
                    $objetoPdo = ManejoDB::CrearAcceso();
                    $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO empleados (id, email, nombre, puesto, fechaRegistro) VALUES (:id, :email, :nombre, :puesto, :fechaRegistro)' );
                    $consulta->bindParam( ':id', $empleado->id );
                    $consulta->bindParam( ':email', $empleado->email, PDO::PARAM_STR_CHAR );
                    $consulta->bindParam( ':nombre', $empleado->nombre, PDO::PARAM_STR_CHAR );
                    $consulta->bindParam( ':puesto', $empleado->puesto, PDO::PARAM_STR_CHAR );
                    $consulta->bindValue( ':fechaRegistro', $empleado->fechaRegistro );
                    $consulta->execute();
                    return $empleado;
                }
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        public static function Baja( $empleado ){
            try{
                if( $empleado instanceof Empleado ) {
                    $objetoBDO = ManejoDB::CrearAcceso();
                    $consulta = $objetoBDO->RetornarConsulta( 'UPDATE empleados SET activo = 0 WHERE id = :idProd' );
                    $consulta->bindParam( ':idProd', $empleado->id );
                    $consulta->execute();
                }
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        public static function Modificacion($actual, $reemplazo)
        {
            try{
                if( $reemplazo instanceof Empleado ){
                    $objetoBDO = ManejoDB::CrearAcceso();
                    $consulta = $objetoBDO->RetornarConsulta( 'UPDATE empleados SET nombre=:nNombre,puesto=:nPuesto WHERE id=:idActual' );
                    $consulta->bindParam( ':nNombre', $reemplazo->nombre);
                    $consulta->bindParam( ':nPuesto', $reemplazo->puesto);
                    $consulta->bindParam( ':idActual', $actual->id);
                    $consulta->execute();
                }
            } catch(Exception $e){
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

        static function ObtenerPorid( $idEmpleado ){
            try{
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM empleados WHERE id = :id" );
                $consulta->bindParam( ':id', $idEmpleado );
                $consulta->execute();
                return $consulta->fetch( PDO::FETCH_CLASS, 'Empleado' );
            }catch(Exception $e){
                echo $e->getMessage();
            }
        }

        public static function UltimoId( ){
            try{
                $ultimoNumero = 0;
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'SELECT id FROM empleados ORDER BY id DESC LIMIT 1' );
                $consulta->bindColumn('id', $ultimoNumero );
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
                return "[" . $this->id . "] " . $this->nombre . ". Puesto: " . $this->puesto . " Fecha de registro ". $this->fechaRegistro . "\n";
            } else {
                return "";
            }
        }
    }

?>