<?php
/**
 * Prueba del framework
 */
final class RouterProject extends \Polymorphic{
    
    protected final function __construct() {
        
        //Definición y registro de librerías
        
        parent::__construct();
    }
    /**
     * @return \Poly\Core\RouterProject
     */
    protected final function finalize() {
        
        return parent::finalize();
    }
    /**
     * @return \Polymorphic
     */
    protected final function request(){
        
        return Poly\Core\Request::importRoute();
    }
    /**
     * Respuesta de la APP
     * @param \Poly\Core\Response | mixed $OUT
     * @return \Polymorphic
     */
    protected final function response($OUT) {
        
        var_dump($OUT);
        die;
        
        
        $routes = \Poly\Core\Router::db();
        
        foreach( $routes as $alias => $path ){
            printf('<p>%s - %s</p>',$alias,$path);
        }
        
        return parent::response($OUT);
    }
}



