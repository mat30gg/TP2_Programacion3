<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as ResponseMW;
use Slim\Routing\RouteContext;

include_once __DIR__ . "/../Clases/DatosUsuarioLogueado.php";
include_once __DIR__ . "/../Clases/ManejoDB.php";

class ValidacionesPedido{
    public static function ValidarMesa( Request $request, Handler $handler ){
        try{
            $routeContext = RouteContext::fromRequest( $request );
            $route = $routeContext->getRoute();
            $codigoMesa = $route->getArgument("codigomesa");

            $response = new ResponseMW();
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM mesas WHERE codigoMesa = '".$codigoMesa."'" );
            $consulta->execute();


            if( $consulta->fetch() ){
                $response = $handler->handle( $request );
            } else {
                $response->getBody()->write( json_encode(['mensaje' => 'Codigo de mesa no existe']) ); 
            }
            return $response;

        }catch(Exception $e){
            $response = new ResponseMW();
            $response->getBody()->write( $e->getMessage() );
            return $response;
        }
    }

    public static function ValidarDatos( Request $request, Handler $handler ) {
        include_once __DIR__ . "/../Clases/ManejoDB.php";
        include_once __DIR__ . "/../Clases/Producto.php";

        try{
            $response = new ResponseMW();
            $cuerpoRequest = $request->getParsedBody();
            $arrayProductosEncargo = $cuerpoRequest['encargos'];
            $todosLosProductosExisten = true;

            foreach( $arrayProductosEncargo as $producto ){
                if( !Producto::Existe($producto) ){
                    $todosLosProductosExisten = false;
                    $response->getBody()->write( json_encode(['mensaje' => 'No existe el producto '.$producto]));
                    break;
                }
            }
            if( $todosLosProductosExisten ){
                $response = $handler->handle($request);
            }
    
    
            return $response;
        }catch(Exception $e){
            $response = new ResponseMW();
            $response->getBody()->write( $e->getMessage() );
            return $response;
        }
    }

    
}