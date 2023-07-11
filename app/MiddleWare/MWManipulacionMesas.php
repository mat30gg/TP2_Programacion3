<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as ResponseMW;
use Slim\Routing\RouteContext;

include_once __DIR__ . "/../Clases/DatosUsuarioLogueado.php";
include_once __DIR__ . "/../Clases/ManejoDB.php";
include_once __DIR__ . "/../Clases/Mesa.php";

class MWManipulacionMesas{
    public static function ValidarMesaExiste( Request $request, Handler $handler ){
        $response = new ResponseMW();

        $mesa = Mesa::ObtenerPorCodigo( $request->getHeader( 'codigoMesa')[0] );
        if( $mesa ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "La mesa no fue creada" );
        }

        return $response;
    }

    public static function ValidarCambioDeEstado( Request $request, Handler $handler ){
        $response = new ResponseMW();

        $mesa = Mesa::ObtenerPorCodigo( $request->getHeader( 'codigoMesa')[0] );
        if( $mesa ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "La mesa no fue creada" );
        }

        return $response;
    }

    public static function ValidarMesaCerrada( Request $request, Handler $handler ){
        $response = new ResponseMW();

        $mesa = Mesa::ObtenerPorCodigo( $request->getHeader( 'codigoMesa')[0] );
        if( $mesa->estado == Mesa::CERRADA ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "La mesa no fue cerrada" );
        }

        return $response;
    }
}