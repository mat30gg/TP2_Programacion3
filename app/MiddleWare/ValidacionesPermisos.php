<?php

use GuzzleHttp\Psr7\Response;
use Slim\Psr7\Response as ResponseMW;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

include_once __DIR__ . "/../Clases/AutenticadorJWT.php";

class ValidacionesPermisos{

    private static function PuestoUsuarioLogueado( $token ){
        try{
            include_once __DIR__ . "/../Clases/Puesto.php";
            include_once __DIR__ . "/../Clases/Empleado.php";
    
            $datosToken = AutenticadorJWT::ObtenerData( $token );
            $empleado = Empleado::ObtenerPorid( $datosToken->id_empleado );

            return Puesto::ObtenerNombreDePuestoID( $empleado->id_puesto );
        }catch(Exception $e){
            echo $e->getMessage();
            return new ResponseMW();
        }
    }

    private static function Logueado(){

        if( isset( $_COOKIE['jwt'] )){
            AutenticadorJWT::VerificarToken( $_COOKIE['jwt'] );
        }else{
            throw new Exception("Iniciar sesion");
        }
    }

    public static function UsuarioAdministrador( Request $request, Handler $handler){
        try{
            self::Logueado();
            include_once __DIR__  . "/../Clases/Empleado.php";

            $response = new ResponseMW();
            $puesto = self::PuestoUsuarioLogueado($_COOKIE['jwt']);
            if( $puesto == "socio" ){
                $response = $handler->handle( $request );
            } else {
                $response->getBody()->write( "El usuario no es administrador" );
            }

            return $response;
        }catch(Exception $e){
            echo $e->getMessage();
            return New ResponseMW();
        }
    }

    public static function UsuarioMozo( Request $request, Handler $handler){
        try{
            self::Logueado();
            include_once __DIR__  . "/../Clases/Empleado.php";

            $response = new ResponseMW();
            $puesto = self::PuestoUsuarioLogueado($_COOKIE['jwt']);
            if( $puesto == "mozo" || $puesto == "socio" ){
                $response = $handler->handle( $request );
            } else {
                $response->getBody()->write( "El usuario no es mozo" );
            }

            return $response;
        }catch(Exception $e){
            echo $e->getMessage();
            return New ResponseMW();
        }
    }

    public static function UsuarioStandard( Request $request, Handler $handler ){
        try{
            self::Logueado();
            AutenticadorJWT::VerificarToken( $_COOKIE['jwt'] );
            $response = $handler->handle( $request );
            return $response;
        }catch(Exception $e){
            echo $e->getMessage();
            $response = new ResponseMW();
            return $response;
        }
    }

}