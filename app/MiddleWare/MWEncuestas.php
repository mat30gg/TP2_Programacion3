<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as ResponseMW;
use Slim\Routing\RouteContext;

class MWEncuestas{
    public static function ValidarComentarios( Request $request, Handler $handler ){
        $response = new ResponseMW();
        $comentarios = $request->getParsedBody()["comentarios"];
        if( strlen($comentarios) > 66 ){
            $response->getBody()->write( "Comentario muy largo" );
        }else{
            $response = $handler->handle($request);
        }
        return $response;
    }
}