<?php namespace Poly\Core;
/**
 * Gestor de componentes html y formularios
 */
class HTML{
    /**
     * Nodo HTML
     * @var string
     */
    private $_htmlNode;
    /**
     * Attributos [key] => value
     * @var array
     */
    private $_htmlAttributes = array();
    /**
     * Contenido [ value ]
     * @var mixed
     */
    protected $_htmlContent = null;
    /**
     * @param string $tag
     * @param array $attributes
     * @param mixed $content
     */
    public function __construct( $tag , array $attributes , $content = null ) {
        $this->_htmlNode = $tag;
        $this->_htmlAttributes = $attributes;
        $this->_htmlContent = $content;
    }
    /**
     * <custom />
     * @param string $TAG
     * @param array $attributes
     * @param mixed $content
     * @return HTML
     */
    protected static final function __html( $TAG , array $attributes , $content = null ){

        if( isset( $attributes['class'])){
            if(is_array($attributes['class'])){
                $attributes['class'] = implode(' ', $attributes['class']);
            }
        }
        
        $serialized = array();
        
        foreach( $attributes as $att => $val ){
            $serialized[] = sprintf('%s="%s"',$att,$val);
        }
        
        if( !is_null($content) ){

            if(is_object($content)){
                $content = strval($content);
            }
            elseif(is_array($content)){
                $content = implode(' ', $content);
            }
            
            return sprintf('<%s %s>%s</%s>' , $TAG ,
                    implode(' ', $serialized) , strval( $content ) ,
                    $TAG);
        }
        
        return sprintf('<%s %s />' , $TAG , implode(' ', $attributes ) );
    }
    /**
     * @return String
     */
    public function __toString() {
        \CodersThemeManager::nominalize( $this );
    }
    /**
     * Parsea el contenido a HTML
     * @return HTML
     */
    public function __toHtml(){
        return self::__html($this->_htmlNode, $this->_htmlAttributes, $this->_htmlContent);
    }
    /**
     * @param string $att
     * @return \CODERS\Theme2\HTML
     */
    protected final function __removeAttribute( $att ){
        if( isset( $this->_htmlAttributes[$att])){
            unset( $this->_htmlAttributes[$att]);
        }
        return $this;
    }
    /**
     * @param string $att
     * @param mixed $value
     * @return \CODERS\Theme2\HTML
     */
    protected final function __setAttribute( $att , $value ){
        $this->_htmlAttributes[$att] = $value;
        return $this;
    }
    /**
     * <meta />
     * @param array $attributes
     * @param string $name
     * @return HTML
     */
    public static final function meta( array $attributes , $name = null ){
        
        if( !is_null($name)){

            $attributes['name'] = $name;
        }
        
        $serialized = array();
        
        foreach( $attributes as $attribute => $value ){
            if(is_array($value)){
                $valueInput = array();
                foreach( $value as $valueVar => $valueVal ){
                    $valueInput[] = sprintf('%s=%s',$valueVar,$valueVal);
                }
                $serialized[] = sprintf('%s="%s"',$attribute, implode(', ', $valueInput) );
            }
            else{
                $serialized[] = sprintf('%s="%s"',$attribute,$value);
            }
        }
        
        return self::__html('meta', $serialized );
    }
    /**
     * <link />
     * @param URL $url
     * @param string $type
     * @param array $attributes
     * @return HTML
     */
    public static final function link( $url , $type , array $attributes = array( ) ){
        
        $attributes[ 'href' ] = $url;
        
        $attributes[ 'type' ] = $type;
        
        return self::__html( 'link', $attributes );
    }
    /**
     * <a href />
     * @param type $url
     * @param type $label
     * @param array $atts
     * @return HTML
     */
    public static function a( $url , $label , array $atts = array( ) ){
        
        $atts['href'] = $url;
        
        if( !isset($atts['target'])){
            $atts['target'] = '_self';
        }
        
        return self::__html('a', $atts, $label);
    }
    /**
     * <ul></ul>
     * @param array $content
     * @param array $atts
     * @param mixed $itemClass
     * @return HTML
     */
    public static function ul( array $content , array $atts , $itemClass = '' ){
        
        $collection = array();
        
        foreach( $content as  $item ){
            $collection[] = !empty($itemClass) ?
                    self::__html('li', array('class'=>$itemClass) , $item ) :
                    self::__html('li', array(), $item ) ;
        }
        
        return self::__html( 'ul' , $atts ,  $collection );
    }
    /**
     * <ol></ol>
     * @param array $content
     * @param array $atts
     * @param mixed $itemClass
     * @return HTML
     */
    public static function ol( array $content , array $atts , $itemClass = '' ){
        
        $collection = array();
        
        foreach( $content as  $item ){
            $collection[] = !empty($itemClass) ?
                    self::__html('li', array('class'=>$itemClass) , $item ) :
                    self::__html('li', array(), $item ) ;
        }
        
        return self::__html( 'ol' , $atts ,  $collection );
    }
    /**
     * <span></span>
     * @param string $content
     * @param array $atts
     * @return HTML
     */
    public static final function span( $content , $atts = array( ) ){
        return self::__html('span', $atts , $content );
    }
    /**
     * <img src />
     * @param string/URL $src
     * @param array $atts
     * @return HTML
     */
    public static final function img( $src , array $atts = array( ) ){
        
        $atts['src'] = $src;
        
        return self::__html('img', $atts);
    }
    /**
     * <label></label>
     * @param string $input
     * @param string $text
     * @param mixed $class
     * @return HTML
     */
    public static function label( $text , array $atts = array() ){

        return self::__html('label', $atts, $text);
    }
    /**
     * <input type="number" />
     * @param String $input
     * @param int $value
     * @param array $atts
     * @return HTML
     */
    public static function inputNumber( $input, $value = 0, array $atts = array() ){
        
        if( !isset($atts['min'])){ $atts['min'] = 0; }

        if( !isset($atts['step'])){ $atts['step'] = 1; }
        
        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        
        $atts['name'] = $input;
        
        $atts['value'] = $value;
        
        $atts['type'] = 'number';
        
        return self::__html('input', $atts);
    }
    /**
     * <span class="price" />
     * @param string $input
     * @param int $value
     * @param string $coin
     * @return HTML
     */
    public static function price( $input, $value = 0.0, $coin = '&eur', array $atts = array() ){

        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        
        return self::__html('span',
                $atts ,
                $value . self::__html('span', array('class'=>'coin'), $coin));
    }
    /**
     * <textarea></textarea>
     * @param string $input
     * @param string $value
     * @param array $atts
     */
    public static function inputTextArea( $input, $value = '', array $atts = array() ){

        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        
        return self::__html('textarea', $atts, $value);
    }
    /**
     * <input type="text" />
     * @param string $input
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputText($input, $value = '', array $atts = array() ){
        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        $atts['value'] = $value;
        $atts['type'] = 'text';
        
        return self::__html( 'input' , $atts );
    }
    /**
     * <input type="password" />
     * @param string $input
     * @param array $atts
     * @return HTML
     */
    public static function inputPassword( $input, array $atts = array() ){
        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        $atts['type'] = 'password';
        return self::__html( 'input' , $atts );
    }
    /**
     * <input type="search" />
     * @param string $input
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputSearch( $input, $value = '' , array $atts = array() ){

        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        $atts['value'] = $value;
        $atts['type'] = 'search';
        return self::__html( 'input' , $atts );
    }
    /**
     * <input type="date" />
     * Versión con jQuery UI
     * <input type="text" class="hasDatepicker" />
     * @param string $input
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputDate($input, $value = '', array $atts = array() ){

        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        $atts['value'] = $value;
        $atts['type'] = 'date';
        return self::__html( 'input' , $atts );
    }
    /**
     * <input type="tel" />
     * @param string $input
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputTelephone($input, $value = null, array $atts = array() ){

        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        $atts['value'] = $value;
        $atts['type'] = 'tel';
        return self::__html( 'input' , $atts );
    }
    /**
     * <input type="email" />
     * @param string $input
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputEmail($input, $value = '', array $atts = array() ){
        
        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        $atts['value'] = $value;
        $atts['type'] = 'email';
        return self::__html( 'input' , $atts );
    }
    /**
     * <input type="checkbox" />
     * @param string $input
     * @param boolean $checked
     * @param array $atts
     * @return HTML
     */
    public static function inputCheckBox( $input, $checked = false , $value = 1, array $atts = array() ){
        
        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        $atts['name'] = $input;
        $atts['value'] = $value;
        $atts['type'] = 'checkbox';
        if($checked){ $atts['checked'] = 1; }
        return self::__html( 'input' , $atts );
    }
    /**
     * Lista de opciones <input type="radio" />
     * @param String $input
     * @param array $options
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputOptionList( $input, array $options, $value = null, array $atts = array( ) ){


        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input );
        
        $radioItems = array();
        
        $baseAtts = array( 'type' => 'radio' , 'name' => $input );
        
        if( isset($atts['disabled']) ){
            $baseAtts['disabled'] = 'disabled';
            unset($atts['disabled']);
        }
        
        foreach( $options as $option => $label){
            
            $optionAtts = array_merge($baseAtts,array('value'=>$option));
            
            if( !is_null($value) && $option == $value ){
                $optionAtts['checked'] = 'checked';
            }
            
            $radioItems[ ] = self::__html(
                    'li',
                    array(),
                    self::__html( 'input', $optionAtts, $label) );
        }
        
        return self::__html('ul', $atts, implode('</li><li>',  $radioItems));
    }
    /**
     * <select size="5" />
     * @param string $input
     * @param array $options
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputList($input, array $options, $value = null, array $atts = array() ){
        
        if( !isset($atts['id']) ){
            preg_replace('/-/', '_',  $input );
        }
        
        if( !isset($atts['size'])){
            $atts['size'] = 5;
        }
        
        $items = array();
        
        if( isset($atts['placeholder'])){
            $items[''] = sprintf('- %s -', $atts['placeholder'] );
            unset($atts['placeholder']);
        }
        
        foreach( $options as $option => $label ){
            $items[] = self::__html(
                    'option',
                    $option == $value ? array('value'=> $option,'selected') : array('value'=>$option),
                    $label);
        }
        
        return self::__html('select', $atts, $options );
    }
    /**
     * <select size="1" />
     * @param string $input
     * @param array $options
     * @param string $value
     * @param array $atts
     * @return HTML
     */
    public static function inputDropDown($input, array $options, $value = null, array $atts = array() ){
        
        $atts['size'] = 1;
        
        return self::inputList( $input , $options, $value, $atts);
    }
    /**
     * <select size="1" />
     * @param string $input
     * @param int $value
     * @return HTML
     */
    public static function inputHour( $input , $value = 0 , array $atts = array()){
        
        $hours = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23];
        
