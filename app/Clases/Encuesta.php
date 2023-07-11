<?php

class Encuesta{
    public $codigoMesa;
    public $pMesa;
    public $pRestaurante;
    public $pMozo;
    public $pCocina;
    public $comentarios;

    static function Alta( $encuesta ){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO encuestas VALUES (:codigoMesa, :puntMesa, :puntRestaurante, :puntMozo, :puntCocina, :comentarios)' );
            $consulta->bindValue( ':codigoMesa', $encuesta->codigoMesa );
            $consulta->bindParam( ':puntMesa', $encuesta->pMesa );
            $consulta->bindParam( ':puntRestaurante', $encuesta->pRestaurante );
            $consulta->bindValue( ':puntMozo', $encuesta->pMozo );
            $consulta->bindValue( ':puntCocina', $encuesta->pCocina );
            $consulta->bindValue( ':comentarios', $encuesta->comentarios );
            $consulta->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function ObtenerMejoresComentarios(){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM encuestas ORDER BY comentarios LIMIT 5; ' );
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Encuesta' );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    
    function __toString()
    {
        return "[".$this->codigoMesa . "] MESA (".$this->pMesa."/10) RESTAURANTE (".$this->pRestaurante."/10) MOZO (".$this->pMozo."/10) COCINA (".$this->pCocina."/10) COMENTARIOS:\n".$this->comentarios."\n";
    }
}