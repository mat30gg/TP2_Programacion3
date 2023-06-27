<?php

use Illuminate\Support\Facades\Date;

include_once 'ManejoDB.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

class Pedido{

    const EN_PREPARACION = 1;
    const LISTO = 2;

    public $codigoMesa;
    public $numPed;
    public $encargo;
    public $estado; 
    public $horaEstimada;

    public function __construct( $codigoMesa = null, $encargo = array(), $horaEstimada = null , $estado = Pedido::EN_PREPARACION, $numPed = null )
    {
        if( $codigoMesa != null ){
            $this->codigoMesa = $codigoMesa;
        }
        if( $encargo != null ){
            $this->encargo = $encargo;
        }
        if( $estado != null ){
            $this->estado = $estado;
        }
        if( $horaEstimada != null ){
            $this->horaEstimada = $horaEstimada;
        }
        if( $numPed != null ){
            $this->numPed = $numPed;
        }
    }

    static function ObtenerListado(){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM pedidos' );
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Pedido' );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    
    public static function Alta( $codigoMesa, $encargo, $minutosEstimados ){
        try{
            $horaEstimada = date( 'Y-m-d H:i:s' , strtotime("now + $minutosEstimados mins" ));
            $numPed = Pedido::UltimoNumeroPedido()+1;
            $pedido = new Pedido( $codigoMesa, $encargo, $horaEstimada, Pedido::EN_PREPARACION, $numPed );
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO pedidos (numPed, codigoMesa, encargo, estado, horaEstimada) VALUES (:numPed, :codigoMesa, :encargo, :estado, :horaEstimada)' );
            $consulta->bindValue( ':numPed', $pedido->numPed );
            $consulta->bindValue( ':codigoMesa', $pedido->codigoMesa );
            $consulta->bindValue( ':encargo', json_encode($pedido->encargo) );
            $consulta->bindValue( ':estado', $pedido->estado );
            $consulta->bindValue( ':horaEstimada', $pedido->horaEstimada );
            $consulta->execute();
            return $pedido;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function Baja( $mesa ){
        try{
            if( $mesa instanceof Mesa ) {
                $objetoBDO = ManejoDB::CrearAcceso();
                $consulta = $objetoBDO->RetornarConsulta( 'UPDATE mesas SET estado = :estadoCancelado WHERE codigo = :codMesa' );
                $consulta->bindValue( ':codMesa', $mesa->codigoMesa );
                $consulta->bindValue( ':estadoCancelado', $mesa::CANCELADA );
                $consulta->execute();
            }
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function Modificacion($actual, $nEstado = null)
    {
        try{
            $objetoBDO = ManejoDB::CrearAcceso();
            $consulta = $objetoBDO->RetornarConsulta( 'UPDATE mesas SET estado=:nEstado WHERE codigo=:codigoActual' );
            $consulta->bindParam( ':nEstado', $nEstado);
            $consulta->bindParam( ':codigoActual', $actual->codigo);
            $consulta->execute();
        } catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function UltimoNumeroPedido( ){
        try{
            $ultimoNumero = 0;
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT numPed FROM pedidos ORDER BY numPed DESC LIMIT 1' );
            $consulta->bindColumn('numPed', $ultimoNumero );
            $consulta->execute();
            $consulta->fetch(PDO::FETCH_BOUND);
            return $ultimoNumero;
        }catch( Exception $e){
            echo $e->getMessage();
        }
    }

    public static function ObtenerEstado( $numeroEstado ){
        switch( $numeroEstado ){
            case Pedido::EN_PREPARACION: return "EN_PREPARACION";
            case Pedido::LISTO: return "LISTO";
        }
    }

    public function __toString()
    {
        return '['.$this->numPed. ' - MESA '.$this->codigoMesa.']'.$this->encargo." ESTADO:".Pedido::ObtenerEstado($this->estado).". HORA ESTIMADA DE PREPARACION: (".$this->horaEstimada.')';
    }
}