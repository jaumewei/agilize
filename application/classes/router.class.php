<?php namespace Poly\Core;

defined('APP_EPK') or die;
/**
 * 
 */
class Router{
    
    const REGEX = '/{([a-z0-9\_]+)}?/';
    
    /**
     * @var Router[]
     */
    private static $_routes = [];
    
    private $_name = NULL;
    
    private $_nodes = [];
    
    private $_params = [];
    
    protected function __construct( $route , $alias = null ) {
        
        $this->_name = $alias;

        $this->setupRoute($route);
    }
    /**
     * Chapuza a medida para extraer parámetros de un nodo
     * @param string $node
     * @return mixed
     */
    private final function regexParam( $node ){
        
        $matches = [];
        
        if( preg_match( self::REGEX , strtolower($node) , $matches ,PREG_OFFSET_CAPTURE ) ){

            if( count( $matches ) > 1 && count( $matches[1] ) ){
            
                return $matches[1][0];
            }
        }
        
        return FALSE;
    }

    /**
     * @param string|URL $route
     * @return \Route
     */
    private final function setupRoute( $route ){
        
        foreach( self::extract($route) as $node ){
            
            if( ( $param = $this->regexParam( $node ) ) !== FALSE ){
                //si res un {parametro}, registrar en la lista
                $this->_params[ $param ] = '';

                $this->_nodes[ ] = $param;
            }
            else{
    
                $this->_nodes[ ] = $node;
            }
        }
        
        return $this;
    }
    /**
     * @return string
     */
    public final function __toString() {
        
        return !is_null($this->_name) ? $this->_name : $this->getPath();
    }
    /**
     * @param string $name
     * @return string
     */
    public final function __get($name) {
        return $this->getParam($name);
    }
    /**
     * @return string
     */
    public final function getPath(){
        return sprintf('/%s/', implode('/', $this->_nodes) );
    }
    /**
     * @return string
     */
    public final function getAlias(){
        
        return $this->_name;
        
    }
    /**
     * @return string
     */
    public final function getModule(){
        return count($this->_nodes) ? $this->_nodes[0] : '';
    }
    /**
     * @return string
     */
    public final function getAction(){
        return count($this->_nodes) > 1 ? $this->_nodes[1] : '';
    }
    /**
     * @return array
     */
    public final function request(){
        
        return array_merge( $this->_params, [
                    'context' => $this->getModule(),
                    'action' => $this->getAction(),
                ]);
    }
    /**
     * @param boolean $keysOnly
     * @return array
     */
    public final function listParams( $keysOnly = TRUE ){
        return $keysOnly ?
                array_keys($this->_params) :
                $this->_params;
    }

    /**
     * @param string $param
     * @return boolean
     */
    public final function hasParam( $param ){
        return array_key_exists($param, $this->_params);
    }
    /**
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public final function getParam( $param  ){
        return array_key_exists($param, $this->_params) ? $this->_params[$param] : '';
    }
    /**
     * @param string $param
     * @param string $value
     * @return \Poly\Core\Router
     */
    protected final function setParam( $param , $value = '' ){
        $this->_params[ $param ] = $value;
        return $this;
    }
    /**
     * @param string|URL $url
     * @param boolean $import
     * @return boolean
     */
    protected function match( $url , $import = FALSE ){

        $nodes = self::extract($url);
        
        //validar que tiene los mismos nodos
        if( count( $nodes ) === count( $this->_nodes )  ){
            
            for( $n = 0; $n < count( $this->_nodes ) ; $n++ ){

                //Si es un parámetro de la ruta, interceptar aqui
                if( $this->hasParam($this->_nodes[$n]) && $import ){
                    //importar valor del parámetro si es preciso ( útil para la Request )
                    $this->setParam( $this->_nodes[$n],$nodes[$n]);
                }
                //si no coincide con el resto de nodos de la ruta, descarttar match
                elseif( $nodes[ $n ] !== $this->_nodes[ $n ] ){
                    //print 'Node doesnt match '. $nodes[$n] . ':' . $this->_nodes[$n];
                    return FALSE;
                }
            }
            
            return TRUE;
        }
        
        return FALSE;
    }
    /**
     * @return array
     */
    public function nodes(){
        
        return $this->_nodes;
    }
    /**
     * @return int
     */
    public function countNodes(){
        
        return count( $this->_nodes );
    }
    /** 
     * Registra una ruta en la aplicación para definir los puntos de entrada
     * @param string $path Ruta a registrar en la bd de rutas
     * @param string $alias por defecto, genera el alias de los 2 primeros nodos de la ruta
     * @return \Poly\Core\Router
     */
    public static final function register( $path , $alias  ){

        if ( !isset( self::$_routes[ $alias ] ) ){
            
            $route = new Router( $path, $alias );

            self::$_routes[ $alias ] = $route;
            
            return $route;
        }
        
        return NULL;
    }
    /**
     * @param string|URL $url
     * @return \Poly\Core\Router|NULL
     */
    public static final function find( $url ){
        
        $path = parse_url( $url , PHP_URL_PATH );
        
        foreach( self::$_routes as $R ){
            
            if(  $R->match( $path , TRUE ) ){
                
                return $R;
            } 
        }
        
        return NULL;
    }
    /**
     * @return \Poly\Core\Router[]
     */
    public static final function db(){
        
        return self::$_routes;
    }
    /**
     * @return array
     */
    public static final function extract( $url ){
        
        $nodes = [];
        
        $path = preg_replace('/\/\//', '/', parse_url( strtolower( $url ) ,PHP_URL_PATH));
        
        foreach( explode('/', $path) as $node ){
            if(strlen($node)){
                $nodes[] = $node;
            }
        }

        return $nodes;
    }
}

