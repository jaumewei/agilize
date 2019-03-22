<?php namespace Poly\Core;

defined('APP_EPK') or die;
/**
 * 
 */
abstract class Component {
    
    private $_data = [];
    
    public function __get($name) {
       
        $var = sprintf('_%s',strtolower($name));
        
        return isset($this->$var) ? $this->$var : '';
    }
    /**
     * 
     * @param String $name
     * @param mixed $default
     * @return mixed
     */
    public function get( $name , $default = NULL ){
        
        return isset( $this->_data[$name] ) ? $this->_data[$name] : $default;
    }
    /**
     * 
     * @param String $name
     * @param mixed $value
     */
    protected function set( $name,  $value ){

        $this->_data[$name] = $value;
    }
}




