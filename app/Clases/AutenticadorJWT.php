<?php

require_once '..\vendor\autoload.php';
use Firebase\JWT\JWT;
date_default_timezone_set('America/Argentina/Buenos_Aires');


class AutenticadorJWT {

    private static $claveSecreta = "pizza";
    private static $tipoEncriptacion = ['HS256'];

    public static function CrearToken( $datos ) {
        $ahora = time();
        $expiracion = strtotime("+1day");

        // PARAMETROS DEL PAYLOAD
        // https://auth0.com/docs/secure/tokens/json-web-tokens/json-web-token-claims   
        $payload = [
            'iat' => $ahora,           // CUANDO SE CREO EL TOKEN (OPCIONAL)
            'exp' => $expiracion,    // EL TIEMPO DE VENCIMIENTO DEL TOKEN (OPCIONAL)
            //'aud' => self::Aud(),      // PARA QUIEN ES EL TOKEN
            'data' => $datos,          // DATOS DEL JWT
            'app' => 'JWT API REST'    // INFO DE LA APLICACION
        ];

        return JWT::encode( $payload, self::$claveSecreta );
    }

    public static function VerificarToken( $token ) {

        if( empty( $token )) {
            throw new Exception( "El token esta vacio" );
        }

        try{
            $decodificado = JWT::decode(
                $token,
                self::$claveSecreta,
                self::$tipoEncriptacion
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function ObtenerPayload( $token ) {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        );
    }

    public static function ObtenerData( $token ) {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$tipoEncriptacion
        )->data;
    }
}
