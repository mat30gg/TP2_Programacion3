<?php

use Usuario as GlobalUsuario;

include_once "AutenticadorJWT.php";
include_once "ManejoDB.php";
include_once "Empleado.php";

class Usuario{

    public $email;
    public $clave;
    public $id_empleado;

    public static function ObtenerListado(){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM usuarios' );
            $consulta->execute();
            return $consulta->fetchAll( PDO::FETCH_CLASS, 'Usuario' );
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function Alta( $empleado, $clave ){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO usuarios (email, clave, id_empleado) VALUES (:email, :clave, :idEmpleado)' );
            $consulta->bindValue( ':email', $empleado->email );
            $consulta->bindValue( ':clave', $clave );
            $consulta->bindValue( ':idEmpleado', $empleado->id_empleado );
            $consulta->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function BorrarUsuario( $id_empleado ){
        try{
            $objetoBDO = ManejoDB::CrearAcceso();
            $consulta = $objetoBDO->RetornarConsulta( 'DELETE FROM usuarios WHERE id_empleado = :idEmpleado' );
            $consulta->bindParam( ':idEmpleado', $id_empleado );
            $consulta->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function ObtenerPorid( $idEmpleado ){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM usuarios WHERE id_empleado = :id" );
            $consulta->bindParam( ':id', $idEmpleado );
            $consulta->setFetchMode( PDO::FETCH_CLASS, 'Usuario');
            $consulta->execute();
            return $consulta->fetch( );
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function ObtenerPorEmail( $email ){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM usuarios WHERE email = :email" );
            $consulta->bindParam( ':email', $email );
            $consulta->setFetchMode( PDO::FETCH_CLASS, 'Usuario');
            $consulta->execute();
            return $consulta->fetch( );
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
}