        $atts['size'] = 1;
        
        if( !array_key_exists('class', $atts) ){
            $atts['class'] = ['input-hour'];
        }
        elseif(is_array ($atts['class'])){
            $atts['class'][] = 'input-hour';
        }
        else{
            $atts['class'] .= ' input-hour';
        }
        
        return self::inputList($input, $hours, $value, $atts );
    }
    /**
     * <input type="hidden" />
     * @param string $input
     * @param string $value
     * @return HTML
     */
    public static function inputHidden( $input, $value ){
        
        return self::__html('input', array(
            'type' => 'hidden',
            'name' => $input,
            'value' => $value,
        ));
    }
    /**
     * <input type="file" />
     * @param string $input
     * @return HTML
     */
    public static function inputFile( $input , array $atts = array( ) ){
        
        $max_filesize = 'MAX_FILE_SIZE';
        
        $atts['id'] = 'id_' . preg_replace('/-/', '_', $input);
        $atts['name'] = $input;
        $atts['type'] = 'file';
        
        $file_size =  pow(1024, 2);
        
        if( isset($atts[$max_filesize]) ){
            $file_size = $atts[$max_filesize];
            unset($atts[$max_filesize]);
        }
        
        return self::renderHidden( $max_filesize, $file_size ).self::__html('file', $atts );
    }
    /**
     * <button type="*" />
     * @param string $input
     * @param string $value
     * @param string $label
     * @param array $atts
     * @return HTML
     */
    public static function inputButton( $input, $value , $label, array $atts = array( ) ){
        
        //$class = isset($atts['class']) ? $atts['class'] : '';
        $atts['value'] = $value;
        $atts['id'] = 'id_' . preg_replace('/-/', '_',  $input ) . '_' . $value;
        $atts['name'] = $input;
        if( !isset($atts['type'])){
            $atts['type'] = 'button';
        }
        return self::__html('button', $atts, $label);
    }
    /**
     * <button type="submit" />
     * @param string $input
     * @param string $value
     * @param string $label
     * @param array $atts
     * @return HTML
     */
    public static function inputSubmit( $input , $value , $label , array $atts = array( ) ){
        return self::inputButton($input, $value, $label, array_merge( $atts , array( 'type'=>'submit' ) ));
    }
}




