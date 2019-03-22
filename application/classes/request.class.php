<?php namespace Poly\Core;

defined('APP_EPK') or die;

/**
 * Description of request
 *
 * @author informatica1
 */
final class Request {
    /**
     * @var string
     */
    private $_context  = 'home';
    /**
     * @var string
     */
    private $_action = 'default';
    /**
     * @var array
     */
    private $_data = [];
    /**
     * @param array $data
     */
    private final function __construct( array $data ) {
        
        foreach( $data as $var => $val ){
        
            $this->_data[$var ] = $val;
        }
    }
    /**
     * @return String
     */
    public final function __toString() {
        return sprintf('%s[%s.%s]', \Polymorphic::className($this) ,$this->_context,$this->_action);
    }
    /**
     * @param string $var
     * @param mixed $default
     * @return mixed
     */
    public final function get( $var , $default = null ){
        return isset($this->_data[$var]) ? $this->_data[$var] : $default;
    }
    /**
     * @param string $var
     * @return int
     */
    public final function getInt( $var ){
        return intval($this->get($var,0));
    }
    /**
     * @param string $var
     * @param string $separator
     * @return array
     */
    public final function getArray( $var , $separator = ',' ){
        
        $value = $this->get($var,array());

        return is_array($value) ? $value : explode($separator, $value);
    }
    /**
     * @return string
     */
    public final function getContext(){
        return $this->_context;
    }
    /**
     * @return string
     */
    public final function getAction(){
        return  $this->_action;
    }
    /**
     * @return \Poly\Core\Request
     */
    public static final function import(){
        
        $get = filter_input_array(INPUT_GET);
        
        $post = filter_input_array(INPUT_POST);
        
        return new Request( array_merge(
                !is_null($get) ? $get : [],
                !is_null($post) ? $post : []));
    }
    /**
     * @return \Poly\Core\Request
     */
    public static final function importRoute(){

        $server = \Polymorphic::instance()->get('server_url', filter_input(INPUT_SERVER, 'SERVER_NAME'));
        
        $path = filter_input( INPUT_SERVER, 'REQUEST_URI');

        $route = parse_url( $path );
        
        $R = Router::find($route['path']);
        
        return !is_null($R) ? new Request( $R->request( ) ) : NULL;
    }
}


