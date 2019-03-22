<?php namespace Poly\Core;

defined('APP_EPK') or die;

/**
 * Description of session
 *
 * @author informatica1
 */
class DataBase extends \mysqli{
    
    private static $_db = null;
    /**
     * 
     * @param string $host
     * @param string $username
     * @param string $passwd
     * @param string $dbname
     * @param int $port
     * @param string $socket
     */
    public function __construct( $host = 'ini_get("mysqli.default_host")', $username = 'ini_get("mysqli.default_user")', $passwd = 'ini_get("mysqli.default_pw")', $dbname = "", $port = 'ini_get("mysqli.default_port")', $socket = 'ini_get("mysqli.default_socket")') {
        parent::__construct($host, $username, $passwd, $dbname, $port, $socket);
    }

    /**
     * @param string $query
     * @param int $mode
     * @return mysqli_result | boolean
     * @throws Exception
     */
    public function query( $query, $mode = MYSQLI_STORE_RESULT) {
        
        try{
            
            $result = parent::query($query,$mode);
            
            if( $result === FALSE || $this->errno ){

                throw new Exception( $this->error );
            }
            
            return $result;
        }
        catch ( \Exception $ex) {
            
            die( $ex->getMessage() );
        }
        return FALSE;
    }
    /**
     * @param \mysqli_result $result
     * @return array
     */
    public function fetch( \mysqli_result $result ){
        $output = [];
        if( $result !== FALSE ){
            while( !is_null( $row = $result->fetch_assoc( ) ) ){
                $output[] = $row;
            }
        }
        return $output;
    }
    /**
     * 
     * @param mixed $filters
     * @return string
     */
    protected function where( $filters ){
        
        
        return '';
    }
    /**
     * UDPATE
     * @param string $resource
     * @param array $values
     * @param array $filters
     * @return int
     */
    public function update( $resource , array $values , array $filters = [ ] ){
        
        $data = [];
        
        foreach( $values as $column => $value ){
            if(is_array($value)){
                $data[] = sprintf("`%s`='%s'",$column, implode(';', $value));
            }
            elseif(is_string($value)){
                $data[] = sprintf("`%s`='%s'",$column, $value );
            }
            elseif(is_numeric($value)){
                $data[] = sprintf("`%s`=%s",$column, $value );
            }
        }
        
        $sql_update = sprintf( 'UPDATE `%s` SET %s' , $resource , implode(',', $data));
        
        if( count( $filters ) ){
            
            $sql_update .= ' WHERE ' . $this->where($filters);
        }
        
        $result = $this->query($sql_update);
        
        return $result !== FALSE ? $this->affected_rows : 0;
    }
    /**
     * INSERT
     * @param string $resource
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function insert( $resource , array $data ){
        
        $columns = $values = [];
        
        foreach( $data as $field => $value ){
            $columns[] = $field;
            if(is_array($value)){
                $values[] = sprintf("'%s'" , implode(';', $value));
            }
            elseif(is_string($value)){
                $values[] = sprintf("'%s'", $value );
            }
            elseif(is_numeric($value)){
                $values[] = $value;
            }
        }
        
        $sql_insert = sprintf('INSERT INTO `%s` (%s) VALUES (%s)',$resource,$columns,$values);
        
        $result = $this->query($sql_insert);

        return $result !== FALSE ? $this->insert_id : 0;
    }
    /**
     * DELETE
     * @param string $resource
     * @param array $filters
     * @return int
     * @throws Exception
     */
    public function delete( $resource , array $filters = [ ] ){
        
        $sql_delete = sprintf( 'DELETE FROM `%s`' , $resource );
        
        if( count($filters) ){
            $sql_delete .= ' WHERE ' . $this->where($filters);
        }
        
        $result = $this->query($sql_delete);
        
        return $result !== FALSE ? $this->affected_rows : 0;
    }
    /**
     * @param string $resource
     * @param array $columns
     * @param array $filters
     * @param array $order
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function select( $resource , array $columns = NULL , array $filters = [] , array $order = [], $limit = 0 , $offset = 0 ){
        
        $sql_select = sprintf('SELECT %s FROM `%s`',
                !is_null($columns) ? implode(',', $columns) : '*' ,
                $resource );
        
        if( count( $filters )){
            $sql_select .= ' WHERE '. $this->where($filters);
        }
        
        if( count( $order )){
            $orderBy = [];
            foreach( $order as $column => $odr ){
                $orderBy[] = sprintf('%s %s',$column, strtoupper($odr));
            }
            $sql_select .= ' ORDER BY ' . implode(',', $orderBy);
        }
        
        if( $limit ){
            
            if( $offset ){
                
            }
        }
        
        $result = $this->query($sql_select);
        
        return $result !== FALSE ? $this->fetch($result) : [ ];
    }
    /**
     * @return \Poly\Core\DataBase
     */
    public static final function instance(){
        
        if(is_null(self::$_db)){

            self::$_db = new DataBase(
                \Polymorphic::instance()->get('db-host'),
                \Polymorphic::instance()->get('db-user'),
                \Polymorphic::instance()->get('db-pass'),
                \Polymorphic::instance()->get('db-name'));
        }
        
        return  self::$_db;
    }
}


/*class DataBaseResult extends \mysqli_result{
    
}*/