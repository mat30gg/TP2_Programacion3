<?php

use GuzzleHttp\Psr7\Response;
use Slim\Psr7\Response as ResponseMW;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

include_once "Clases/ManejoDB.php";

class ValidacionesLogueo{

    public static function ValidarLogin( Request $request, Handler $handler ){
        $response = new ResponseMW();
        $cuerpoSolicitud = $request->getParsedBody();

        if ( trim( $cuerpoSolicitud['email'] ) == false ) {
            $response->getBody()->write(json_encode(['mensaje' => 'Ingresar un correo', 'faltante' => 'email']));
        } else {
            if ( trim( $cuerpoSolicitud['clave'] ) == false ) {
                $response->getBody()->write(json_encode(['mensaje' => 'Ingresar clave', 'faltante' => 'puesto']));
            } else {
                $response = $handler->handle( $request );
            }
        }
        return $response;
    }

    

    public static function ValidarCredenciales( Request $request, Handler $handler ){
        include_once "Clases/Usuario.php";

        $response = new ResponseMW();
        $usuarios = Usuario::ObtenerListado();
        $requestBody = $request->getParsedBody();
        foreach( $usuarios as $usr ){
            if( $usr->email == $requestBody['email'] ){
                if( $usr->clave == $requestBody['clave'] ){
                    $response = $handler->handle( $request );
                    $responseBody = json_encode(['mensaje' => 'Credenciales validas']);
                } else {
                    $responseBody = json_encode(['mensaje' => 'Clave incorrecta']);
                }
                break;
            } else {
                $responseBody = json_encode(['mensaje' => 'Email no registrado']);
            }
        }
        $response->getBody()->write( $responseBody );
        return $response;
    }
}