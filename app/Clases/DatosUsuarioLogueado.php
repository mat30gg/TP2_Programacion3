<?php

include_once "AutenticadorJWT.php";
include_once "Empleado.php";

class DatoUsuario{

    
    static function IdEmpleado(){
        $token = $_COOKIE['jwt'];
        $idEmpleado = AutenticadorJWT::ObtenerData( $token )->id_empleado;
        return $idEmpleado;
    }
    
    static function Empleado(){
        $idEmpleado = self::IdEmpleado();
        return Empleado::ObtenerPorid($idEmpleado);
    }
    
    static function IDPuesto(){
        $empleado = self::Empleado();
        return $empleado->id_puesto;
    }
}