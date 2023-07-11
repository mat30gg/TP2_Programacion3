<?php

use Slim\Psr7\Response as ResponseMW;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class ValidacionesFormulario{

    public static function ValidarCamposLlenos( Request $request, Handler $handler ){
        $response = new ResponseMW();
        $emptyFlag = false;
        $requestBody = $request->getParsedBody() ?? [];
        $requestBodyParams = array_keys($requestBody);
        foreach($requestBodyParams as $param){
            if(empty($requestBody[$param])){
                $emptyFlag = true;
                $response->getBody()->write('Completar campo '.$param);
                break;
            }
        }
        if( !$emptyFlag ){
            $response = $handler->handle( $request );
        }
        return $response;
    }

    public static function ValidarArchivoCargado( Request $request, Handler $handler ){
        $response = new ResponseMW();
        $emptyFlag = false;
        $requestFiles = $request->getUploadedFiles();
        $requestFilesParams = array_keys($requestFiles);
        foreach($requestFilesParams as $param){
            if(empty($requestFiles[$param]->getClientFilename())){
                $emptyFlag = true;
                $response->getBody()->write('Subir archivo en '.$param);
            }
        }
        if( !$emptyFlag ){
            $response = $handler->handle($request);
        }
        return $response;
    }
}