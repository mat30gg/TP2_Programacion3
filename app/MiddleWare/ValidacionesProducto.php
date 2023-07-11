<?php

use Slim\Psr7\Response as ResponseMW;

class ValidacionesProducto{

    public static function ValidarDatos( $request, $handler ) {
        include_once __DIR__ . "/../Clases/Puesto.php";

        $response = new ResponseMW();
        $cuerpoRequest = $request->getParsedBody();

        if( Puesto::IdPuestoPorNombre( $cuerpoRequest['puestoasignado']) == -1 ){
            $response->getBody()->write( json_encode(['mensaje' => 'Ingrese un puesto valido']) );
        }else{
            if( $cuerpoRequest['precio'] < 0 || !is_numeric($cuerpoRequest['precio']) ){
                $response->getBody()->write( json_encode(['mensaje' => 'Ingrese un precio valido']));
            }else{
                $response = $handler->handle( $request );
            }
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarQueNoExiste( $request, $handler ){
        include_once __DIR__ . "/../Clases/ManejoDB.php";
        $cuerpoSolicitud = $request->getParsedBody();
        
        $response = new ResponseMW();
        $objetoPdo = ManejoDB::CrearAcceso();
        $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM productos WHERE nombre = :nombre' );
        $consulta->bindValue(':nombre', $cuerpoSolicitud['nombre']);
        $consulta->execute();
        $resultado = $consulta->fetchAll();
        
        if( empty($resultado) ){
            $response = $handler->handle($request);
        } else {
            $response->getBody()->write( json_encode(['mensaje' => 'El producto ya esta registrado']));
        }

        return $response;
    }
}