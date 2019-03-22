<?php namespace Poly\Core;

defined('APP_EPK') or die;

/**
 * Description of request
 * 
 * @link http://php.net/manual/es/function.http-response-code.php Resource
 *
 * @author informatica1
 */
final class Response {
    
    const CONTINIUE = 100;
    const SWITCHING_PROTOCOLS = 101;
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NONAUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const MOVED_TEMPORARILY = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 408;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUESTURI_TOO_LARGE = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const IM_A_TEAPOT = 418;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    
    private $_code = self::OK;
    
    private $_message = '';
    
    private $_headers = [];
    /**
     * 
     * @param int $code
     */
    protected function __construct( $code = self::OK , $message = '' ) {
        
        $this->_code = $code;
        
        if(strlen($message)){
            $this->_message = $message;
        }
        else{
            
            $message_list = self::listMessages();
            
            $this->_message = isset($message_list[$this->_code]) ?
                    $message_list[$this->_code] : 
                    'Custom Header Message';
        }
    }
    /**
     * @return string
     */
    public final function __toString() {
        $output = [ $this->message() ];
        foreach($this->_headers as $hdr){
            $output[] = $hdr;
        }
        return implode("\n", $output);
    }
    /**
     * Listado de mensajes de respuesta predefinidos
     * @return array
     */
    public static final function listMessages(){
        return [
            self::CONTINIUE => 'Continue',
            self::SWITCHING_PROTOCOLS => 'Switching Protocols',
            self::OK => 'OK',
            self::CREATED => 'Created',
            self::ACCEPTED => 'Accepted',
            self::NONAUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
            self::NO_CONTENT => 'No Content',
            self::RESET_CONTENT => 'Reset Content',
            self::PARTIAL_CONTENT => 'Partial Content',
            self::MULTIPLE_CHOICES => 'Multiple Choices',
            self::MOVED_PERMANENTLY => 'Moved Permanently',
            self::MOVED_TEMPORARILY => 'Moved Temporarily',
            self::SEE_OTHER => 'See Other',
            self::NOT_MODIFIED => 'Not Modified',
            self::USE_PROXY => 'Use Proxy',
            self::BAD_REQUEST => 'Bad Request',
            self::UNAUTHORIZED => 'Unauthorized',
            self::PAYMENT_REQUIRED => 'Payment Required',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Not Found',
            self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
            self::NOT_ACCEPTABLE => 'Not Acceptable',
            self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
            self::REQUEST_TIMEOUT => 'Request Time-out',
            self::CONFLICT => 'Conflict',
            self::GONE => 'Gone',
            self::LENGTH_REQUIRED => 'Length Required',
            self::PRECONDITION_FAILED => 'Precondition Failed',
            self::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
            self::REQUESTURI_TOO_LARGE => 'Request-URI Too Large',
            self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
            self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
            self::NOT_IMPLEMENTED => 'Not Implemented',
            self::BAD_GATEWAY => 'Bad Gateway',
            self::SERVICE_UNAVAILABLE => 'Service Unavailable',
            self::GATEWAY_TIMEOUT => 'Gateway Time-out',
            self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version not supported',
        ];
    }
    /**
     * @return string
     */
    public final function message(){
        
        $protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL');
        
        if(is_null($protocol)){
            $protocol = 'HTTP/1.0';
        }
        
        return sprintf('%s %s %s',$protocol,$this->_code,$this->_message);
    }
    /**
     * @param string $header
     * @return \Poly\Colre\Response
     */
    public final function defineHeader( $header ){
        
        $this->_headers[] = $header;
        
        return $this;
    }
    /**
     * @param mixed $content Contenido a mostrar
     */
    public static function output( $content ){
        
        if(is_object($content) ){
            
            if( get_class( $content ) === self::class ){
                //mostrar respuesta
                $content->validateResponse()->sendHeaders();
            }
            elseif( is_subclass_of( $content , self::class ) ){
                //mostrar respuesta y cuerpo del contenido
                //util para HTML|JSON|XML|...
                $content->validateResponse()->sendHeaders()->sendContent();
            }
            /*elseif(is_subclass_of($content, \Poly\Core\Renderer::class ) ){
                //renderizar vista
                print $content->render();
            }*/
            else{
                //tirar directamente por pantalla
                print \Polymorphic::className($content);
                //print strval($content);
                //print get_class( \Polymorphic::className( $content  ) );
            }
        }
        elseif(is_array($content)){
            //dumpear directamente
            var_dump($content);
        }
        else{
            //tirar contenido a pelo
            print $content;
        }
    }
    /**
     * @return \Poly\Core\Response
     */
    private final function validateResponse(){
        
        $header_list = self::listMessages();
        
        if( !isset($header_list[$this->_code]) ){
            exit( sprintf('Unknown http status code "%s"',htmlentities($this->_code)));
        }
        
        return $this;
    }
    /**
     * @return \Poly\Core\Response
     */
    private final function sendHeaders(){
        
        header( $this->message( ) );
        
        foreach( $this->_headers as $header ){
            
            header( $header );

        }
        
        return $this;
    }
    /**
     * @return string
     */
    protected function sendContent(){ return ''; }
}





