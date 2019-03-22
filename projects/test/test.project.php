<?php
/**
 * Prueba del framework
 */
final class TestProject extends Polymorphic{
    /**
     * 
     */
    protected final function __construct() {
        
        //Definición y registro de librerías
        
        parent::__construct();
    }
    /**
     * @return \Polymorphic
     */
    protected final function finalize() {
        
        return parent::finalize();
    }

    /**
     * Respuesta de la APP
     * @param \Poly\Core\Response | mixed $OUT
     * @return \Polymorphic
     */
    protected final function response( $OUT ) {
        
        return parent::response( $OUT );
    }
    /**
     * Captura de la petición
     * @return \Poly\Core\Request
     */
    protected final function request() {
        
        //
        
        return parent::request();
    }
}



