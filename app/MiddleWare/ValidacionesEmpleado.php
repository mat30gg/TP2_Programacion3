<?php

use GuzzleHttp\Psr7\Response;
use Slim\Psr7\Response as ResponseMW;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ValidacionesEmpleado{
    static function ValidarDatos( $request, $handler ){
    
        $response = new ResponseMW();
        $cuerpoSolicitud = $request->getParsedBody();
    

        switch( $cuerpoSolicitud['puesto']) {
            case "bartender":
            case "cervecero":
            case "cocinero":
            case "mozo":
                $response = $handler->handle( $request );
                break;
            default:
                $response->getBody()->write( json_encode(['mensaje' => 'Ingresar un puesto valido']) );
                break;
        }
        
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarQueNoExiste( $request, $handler ){
        include_once __DIR__ . "/../Clases/ManejoDB.php";
        $cuerpoSolicitud = $request->getParsedBody();
        
        $response = new ResponseMW();
        $objetoPdo = ManejoDB::CrearAcceso();
        $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM usuarios WHERE email = :email' );
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

    public static function ValidarQueExiste( Request $request, Handler $handler ){
        include_once __DIR__ . "/../Clases/ManejoDB.php";
        
        $id_empleado = $request->getHeader("id_empleado")[0];
        $response = new ResponseMW();
        $objetoPdo = ManejoDB::CrearAcceso();
        $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM empleados WHERE id_empleado = :id_empleado' );
        $consulta->bindValue(':id_empleado', $id_empleado);
        $consulta->setFetchMode( PDO::FETCH_OBJ );
        $consulta->execute();
        $resultado = $consulta->fetch();
        if( !empty($resultado) ){
            $response = $handler->handle($request);
        } else {
            $response->getBody()->write( json_encode(['mensaje' => 'El id no existe']));
        }

        return $response;
    }

    public static function ValidarBaja( Request $request, Handler $handler ){
        include_once __DIR__ . '/../Clases/DatosUsuarioLogueado.php';

        $response = new ResponseMW();
        $id_empleado = $request->getHeader("id_empleado")[0];

        if( DatoUsuario::IdEmpleado() != $id_empleado ){
            $response = $handler->handle($request);
        }else{
            $response->getBody()->write("No puedes dar de baja a tu propio usuario");
        }

        return $response;
    }
}