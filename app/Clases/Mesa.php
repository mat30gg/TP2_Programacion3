<?php

include_once 'ManejoDB.php';

class Mesa{
    
    const CLIENTE_ESPERANDO = 1;
    const CLIENTE_COMIENDO = 2;
    const CLIENTE_PAGANDO = 3;
    const CERRADA = 0;
    const CANCELADA = -1;

    public $estado;
    public $codigoMesa;
    public $fotoMesa;

    public function __construct( $estado = null, $fotoMesa = null, $codigoMesa = null)
    {
        if( $estado != null ){
            $this->estado = $estado;
        }
        if( $codigoMesa != null ){
            $this->codigoMesa = $codigoMesa;
        }
        if( $fotoMesa != null ){
            $this->fotoMesa = $fotoMesa;
        }
    }

    static function ObtenerListado(){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM mesas' );
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Mesa' );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
    
    public static function Alta( $fotoMesa = null ){
        try{
            $codigoMesaMesa = Mesa::UltimocodigoMesa()+1;
            $mesa = new Mesa( Mesa::CLIENTE_ESPERANDO, $fotoMesa, $codigoMesaMesa );
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO mesas (codigoMesa, estado, fotoMesa) VALUES (:codigoMesa, :estado, :fotoMesa)' );
            $consulta->bindValue( ':estado', $mesa->estado );
            $consulta->bindValue( ':fotoMesa', $mesa->fotoMesa );
            $consulta->bindValue( ':codigoMesa', $mesa->codigoMesa );
            $consulta->execute();
            return $mesa;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function Baja( $mesa ){
        try{
            if( $mesa instanceof Mesa ) {
                $objetoBDO = ManejoDB::CrearAcceso();
                $consulta = $objetoBDO->RetornarConsulta( 'UPDATE mesas SET estado = :estadoCancelado WHERE codigoMesa = :codMesa' );
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
            $consulta = $objetoBDO->RetornarConsulta( 'UPDATE mesas SET estado=:nEstado WHERE codigoMesa=:codigoMesaActual' );
            $consulta->bindParam( ':nEstado', $nEstado);
            $consulta->bindParam( ':codigoMesaActual', $actual->codigoMesa);
            $consulta->execute();
        } catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function UltimocodigoMesa( ){
        try{
            $ultimoNumero = 0;
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT codigoMesa FROM mesas ORDER BY codigoMesa DESC LIMIT 1' );
            $consulta->bindColumn('codigoMesa', $ultimoNumero );
            $consulta->execute();
            $consulta->fetch(PDO::FETCH_BOUND);
            return $ultimoNumero;
        }catch( Exception $e){
            echo $e->getMessage();
        }
    }

    public function AgregarFoto( $fotoMesa ){
        
    }

    public static function ObtenerEstado( $estadoNumero ){
        switch($estadoNumero){
            case Mesa::CLIENTE_ESPERANDO:  return "CLIENTE_ESPERANDO";
            case Mesa::CLIENTE_COMIENDO:  return "CLIENTE_COMIENDO";
            case Mesa::CLIENTE_PAGANDO:  return "CLIENTE_PAGANDO";
            case Mesa::CERRADA:  return "CERRADA";
            case Mesa::CANCELADA: return "CANCELADA";
        }
    }
    // public static function Alta( $pedidos, $fotoMesa ){
    //     try{
    //         include_once 'Utilidades.php';

    //         $path = dirname(__FILE__)."\\datos";
    //         $archivo = $path . "\\logmesas.json";
    //         $ncodigoMesa = Mesa::ObtenercodigoMesaMesa();
    //         $nMesa = new Mesa( Mesa::CLIENTE_ESPERANDO, $ncodigoMesa, $pedidos, $fotoMesa );
    //         $arrMesas = Utilidades::AgregarElemento( $archivo, $nMesa );
    //         return $arrMesas;

    //     }catch(Exception $e){
    //         echo $e->getMessage();
    //     }
    // }

    // public static function Baja( $codigoMesaMesa ){
    //     try{

    //         $arrMesas = self::ObtenerListado();
    //         $indice = self::BuscarPorcodigoMesa( $codigoMesaMesa );
    //         if( $indice ){
    //             $arrMesas[$indice]->estado = Mesa::CANCELADA;
    //         }
            
    //         self::GuardarListado( $arrMesas );

    //     }catch(Exception $e){
    //         echo $e->getMessage();
    //     }
    // }

    // public static function Modificacion($codCambio, $nEstado = null, $nPedidos = array() )
    // {
    //     try{
    //         $arrMesas = self::ObtenerListado();
    //         $indice = self::BuscarPorcodigoMesa($codCambio);
    //         if( $indice ){
    //             $arrMesas[$indice]->estado = $nEstado;
    //             $arrMesas[$indice]->pedidos = $nPedidos;
    //         }
    //         self::GuardarListado( $arrMesas );
    //     } catch(Exception $e){
    //         echo $e->getMessage();
    //     }
    // }

    // public static function BuscarPorcodigoMesa( $codigoMesaMesa ){
    //     $arrMesas = self::ObtenerListado();
    //     for( $i = 0; $i < count($arrMesas); $i++ ){
    //         if( $arrMesas[$i]->codigoMesa == $codigoMesaMesa ){
    //             return $i;
    //         }
    //     }
    //     return false;
    // }

    // public static function ObtenerListado(){
    //     include_once 'Utilidades.php';

    //     $path = dirname(__FILE__)."\\datos";
    //     $archivo = $path . "\\logmesas.json";

    //     return Utilidades::LeerJson( $archivo );
    // }

    // private static function GuardarListado( $arrMesas ){
    //     include_once 'Utilidades.php';

    //     $path = dirname(__FILE__)."\\datos";
    //     $archivo = $path . "\\logmesas.json";

    //     return Utilidades::SubirJson($archivo, $arrMesas);
    // }

    // private static function ObtenercodigoMesaMesa(){
    //     include_once 'Utilidades.php';
    //     $ultiCod = ( Utilidades::ObtenerDeValores( 'mesasUCod' ) + 1);
    //     Utilidades::ModificarEnValores( 'mesasUCod', $ultiCod );
    //     return $ultiCod;
    // }

    public function __toString()
    {
        return '[MESA.'.$this->codigoMesa.']'." Estado: ".Mesa::ObtenerEstado($this->estado)."\n";
    }
}