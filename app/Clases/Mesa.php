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

    public function __construct( $estado = null, $codigoMesa = null, $fotoMesa = null)
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
            $codigoMesa = Mesa::UltimocodigoMesa()+1;
            $mesa = new Mesa( Mesa::CLIENTE_ESPERANDO, $codigoMesa );
            $mesa->AgregarFoto( $fotoMesa );
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
                $consulta = $objetoBDO->RetornarConsulta( 'UPDATE mesas SET estado = :estadoCancelado fotoMesa = :fotoMesa WHERE codigoMesa = :codMesa' );
                $consulta->bindValue( ':codMesa', $mesa->codigoMesa );
                $consulta->bindValue( ':fotoMesa', $mesa->fotoMesa);
                $consulta->bindValue( ':estadoCancelado', $mesa::CANCELADA );
                $consulta->execute();
            }
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function Modificacion($codigo, $nuevo )
    {
        try{
            $objetoBDO = ManejoDB::CrearAcceso();
            $consulta = $objetoBDO->RetornarConsulta( 'UPDATE mesas SET estado=:nEstado WHERE codigoMesa=:codigo' );
            $consulta->bindParam( ':nEstado', $nuevo->estado);
            $consulta->bindParam( ':codigo', $codigo);
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
        $this->fotoMesa = null;
        if( isset($fotoMesa) ){
            $targetDir = __DIR__ . "/../fotos/mesas/";
            $targetFile = $targetDir . date("Ymd", time()) .$this->codigoMesa . basename($fotoMesa["name"]);
            if( !is_dir($targetDir))
                mkdir( $targetDir, 0777, true);
            move_uploaded_file( $fotoMesa["tmp_name"], $targetFile );
            $this->fotoMesa = basename( $targetFile );
        }
    }

    static function ObtenerPorCodigo( $codigo ){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( "SELECT * FROM mesas WHERE codigoMesa = :codigo" );
            $consulta->bindParam( ':codigo', $codigo );
            $consulta->setFetchMode(PDO::FETCH_CLASS, 'Mesa');
            $consulta->execute();
            return $consulta->fetch();
        }catch(Exception $e){
            echo $e->getMessage();
        }
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

    public function setEstado( $estado ){
        $this->estado = $estado;
        self::Modificacion( $this->codigoMesa, $this);
    }

    public function __toString()
    {
        return '[MESA.'.$this->codigoMesa.']'." Estado: ".Mesa::ObtenerEstado($this->estado)."\n";
    }
}