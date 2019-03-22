<?php
/**
 * 
 */
abstract class Polymorphic {

    const COMPONENT_TYPE_CORE = 100;
    const COMPONENT_TYPE_INTERFACE = 110;

    const COMPONENT_TYPE_PROVIDER = 130;
    const COMPONENT_TYPE_SERVICE = 140;
    const COMPONENT_TYPE_PLUGIN = 150;

    const COMPONENT_TYPE_CONTROLLER = 160;
    const COMPONENT_TYPE_MODEL = 170;
    const COMPONENT_TYPE_RENDER = 180;

    /**
     * @var \Polymorphic
     */
    private static $_instance = NULL;
    
    /**
     * @var array Configuración
     */
    private $_config = [];
    /**
     * @var array List of preloaded Members
     */
    private $_componentdb = [ 
        self::COMPONENT_TYPE_INTERFACE => [
            //interfaces del sistema
        ],
        self::COMPONENT_TYPE_CORE => [
            //componentes del sistema
            'DataBase','Dictionary','Router','Request','Response',
            //componentes MVC
            'Controller','Model',
        ]];
    /**
     * @var Poly\Core\IService[]
     */
    private $_services = [];
    /**
     * @var array
     */
    private $_routes = [];
    /**
     * 
     */
    protected function __construct() {
        
        $this->setupConfig()
                ->preloadRoutes( defined('APP_EPK') ? APP_EPK : NULL )
                ->setupProviders()
                ->setupServices()
                ->setupPlugins();
        
        $this->preloadComponents()->setupRoutes();
    }
    /**
     * @return string Nombre de la clase del iniciador
     */
    public final function __toString() {
        
        $class = get_class($this);
        
        $offset = strrpos('\\', $class);
        
        return $offset !== false ? substr($class, $offset) : $class;
    }
    /**
     * Start Polymorphic
     * 
     * @param string $epk
     * 
     * @throws ErrorProvider
     */
    static public final function start($epk) {
        /**
         * Polymorphic Entry Point Key
         */
        define('APP_EPK', $epk);
        /**
         * Polymorphik root path
         * [application_path]
         */
        define('APP_ROOT', __DIR__);
        /**
         * Captura del timespan para controlar tiempo de respuesta
         */
        define('APP_TIMESTAMP', time());

        try{
            //configura las definiciones globales
            if( is_null(self::$_instance) ){
                
                self::setupDefines();
                
                //provide a bootstrap entry point
                self::$_instance = self::create( $epk );

                if (!is_null( self::$_instance ) ) {
                    
                    self::$_instance->response( self::$_instance->request() )->finalize();
                }
                else{
                    throw new Exception('INVALID_ENTRY_POINT');
                }
            }
            else{
                throw new Exception( '#define.cfg' );
            }            
        }
        catch (Exception $ex) {
            die($ex->getMessage());
        }
    }
    /**
     * Initialize application and load an entry point
     * 
     * @param string $epk Define the Entry-Point Key to register a valid Bootstrap
     * 
     * @return \Polymorphic|null Get a bootstrap intstance or null if failure
     */
    static private final function create($epk) {

        $class = sprintf('%sProject',$epk);

        $path = sprintf('%s/%s/%s.project.php',
                APP_PROJECTS,
                strtolower( $epk ) ,
                strtolower( $epk ) );

        //provide a bootstrap entry point
        if (file_exists($path)) {
            
            require $path;
            
            return (class_exists($class) && is_subclass_of($class, self::class) ) ? new $class() : null;
        }
        
        return null;
    }
    /**
     * @param string $component
     * @param int $type
     * @return string
     */
    protected static final function componentPath( $component, $type = self::COMPONENT_TYPE_PROVIDER ){
        switch ($type) {
            case self::COMPONENT_TYPE_INTERFACE:
                return sprintf('%s/classes/interfaces/%s.php',APP_ROOT, strtolower($component));
            case self::COMPONENT_TYPE_CORE:
                return sprintf('%s/classes/%s.class.php',APP_ROOT, strtolower($component));
            case self::COMPONENT_TYPE_PROVIDER:
                return sprintf('%s/components/providers/%s.php',APP_ROOT, strtolower($component));
            case self::COMPONENT_TYPE_SERVICE:
                return sprintf('%s/components/services/%s.php',APP_ROOT, strtolower($component));
            case self::COMPONENT_TYPE_PLUGIN:
                return sprintf('%s/components/plugins/%s.php',APP_ROOT, strtolower($component));
        }
        return '';
    }
    /**
     * @param string $component
     * @param int $type
     * @return string
     */
    protected static final function componentNameSpace( $component, $type = self::COMPONENT_TYPE_PROVIDER ){
        switch ($type) {
            case self::COMPONENT_TYPE_INTERFACE:
                return sprintf('Poly\Core\Interfaces\I%s',$component);
                //return APP_ROOT . "/components/interfaces/" . strtolower($component) . ".interface.php";
            case self::COMPONENT_TYPE_CORE:
                return sprintf('Poly\Core\%s',$component);
            case self::COMPONENT_TYPE_PROVIDER:
                return sprintf('Poly\Providers\%s',$component);
                //return APP_ROOT . "/providers/" . strtolower($component) . ".provider.php";
            case self::COMPONENT_TYPE_SERVICE:
                return sprintf('Poly\Services\%s',$component);
                //return APP_ROOT . "/components/services/" . strtolower($component) . ".service.php";
            case self::COMPONENT_TYPE_PLUGIN:
                return sprintf('Poly\Plugins\%s',$component);
                //return APP_ROOT . "/components/services/" . strtolower($component) . ".service.php";
        }
        return '';
    }
    /**
     * Extrae el NS del nombre de  la clase
     * @param string $component
     * @return string
     */
    public static final function className( $component ){
        
        if(is_object($component)){
            
            $component = get_class($component);
        }
        
        $nodes = explode( '\\', $component);
        
        return $nodes[ count( $nodes ) - 1 ];
    }
    /**
     * @param string $path
     * @param boolean $readKeys
     * @param string|null $project Si se define, busca el archivo de configuración en el proyecto proveido
     * @return array
     */
    protected static final function importConfigFile( $path , $readKeys = TRUE , $project = '' ){
        $config_set = [];
        $config_path = strlen( $project ) ?
                sprintf('%s/%s/config/%s.cfg',APP_PROJECTS,$project,$path) :
                sprintf('%s/config/%s.cfg',APP_ROOT,$path) ;
        if(file_exists($config_path)){
            $buffer = file_get_contents($config_path);
            if( $buffer !== FALSE && strlen($buffer)){
                foreach( explode("\n", $buffer) as $line ){
                    if(strlen($line)){
                        if( $readKeys ){
                            $values = explode('=', trim( $line ) );
                            if( count( $values ) > 1 ){
                                $config_set[ $values[ 0 ] ] = $values[ 1 ];
                            }
                        }
                        else{
                            $config_set[ ] = trim( $line );
                        }
                    }
                }
            }
        }
        return $config_set;
    }
    /**
     * Retorno de la captura de la petición básica de la app
     * @return \Poly\Core\Request
     */
    protected function request() {
        
        return \Poly\Core\Request::import();
    }
    /**
     * 
     * @param \Poly\Core\Response | mixed $OUT
     * @return \Polymorphic
     */
    protected function response( $OUT ){
        
        \Poly\Core\Response::output( $OUT );
        
        return $this;
    }
    /**
     * Sobrescribir para ejecutar aplicación
     * @return \Polymorphic
     */
    /*protected function execute(){
        
        Poly\Core\Response::output( Poly\Core\Controller::create( \Poly\Core\Request::import( ) ) );
        
        return $this;
    }*/
    /**
     * Unload
     * @return \Polymorphic
     */
    protected function finalize(){
        
        return $this;
    }
    /**
     * @return \Polymorphic
     */
    private final function setupConfig(){
        
        foreach( self::importConfigFile('config') as $key => $value ){

            $this->_config[ $key ] = $value;

        }

        return $this;
    }
    /**
     * Registra las rutas de entrada en la aplicación
     * @param string $project
     * @return \Polymorphic
     * @throws Exception
     */
    private final function preloadRoutes( $project ){
        
        try{

            if( is_null( $project ) ){

                throw new Exception( '#UNDEFINED_PROJECT_ROUTING' );
            }

            foreach( self::importConfigFile('routes',FALSE,$project) as $route ){

                $R = explode('@', $route);

                if( count( $R ) < 2 ){

                    if(substr($R[0], 0,1) === '/'){

                        $R[ 0 ] = substr($R[0], 1 , strlen($R[0])-1);
                    }

                    if(substr($R[0], strlen($R[0])-1,1) === '/'){

                        $R[ 0 ] = substr($R[0], 0 , strlen($R[0])-1);
                    }

                    $nodes = explode( '/' , $R[ 0 ] );

                    if( count($nodes) === 1 ){

                        $nodes[ ] = 'default';
                    }

                    $alias = sprintf('%s.%s', strtolower($nodes[0]), strtolower($nodes[1]));

                    $this->_routes[ $alias ] = $route;
                }
                else{
                    $this->_routes[ $R[0] ] = $R[ 1 ];
                }
            }
        }
        catch (\Exception $ex) {
            die( $ex->getMessage() );
        }
        
        return $this;
    }

