<?php

use Slim\Psr7\Response as ResponseMW;

class ValidacionesPedido{
    public static function ValidarMesa( $request, $handler ){
        include_once __DIR__ . "/../Clases/ManejoDB.php";
        try{
            $codigoRequest = $request->getParsedBody()["codigoMesa"];
            $response = new ResponseMW();
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( "SELECT codigoMesa FROM mesas" );
            $consulta->execute();
            $arrayCodigos = $consulta->fetchAll(PDO::FETCH_COLUMN);

            $response->getBody()->write( json_encode(['mensaje' => 'Codigo de mesa no existe']) );
            foreach( $arrayCodigos as $codigo ){
                if( $codigoRequest == $codigo ){
                    $response = $handler->handle( $request );
                    break;
                }
            }
            return $response;

        }catch(Exception $e){
            $response = new ResponseMW();
            $response->getBody()->write( $e->getMessage() );
            return $response;
        }
    }

    public static function ValidarDatos( $request, $handler ) {
        include_once __DIR__ . "/../Clases/ManejoDB.php";
        try{
            $response = new ResponseMW();
            $cuerpoRequest = $request->getParsedBody();
            $arrayProductosEncargo = $cuerpoRequest['encargo'];
            
            if( $cuerpoRequest['minutosEstimados'] < 1 ){
                $response->getBody()->write( json_encode(['mensaje' => 'Tiempo no valido']) );
            } else {
                $objetoPdo = ManejoDB::CrearAcceso();
                $consulta = $objetoPdo->RetornarConsulta( "SELECT nombre FROM productos" );
                $consulta->execute();
                $arrayProductos = $consulta->fetchAll(PDO::FETCH_COLUMN);
                for( $i = 0; $i < count($arrayProductosEncargo); $i++ ){
                    $indx = array_search($arrayProductosEncargo[$i], $arrayProductos );
                    if( $indx == false ){
                        $response->getBody()->write( json_encode(['mensaje' => 'No existe el producto '.$cuerpoRequest['encargo'][$i]]) );
                        break;
                    } 
                }
                if( $indx ){
                    $response = $handler->handle( $request );
                }
            }
    
    
            return $response;
        }catch(Exception $e){
            $response = new ResponseMW();
            $response->getBody()->write( $e->getMessage() );
            return $response;
        }
    }
}