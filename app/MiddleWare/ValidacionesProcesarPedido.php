<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as ResponseMW;
use Slim\Routing\RouteContext;

include_once __DIR__ . "/../Clases/DatosUsuarioLogueado.php";
include_once __DIR__ . "/../Clases/ManejoDB.php";
include_once __DIR__ . "/../Clases/Pedido.php";

class ValidacionesProcesarPedidos{
    public static function ValidarQuePedidoCorrespondaAUsuario( Request $request, Handler $handler ){
        include_once __DIR__ . "/../Clases/Producto.php";
        
        $response = new ResponseMW();        
        $idPuesto = DatoUsuario::IDPuesto();

        $pedido = Pedido::ObtenerPorNumPed( $request->getHeader('numPed')[0] );
        if( $idPuesto == Producto::IDPuestoQCorresponde($pedido->encargo) ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "El numero de pedido no corresponde al usuario" );
        }
        return $response;
    }

    public static function ValidarPedidoPendiente( Request $request, Handler $handler ){

        $response = new ResponseMW();

        $pedido = Pedido::ObtenerPorNumPed( $request->getHeader('numPed')[0] );
        if( $pedido->estado == Pedido::PENDIENTE ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "El pedido no esta pendiente" );
        }

        return $response;
    }

    public static function ValidarPedidoEnPreparacion( Request $request, Handler $handler ){
        $response = new ResponseMW();

        $pedido = Pedido::ObtenerPorNumPed( $request->getHeader('numPed')[0] );
        if( $pedido->estado == Pedido::EN_PREPARACION ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "El pedido no esta en preparacion" );
        }

        return $response;
    }

    public static function ValidarPedidoListo( Request $request, Handler $handler ){
        $response = new ResponseMW();

        $pedido = Pedido::ObtenerPorNumPed( $request->getHeader('numPed')[0] );
        if( $pedido->estado == Pedido::LISTO ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "El pedido no esta listo" );
        }

        return $response;
    }

    public static function ValidarPedidoExiste( Request $request, Handler $handler ){

        $response = new ResponseMW();
        $pedido = Pedido::ObtenerPorNumPed( $request->getHeader('numPed')[0] );
        if( $pedido ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "El pedido no existe" );
        }
        return $response;
    }

    public static function ValidarPedidoDeMesa( Request $request, Handler $handler ){
        $response = new ResponseMW();
        $pedido = Pedido::ObtenerPorNumPed( $request->getHeader('numPed')[0] );
        $codigoMesa = $request->getHeader('codigoMesa')[0];
        if( $pedido && $pedido->codigoMesa == $codigoMesa ){
            $response = $handler->handle( $request );
        }else{
            $response->getBody()->write( "El pedido no corresponde a esa mesa" );
        }
        return $response;
    }
}