    /**
     */
    private static final function setupDefines(){
        
        if( !defined( 'APP_PROJECTS' ) ){
            define( 'APP_PROJECTS' , sprintf('%s/../projects',APP_ROOT));
        }
        if( !defined( 'APP_REPOSITORY' ) ){
            define( 'APP_REPOSITORY' , sprintf('%s/../repository',APP_ROOT));
        }
        if( !defined( 'APP_SERVICES' ) ){
            define( 'APP_SERVICES' , sprintf('%s/components/services',APP_ROOT));
        }
        if( !defined( 'APP_THEMES' ) ){
            define( 'APP_THEMES' , sprintf('%s/components/themes',APP_ROOT));
        }
        if( !defined( 'APP_PLUGINS' ) ){
            define( 'APP_PLUGINS' , sprintf('%s/components/plugins',APP_ROOT));
        }

        /*$defines = self::importConfigFile( 'define' );
        
        if( count( $defines ) ){
            foreach( $defines as $define => $value ){

                if( !defined($define)){
                    //prevenir redefiniciones
                    define( $define, $value );
                }
            }
        }*/
        

        //return TRUE;
    }
    /**
     * @return \Polymorphic
     */
    private final function setupServices(){
        
        foreach( self::importConfigFile('services') as $service ){

            $this->register($service, self::COMPONENT_TYPE_SERVICE );
        }

        return $this;
    }
    /**
     * 
     * @return \Polymorphic
     */
    private final function setupProviders(){
        
        foreach( self::importConfigFile( 'providers' , FALSE ) as $provider ){

            $this->register( $provider );
        }

        return $this;
    }
    /**
     * 
     * @return \Polymorphic
     */
    private final function setupPlugins(){
        
        foreach( self::importConfigFile( 'plugins' ) as $plugin ){

            $this->register($plugin, self::COMPONENT_TYPE_PLUGIN);
        }

        return $this;
    }
    /**
     * 
     * @param string $component
     * @param string $type
     * @return \Polymorphic
     */
    private final function register( $component , $type = self::COMPONENT_TYPE_PROVIDER ){
        
        if( !array_key_exists($type, $this->_componentdb ) ){
         
            $this->_componentdb[ $type ] = [ ];
        }
        
        if(in_array($component, $this->_componentdb[ $type  ] ) ){
            
            $this->_componentdb[ $type ][ /*agregar para la precarga*/ ] = $component;
        }
        
        return $this;
    }
    /**
     * Preload all application config settings and components
     * @return \Polymorphic
     */
    private final function preloadComponents() {

        //load app plugins here ...

        //load all required app components here, BEFORE EVERYTHING
        foreach ($this->_componentdb as $type => $components ) {
            
            foreach( $components as $component ){

                $class_name = self::componentNameSpace( $component , $type );

                $path = self::componentPath( $component , $type );
                
                
                if(file_exists($path)){

                    require_once $path;

                    if( !class_exists( $class_name ) ){

                        throw new Exception( '#INVALID_COMPONENT [' . $class_name . ']' );
                    }
                }
                else{
                    throw new Exception( '#INVALID_COMPONENT [' . $path . ']');
                }
            }
        }

        return $this;
    }
    /**
     * Inicializa las rutas
     * @return \Polymophic
     */
    private final function setupRoutes(){
        
        foreach( $this->_routes as $alias => $route ){
            \Poly\Core\Router::register($route, $alias);
        }
        
        return $this;
    }
    /**
     * Require a bootstrapper to define all allowed events before start
     * @param string $eType EventProvider to allow in the bootstrap
     * @param boolean $eAllow Define if this event is allowed or not. Default to true
     */
    protected final function registerService(  Core\IService $service ){
        if(!isset($this->appAllowedEvents[$eType])){
            $this->appAllowedEvents[$eType] = $eAllow;
        }
    }
    /**
     * Load an explicit provider which wasn't included in the core
     * provider's database.
     * @param strin $provider Provider Name
     * @param array $settings
     * @return boolean TRUE if success
     */
    public static final function loadProvider($provider){
        
        if(!(isset(self::$_components[$provider]) && self::$_components[$provider] === self::COMPONENT_TYPE_PROVIDER)){
            
            $P = $provider.'Provider';
            
            if(!class_exists($P)){
                
                $path = APP_ROOT.'/components/providers/'.strtolower($provider).'.provider.php';
                
                if(file_exists($path)){
                    
                    require_once($path);
                    
                    return class_exists($P);
                }
            }
            else{
                return true;
            }
        }
        return false;
    }
    /**
     * 
     * @param string $setting Key value to find within the application config data.
     * @param mixed $default Return an alternate value or null if config key is not found
     * @return mixed|null Returned value or $undef if not found
     */
    public final function get( $setting , $default = null ) {
        return (!is_null($this->_config) && isset($this->_config[$setting]))?
            $this->_config[$setting]:
            $default;
    }
    /**
     * Generates a new Unique GUID-ID
     * @return string Random GUID
     */
    public static final function GenerateGUID() {
        return md5(str_replace('.', '', uniqid(rand(100, 999), true)));
    }
    /**
     * @param string $name
     * @return string
     */
    public static final function classify( $name ){
    
        $chunks = explode('_', $name);
        
        $output = [ ];
        
        foreach( $chunks  as $string ){
        
            $output[] = strtoupper( substr($string, 0,1) ) . substr($string, 1, strlen($string)-1);
        }
        
        return implode('', $output);
    }
    /**
     * Formatea un nombre de fichero como clase utilizando CamelCase
     * @param mixed $class
     * @return string
     */
    public static final function nominalize( $class ){
        
        $class_name =  is_object($class) ? get_class( $class ) : $class;
            
        if( !is_null($class_name)){

            if(is_string($class_name)){
                
                $name = explode('\\', $class_name );

                return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-',  $name[ count($name) - 1 ] ) );
            }
        }
        
        return $class_name;
    }
    /**
     * @return \Polymorphic
     */
    public static final function instance(){
        
        return self::$_instance;
    }
}




