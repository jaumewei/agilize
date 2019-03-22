<?php namespace Poly\Core;

defined('APP_EPK') or die;

include \Poly\Core\HTML;
include \Poly\Core\Dictionary;

/**
 * Generador de la vista
 */
abstract class Renderer extends Response{
    /**
     * @var array
     */
    private $_settings = [
        //definir propiedades
    ];
    /**
     *
     * @var \Poly\Core\Dictionary
     */
    private $_content = NULL;
    /**
     * 
     * @param int $code
     * @param string $message
     */
    protected function __construct($code = self::OK, $message = '') {
        
        //constructor de la vista
        
        parent::__construct($code, $message);
    }
    /**
     * Propiedad de la vista
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get( $name , $default = NULL ){
        return array_key_exists( $name, $this->_settings) ?
                $this->_settings[ $name ] : 
                $default;
    }
    /**
     * 
     * @param string $name
     * @return mixed
     */
    protected function __get($name) {
        
        if( strlen($name) > 5 && substr($name, 0 , 5) === 'data_' ){
            //is data
            return $this->__data(substr($name, 5));
        }
        if( strlen($name) > 8 && substr($name, 0 , 8) === 'display_' ){
            //is a display
            return $this->__display(substr($name, 8));
        }
        if( strlen($name) > 6 && substr($name, 0 , 6) === 'input_' ){
            //form input
            return $this->__input(substr($name, 6));
        }
        if( strlen($name) > 7 && substr($name, 0 , 7) === 'render_' ){
            //include php
            return $this->__render(substr($name, 7));
        }

        //propiedad de la vista
        return $this->get($name,FALSE);
    }
    /**
     * Busca la definición del tipo en el diccionario y la representa en un campo de formulario.
     * @param string $input
     * @return String|HTML
     */
    protected function __input( $input ){
        //form input
        if( !is_null($this->_content)){
            
            $inputDef = $this->_content->getDefinition($input);
            
            switch ( $this->_content->getType($input) ){
                case Dictionary::TYPE_CHECKBOX:
                    return HTML::inputCheckBox(
                            $input,
                            $inputDef['checked'],
                            $inputDef['value'],
                            ['class'=>'form-input']);
                case Dictionary::TYPE_NUMBER:
                    return HTML::inputCheckBox(
                            $input,
                            $inputDef['checked'],
                            $inputDef['value'],
                            ['class'=>'form-input']);
                case Dictionary::TYPE_LIST:
                    return HTML::inputDropDown(
                            $input,
                            $this->_content->getOptions($input),
                            $inputDef['value'],
                            ['class'=>'form-input']);
                case Dictionary::TYPE_DATE:
                    return HTML::inputDate(
                            $input,
                            $inputDef['value'],
                            ['class'=>'form-input']);
                case Dictionary::TYPE_DATETIME:
                    return HTML::inputDate(
                            $input,
                            $inputDef['value'],
                            ['class'=>'form-input']);
                case Dictionary::TYPE_DATE:
                    return HTML::inputDate(
                            $input,
                            $inputDef['value'],
                            ['class'=>'form-input']);
                case Dictionary::TYPE_TEXT:
                    return HTML::inputText(
                            $input,
                            $inputDef['value'],
                            ['class'=>'form-input']);
                case Dictionary::TYPE_TEXTAREA:
                    return HTML::inputTextArea(
                            $input,
                            $inputDef['value'],
                            ['class'=>'form-input']);
            }
        }
        
        return sprintf( '<!-- invalid_input_%s -->' , $input );
    }
    /**
     * Muestra un módulo o parte de una vista
     * @param string $render
     */
    protected function __render( $render ){
        
        $renderPath = sprintf('');
        
        if(file_exists($renderPath)){
            
            require $renderPath;
        }
        else{
            printf('<!-- invalid_render_%s -->',$render);
        }
    }
    /**
     * Retorna el resultado de un método interno o un parámetro de la vista, por defecto
     * @param string $display
     * @return string
     */
    protected function __display( $display ){
        
        $callback = sprintf('display%s',$display);
        
        return method_exists($this, $callback) ?
                //callback function
                $this->$callback() : 
                //view setting
                sprintf('<!-- invalid_display_%s -->',$display);
    }
    /**
     * Valor de un atributo del contenido
     * @param string $data
     * @return string
     */
    protected function __data( $data ){
        
        if( !is_null($this->_content)){
            
            return strval( $this->_content->getValue($data,'') );
        }
        else{
            return sprintf('<!-- invalid_data_%s -->',$data);
        }
    }
    /**
     * Título de la vista
     * @return string
     */
    protected function displayTitle(){
        if( !is_null($this->_content)){
            $title = $this->_content->findByAttribute('title',TRUE);
            if( $title !== FALSE ){
                return $this->_content->getValue($title);
            }
        }
        return $this->get('title', '');
    }

    /**
     * Define la composición del contenido
     */
    abstract function render();
}





