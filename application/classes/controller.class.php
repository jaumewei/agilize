<?php namespace Poly\Core;

defined('APP_EPK') or die;

/**
 * @todo Completar redirecciones automatizadas desde el controlador (respuesta Request)
 * @todo Compltar respuesta de redirección de error
 * 
 * 
 * 
 * El controlador generará una  respuesta a la petición de usuario GET/POST/PUT/loquesea
 * 
 * Retornando cualquier cosa que será procesada por Request::output()
 * 
 * @link http://php.net/manual/es/function.http-response-code.php Resource
 *
 * @author informatica1
 */
abstract class Controller {

    protected function __construct() {
        
    }
    
    public function __toString() {
        return get_class($this);
    }
    /**
     * 
     */
    abstract protected function default_action( Request $request );
    /**
     * 
     * @param \Poly\Core\Request $request
     * @return \Poly\Core\Response
     */
    protected function redirect( Request $request ){
       
        return new Response();
    }
    /**
     * @return \Poly\Core\Response
     */
    protected function error( Request $request ){
        
        return new Response(Response::NOT_FOUND);
    }
    
    public function action( Request $request , $action = 'default' ){
        
        $callback = sprintf('%s_action',$action);
        
        return  method_exists($this, $callback) ?
                $this->$callback( $request ) : 
                $this->error( $request );
    }
    /**
     * 
     * @param \Poly\Core\Request $request
     * @param string $controller (optional)
     * @return \Poly\Core\Controller
     */
    public static final function create( Request $request , $controller = null ){
        
        if(is_null($controller)){
            $controller = $request->getContext();
        }
        
        
        
        return null;
    }
}




