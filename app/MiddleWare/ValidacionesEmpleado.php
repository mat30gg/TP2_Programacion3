<?php

use Slim\Psr7\Response as ResponseMW;

class ValidacionesEmpleado{
    static function ValidarDatos( $request, $handler ){
    
        $response = new ResponseMW();
        $cuerpoSolicitud = $request->getParsedBody();
    

        switch( $cuerpoSolicitud['puesto']) {
            case "bartender":
            case "cervecero":
            case "cocinero":
            case "mozo":
            case "socio":
                $response = $handler->handle( $request );
                break;
            default:
                $response->getBody()->write( json_encode(['mensaje' => 'Ingresar un puesto valido']) );
                break;
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarRegistro( $request, $handler ) {
        
        $response = new ResponseMW();
        $validado = false;
        $cuerpoSolicitud = $request->getParsedBody();


        if ( !trim( $cuerpoSolicitud['nombre'] ) ) {
            $mensajeNoValidado = 'Ingresar un nombre';
        } else {
            if ( !trim( $cuerpoSolicitud['puesto'] ) ) {
                $mensajeNoValidado = 'Ingresar un puesto';
            } else {
                if( !trim($cuerpoSolicitud['email']) ){
                    $mensajeNoValidado = 'Ingresar un mail';
                } else {
                    $validado = true;
                }
            }
        }
        if( $validado ){
            $response = $handler->handle($request);
        } else {
            $response->getBody()->write(json_encode(['mensaje' => $mensajeNoValidado]));
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarQueNoExiste( $request, $handler ){
        include_once __DIR__ . "/../Clases/ManejoDB.php";
        $cuerpoSolicitud = $request->getParsedBody();
        
        $response = new ResponseMW();
        $objetoPdo = ManejoDB::CrearAcceso();
        $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM empleados WHERE email = :email' );
        $consulta->bindValue(':email', $cuerpoSolicitud['email']);
        $consulta->execute();
        $resultado = $consulta->fetchAll();
        
        if( empty($resultado) ){
            $response = $handler->handle($request);
        } else {
            $response->getBody()->write( json_encode(['mensaje' => 'El correo ya esta registrado']));
        }

        return $response;
    }
}