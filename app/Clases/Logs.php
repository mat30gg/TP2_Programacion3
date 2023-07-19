<?php

include_once "DatosUsuarioLogueado.php";

class Logs{

    public $logId;
    public $accion;
    public $fecha_accion;
    public $numPed;
    public $id_empleado;

    static function ObtenerListado(){
        try{
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT * FROM logs' );
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_CLASS, 'Logs' );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function CrearLog( $accion, $numPed = null, $id_empleado = null ){
        $log = new self();
        $log->logId = self::UltimoId()+1;
        $log->accion = $accion;
        $log->fecha_accion = date_format( date_create(), "Y-m-d H:i:s" );
        $log->numPed = $numPed;
        $log->id_empleado = $id_empleado;
        if( $id_empleado == null ){
            $log->id_empleado = DatoUsuario::IdEmpleado();
        }
        self::Alta($log);
        return $log;
    }

    public static function Alta( self $log ){
        try{

            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'INSERT INTO logs VALUES (:logId, :accion, :fecha, :pedido, :empleado)' );
            $consulta->bindValue( ':logId', $log->logId );
            $consulta->bindValue( ':accion', $log->accion );
            $consulta->bindValue( ':fecha', $log->fecha_accion );
            $consulta->bindValue( ':pedido', $log->numPed );
            $consulta->bindValue( ':empleado', $log->id_empleado );
            $consulta->execute();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    private static function UltimoId( ){
        try{
            $ultimoNumero = 0;
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( 'SELECT logId FROM logs ORDER BY logId DESC LIMIT 1' );
            $consulta->bindColumn('logId', $ultimoNumero );
            $consulta->execute();
            $consulta->fetch(PDO::FETCH_BOUND);
            return $ultimoNumero;
        }catch( Exception $e){
            echo $e->getMessage();
        }
    }

    static function MesasOrdenadasPorUso( $ascendente ,$desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");
            $orden = "DESC";
            if( $ascendente )
                $orden = "ASC";
            
            $query = "SELECT mesas.codigoMesa, mesas.estado, mesas.fotoMesa, COUNT(mesas.codigoMesa) AS cantidad_usos 
            FROM ((mesas JOIN pedidos ON mesas.codigoMesa = pedidos.codigoMesa) ".
            "JOIN `logs` ON `logs`.`numPed` = pedidos.numPed) ".
            "WHERE `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta ".
            "AND `logs`.`accion` = 'Tomar pedido' ".
            "GROUP BY mesas.codigoMesa ".
            "ORDER BY cantidad_usos ".$orden;

            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll();

            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function CantidadOperacionesPorSector( $ascendente ,$desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");
            $orden = "DESC";
            if( $ascendente )
                $orden = "ASC";
            
            $query = "SELECT puestos.nombre, COUNT(*) AS cantidad_incidencias 
            FROM ((empleados JOIN `logs` ON empleados.id_empleado = `logs`.`id_empleado`) 
            JOIN puestos ON puestos.id_puesto = empleados.id_puesto) ".
            "WHERE `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta ".
            "GROUP BY puestos.nombre ".
            "ORDER BY puestos.nombre ".$orden;

            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll();

            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
        
    }

    public static function CantidadOperacionesDeEmpleadosPorSector( $ascendente ,$desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");
            $orden = "DESC";
            if( $ascendente )
                $orden = "ASC";

            $query = "SELECT empleados.nombre, puestos.nombre AS puesto, COUNT(*) AS cantidad_incidencias 
            FROM ((empleados JOIN `logs` ON empleados.id_empleado = `logs`.`id_empleado`) 
            JOIN puestos ON puestos.id_puesto = empleados.id_puesto)".
            "WHERE `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta ".
            "GROUP BY empleados.nombre ".
            "ORDER BY puestos.nombre ".$orden;
            $objetoPdo = ManejoDB::CrearAcceso();
            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( );

            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public static function CantidadOperacionesDeEmpleado( $id_empleado, $desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");
            
            $objetoPdo = ManejoDB::CrearAcceso();
            $query = "SELECT empleados.nombre, puestos.nombre AS puesto, COUNT(*) AS cantidad_incidencias 
            FROM ((empleados JOIN `logs` ON empleados.id_empleado = `logs`.`id_empleado`) 
            JOIN puestos ON puestos.id_puesto = empleados.id_puesto)".
            "WHERE empleados.id_empleado = :id_empleado ".
            "AND `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta";
            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":id_empleado", $id_empleado);
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->setFetchMode( PDO::FETCH_OBJ );
            $consulta->execute();

            return $consulta->fetch();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function CantidadIngresosAlSistema( $id_empleado ,$desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");

            $objetoPdo = ManejoDB::CrearAcceso();
            $query = "SELECT empleados.nombre, COUNT(*) AS cantidad_logins ".
            "FROM (empleados JOIN `logs` ON empleados.id_empleado = `logs`.`id_empleado`) ".
            "WHERE `logs`.`accion` = \"Log in\" ".
            "AND empleados.id_empleado = :id_empleado ".
            "AND `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta";

            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":id_empleado", $id_empleado);
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->setFetchMode( PDO::FETCH_OBJ );
            $consulta->execute();
            return $consulta->fetch();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function ObtenerProductosOrdenadoPorPopularidad( $ascendente, $desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");
            $orden = "DESC";
            if( $ascendente )
                $orden = "ASC";
            $objetoPdo = ManejoDB::CrearAcceso();
            $query = "SELECT productos.id_producto, productos.nombre, COUNT(*) AS cantidad_vendido ".
            "FROM ((productos JOIN pedidos ON productos.nombre = pedidos.encargo) ".
            "JOIN `logs` ON `logs`.`numPed` = pedidos.numPed) ".
            "WHERE `logs`.`accion` = \"Tomar pedido\" ".
            "AND `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta ".
            "GROUP BY productos.id_producto ".
            "ORDER BY cantidad_vendido ".$orden;
            
            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_OBJ );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function ObtenerMesasPorFactura( $ascendente, $desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");
            $orden = "DESC";
            if( $ascendente )
                $orden = "ASC";
            
            $objetoPdo = ManejoDB::CrearAcceso();
            $query = "SELECT mesas.codigoMesa, SUM(productos.precio) precio_facturado 
            FROM ((mesas JOIN pedidos ON mesas.codigoMesa = pedidos.codigoMesa) 
            JOIN productos ON pedidos.encargo = productos.nombre) 
            JOIN `logs` ON `logs`.numPed = pedidos.numPed ".
            "WHERE `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta ".
            "AND `logs`.`accion` = 'Tomar pedido' ".
            "GROUP BY mesas.codigoMesa ".
            "ORDER BY precio_facturado ".$orden;
            
            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->execute();
            $arrayReturn = $consulta->fetchAll( PDO::FETCH_OBJ );
            return $arrayReturn;
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    static function ObtenerFacturacionMesa( $codigoMesa, $desde = null, $hasta = null ){
        try{
            $paramDesde = $desde ?? date_format( date_create("-100 years"), "Y-m-d" );
            $paramHasta = $hasta ?? date_format( date_create(), "Y-m-d H:i:s");
            
            $objetoPdo = ManejoDB::CrearAcceso();
            $query = "SELECT mesas.codigoMesa, SUM(productos.precio) precio_facturado 
            FROM ((mesas JOIN pedidos ON mesas.codigoMesa = pedidos.codigoMesa) 
            JOIN productos ON pedidos.encargo = productos.nombre) 
            JOIN `logs` ON `logs`.numPed = pedidos.numPed ".
            "WHERE `logs`.`fecha_accion` > :fecha_desde ".
            "AND `logs`.`fecha_accion` <= :fecha_hasta ".
            "AND `logs`.`accion` = 'Tomar pedido' ".
            "AND mesas.codigoMesa = :codigoMesa ";
            
            $consulta = $objetoPdo->RetornarConsulta( $query );
            $consulta->bindValue(":codigoMesa", $codigoMesa);
            $consulta->bindValue(":fecha_desde", $paramDesde);
            $consulta->bindValue(":fecha_hasta", $paramHasta);
            $consulta->setFetchMode( PDO::FETCH_OBJ );
            $consulta->execute();
            return $consulta->fetch();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
}