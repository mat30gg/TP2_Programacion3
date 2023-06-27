<?php

use Usuario as GlobalUsuario;

include_once "AutenticadorJWT.php";

class Usuario{


    public $idEmpleado;
    public $token;
    public $puesto;

    public function __construct( $email, $idEmpleado )
    {
        include_once "Empleado.php";
        $this->token = AutenticadorJWT::CrearToken( $email );
        $this->puesto = Empleado::ObtenerPorid($idEmpleado)->puesto;
        $this->idEmpleado = $idEmpleado;
    }

    public static function Alta( $empleado, $clave ){
        try{
            if( $empleado instanceof Empleado ){
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO usuarios (email, clave, id_empleado) VALUES (:email, :clave, :idEmpleado)' );
                $consulta->bindParam( ':email', $empleado->email );
                $consulta->bindParam( ':clave', $clave );
                $consulta->bindParam( ':idEmpleado', $empleado->id );
                $consulta->execute();
            }
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public function GetEmail(){
        AutenticadorJWT::ObtenerData( $this->token );
    }

    public static function LoguearUsuario( $email, $clave ){
        include_once "ManejoDB.php";

        $logueado = false;
        $objetoPdo = ManejoDB::CrearAcceso();
        $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM usuarios WHERE email = ".$email );
        $usuario = $consulta->fetch( PDO::FETCH_OBJ );
        if( $usuario->clave = $clave ){
            $logueado = new Usuario( $usr->email, $usr->idEmpleado );
        }
        return $logueado;
    }
}