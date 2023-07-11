<?php

use Illuminate\Support\Facades\Date;

include_once 'ManejoDB.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

class Pedido{

    const PENDIENTE = 1;
    const EN_PREPARACION = 2;
    const LISTO = 3;
    const SERVIDO = 4;

    public $codigoMesa;
    public $numPed;
    public $encargo;
    public $estado; 
    public $horaEstimada;
    public $horaFinal;

    public function __construct( $codigoMesa = null, $encargo = array(), $estado = null, $numPed = null, $horaEstimada = null, $horaFinal = null )
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
        if( $horaFinal != null ){
            $this->horaFinal = $horaFinal;
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

    static function ObtenerListadoDePuesto( $idPuesto ){
        include_once "Producto.php";
        $arrayReturn = [];
        $listadoPedidos = self::ObtenerListado();
        foreach( $listadoPedidos as $pedido ){
            if( Producto::IDPuestoQCorresponde($pedido->encargo) == $idPuesto || $idPuesto == Puesto::SOCIO || $idPuesto == Puesto::MOZO ){
                
                $arrayReturn[] = $pedido;
            }
        }
        return $arrayReturn;
    }

    static function ObtenerListadoDeMesa( $codigoMesa ){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM pedidos WHERE codigoMesa = :codigoMesa' );
            $consulta->bindValue( ":codigoMesa", $codigoMesa );
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Pedido' );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public function getProducto(){
        include_once "Producto.php";
        return Producto::ObtenerPorNombre( $this->encargo );
    }
    
    public static function Alta( $codigoMesa, $encargo ){
        try{
            $numPed = Pedido::UltimoNumeroPedido()+1;

            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO pedidos (numPed, codigoMesa, encargo, estado) VALUES (:numPed, :codigoMesa, :encargo, :estado)' );
            //$consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO pedidos (numPed, codigoMesa, encargo, estado, horaEstimada) VALUES (:numPed, :codigoMesa, :encargo, :estado, :horaEstimada)' );
            $consulta->bindValue( ':numPed', $numPed );
            $consulta->bindValue( ':codigoMesa', $codigoMesa );
            $consulta->bindValue( ':encargo', $encargo );
            $consulta->bindValue( ':estado', Pedido::PENDIENTE );
            $consulta->execute();
            return new Pedido( $codigoMesa, $encargo, Pedido::PENDIENTE, $numPed );
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function Baja( $numPed ){
        try{
            $objetoBDO = ManejoDB::CrearAcceso();
            $consulta = $objetoBDO->RetornarConsulta( 'DELETE FROM pedidos WHERE numPed = :numPed' );
            $consulta->bindValue( ':numPed', $numPed );
            $consulta->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function Modificacion($numPed, $nuevo)
    {
        try{
            $objetoBDO = ManejoDB::CrearAcceso();
            $consulta = $objetoBDO->RetornarConsulta( 'UPDATE pedidos SET encargo=:encargo, estado=:estado, horaEstimada=:hrEstimada, horaFinal=:hrFinal WHERE numPed=:numPed' );
            $consulta->bindValue( ':encargo', $nuevo->encargo);
            $consulta->bindValue( ':estado', $nuevo->estado);
            $consulta->bindValue( ':hrEstimada', $nuevo->horaEstimada);
            $consulta->bindValue( ':hrFinal', $nuevo->horaFinal);
            $consulta->bindValue( ':numPed', $numPed);
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

    static function ObtenerPorNumPed( $numPed ){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM pedidos WHERE numPed = :numPed" );
            $consulta->bindParam( ':numPed', $numPed );
            $consulta->setFetchMode(PDO::FETCH_CLASS, 'Pedido');
            $consulta->execute();
            return $consulta->fetch();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function ObtenerEstado( $numeroEstado ){
        switch( $numeroEstado ){
            case Pedido::PENDIENTE: return "PENDIENTE";
            case Pedido::EN_PREPARACION: return "EN_PREPARACION";
            case Pedido::LISTO: return "LISTO";
            case Pedido::SERVIDO: return "SERVIDO";
        }
    }

    public function setHoraEstimada( $horaEstimada ){
        $this->horaEstimada = $horaEstimada;
    }

    public function setEstado( $estadoDePedido ){
        $this->estado = $estadoDePedido;
    }

    public function PrepararPedido( $minutosEstimados ){
        $horaEstimada = date( 'Y-m-d H:i:s' , strtotime("now + $minutosEstimados mins" ));
        $this->setEstado( Pedido::EN_PREPARACION );
        $this->setHoraEstimada( $horaEstimada );
        Pedido::Modificacion( $this->numPed, $this );
        return $this;
    }

    public function PedidoListo(){
        $this->setEstado( Pedido::LISTO );
        $this->horaFinal = date('Y-m-d H:i:s' , time());
        Pedido::Modificacion( $this->numPed, $this);
        return $this;
    }

    public function EntregarPedido(){
        $this->setEstado(Pedido::SERVIDO);
        Pedido::Modificacion( $this->numPed, $this);
        return $this;
    }

    public function __toString()
    {
        $stringPedido = '[ N.Ped {'.$this->numPed. '} - MESA '.$this->codigoMesa.'] '.$this->encargo." ESTADO:".Pedido::ObtenerEstado($this->estado);
        if( $this->estado != Pedido::PENDIENTE ){
            $stringPedido .= " HORA ESTIMADA DE PREPARACION: " . date_create($this->horaEstimada)->format("H:i:s") . "\n";
            $tiempoRestante = date_diff(  date_create(), date_create($this->horaEstimada), false);
            $stringPedido .= "Tiempo restante: [". $tiempoRestante->format("%r%H:%I:%S") . "]". "\n";
            if( $this->estado == Pedido::SERVIDO ){
                $stringPedido .= " HORA DE ENTREGA: " . date_create($this->horaFinal)->format("H:i:s");
            }
        }
        $stringPedido .= "\n";
        return $stringPedido;
    }

    static function PedidosTardios(){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM pedidos WHERE pedidos.horaFinal < NOW()' );
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Pedido' );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
}