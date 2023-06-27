<?php

use Slim\Psr7\Response as ResponseMW;

class ValidacionesProducto{

    public static function ValidarDatos( $request, $handler ) {
        $response = new ResponseMW();
        $cuerpoRequest = $request->getParsedBody();

        switch( $cuerpoRequest['tipo']){
            case 'trago':
            case 'vino':
            case 'cocina':
            case 'cerveza':
            case 'postre':
                $response = $handler->handle( $request );
                break;
            default:
                $response->getBody()->write( json_encode(['mensaje' => 'Ingrese un tipo valido']) );
                break;
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarIngresoDatos( $request, $handler ) {
        
        $response = new ResponseMW();
        $cuerpoRequest = $request->getParsedBody();

        if( trim($cuerpoRequest['nombre']) == false ){
            $response->getBody()->write( json_encode(['mensaje' => 'Ingrese un nombre del producto']) );
        } elseif( trim($cuerpoRequest['tipo']) == false ) {
            $response->getBody()->write( json_encode(['mensaje' => 'Ingrese un tipo de producto']) );
        } else {
            $response = $handler->handle( $request );
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}