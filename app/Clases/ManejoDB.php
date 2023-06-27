<?php

class ManejoDB {

    private static $ObjetoAccesoDatos;
    private $objetoPDO;


    
    private function __construct()
    {
        try {
            $this->objetoPDO = new PDO('mysql:host=localhost;port=3310;dbname=tpprog02_comanda', 'root', '', );
        } catch ( Exception $e ) {
            echo "Error! " . $e->getMessage();
            die;
        }
    }

    public function RetornarConsulta( $query ) {
        return $this->objetoPDO->prepare( $query );
    }

    public static function CrearAcceso() {
        if( !isset(self::$ObjetoAccesoDatos)) {
            self::$ObjetoAccesoDatos = new ManejoDB();
        }
        return self::$ObjetoAccesoDatos;
    }
}