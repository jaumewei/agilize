<?php namespace Poly\Providers;
/**
 * 
 */
class Encription {
    
    private $_key;
    private $_method = MCRYPT_RIJNDAEL_256;
    private $_mode = MCRYPT_MODE_CBC;
    
    public function __construct( $key , $method = MCRYPT_RIJNDAEL_256 , $mode = MCRYPT_MODE_CBC ) {
        
        $this->_key = $key;

        $this->_method = $method;
        
        $this->_mode = $mode;
    }
    /**
     * @param string $content
     * @return string
     */
    public final function encrypt( $content ) {

        return base64_encode(mcrypt_encrypt(
                        $this->_method,
                        md5($this->_key),
                        $content,
                        $this->_mode,
                        md5(md5($this->_key))));
    }
    /**
     * @param string $content
     * @return string
     */
    public final function decrypt( $content ) {
        return rtrim(mcrypt_decrypt(
                        $this->_method,
                        md5($this->_key),
                        base64_decode($content),
                        $this->_mode,
                        md5(md5($this->_key))), "\0");
    }
}



