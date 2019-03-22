<?php namespace Poly\Core;

defined('APP_ROOT') or die;

use Poly\Core\Dictionary;

/**
 * @author Jaume Llopis modelo abstracto para describir las entidades lógicas de la aplicación
 */
class Collection extends Dictionary {
    
    const CLAUSE_SELECT = 'SELECT';
    const CLAUSE_WHERE = 'WHERE';
    const CLAUSE_LIMIT = 'LIMIT';
    const CLAUSE_ORDER = 'ORDER';
    
    /**
     * @var array
     */
    private $_settings = [];
    /**
     * Query para ejecutar la captura de registros del modelo
     * @var array
     */
    private $_query = [];

    /**
     * @var \Poly\Core\Model[]
     */
    private $_collection = [];
    
    /**
     * Crea una colección de modelos del tipo facilitado
     * @param \Poly\Core\Model $model
     */
    private function __construct(Model $model) {

        $this->registerModel($model);
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->_settings['class'];
    }
    /**
     * @param \Poly\Core\Model $model
     * @return \Poly\Core\Collection
     */
    private final function registerModel(Model $model) {
        //importa los datos de tabla y clase
        $this->_settings['class'] = get_class($model);
        $this->_settings['table'] = $model->getTable();
        $this->_settings['index'] = $model->getIndex();
        $this->_settings['identifier'] = $model->getIdentifier();
        //importa la definición de atributos
        foreach ($model->listFields() as $field) {

            $this->addField($field, $model->getType($field), $model->getDefinition($field));
        }

        return $this;
    }
    /**
     * Genera la lista de modelos a exportar como colección
     * @param array $dataSet
     * @return \Poly\Core\Model[]
     */
    private final function fill( array $dataSet ){
        
        $this->_collection = [];
        
        $class = $this->_settings['class'];
        
        foreach( $dataSet as $data ){
        
            $this->_collection[ ] = new $class( $data );
        }
    
        return $this->_collection;
    }
    /**
     * @param string $clause
     * @return mixed
     */
    private final function getClause( $clause ){
        return array_key_exists($clause, $this->_query) ? $this->_query[ $clause ] : [];
    }
    /**
     * 
     * @param type $statement
     * @param type $query
     * @return \Poly\Core\Collection
     */
    private final function addClause( $statement , $query ){
        
        if( !array_key_exists($statement, $this->_query)){
            
            $this->_query[ $statement ] = [ $query ];
        }
        else{
            $this->_query[ $statement ][ ] = $query;
        }
        
        return $this;
    }
    /**
     * @param mixed $input
     * @return string
     */
    private final function parseType( $input ){
        
        if(is_object($input)){
            return "'".get_class( $input )."'";
        }
        elseif(is_array($input)){
            $list = [];
            foreach( $input as $val ){
                $list[] = is_string($val) ? "'".$val."'" : $val;
            }
            return implode(',', $list);
        }
        elseif(is_string($input)){
            return "'".$input."'";
        }
        return $input;
    }
    /**
     * @param array $columns
     * @return \Poly\Core\Collection
     */
    public function select( array $columns = [ ] ){
        
        foreach( $columns as $col ){
            $this->addClause(self::CLAUSE_SELECT, $col );
        }
        
        return $this;
    }
    /**
     * Filtros de la consulta
     * @param string $column
     * @param mixed $value
     * @param string $operator
     * @return \Poly\Core\Collection
     */
    public function where( $column , $value , $operator = '=' , $join = 'AND' ){
        
        switch( $operator ){
            case 'IN':
            case 'NOT IN':
                $clause = sprintf('`%s` %s (%s)',$column, strtoupper( $operator ),$this->parseType($value));
                break;
            default:
                $clause = sprintf('`%s`%s%s',$column,$operator,$this->parseType($value));
                break;
        }
        
        return array_key_exists(self::CLAUSE_WHERE, $this->_query) ?
                //agregar nodo a las sucesivas condiciones
                $this->addClause( self::CLAUSE_WHERE, $join . ' ' . $clause ) :
                //no agregar join a la primera condicion
                $this->addClause( self::CLAUSE_WHERE, $clause );
    }
    
    public function innerJoin( $table , $local , $foreign ){
        
        return $this;
    }
    
    public function leftJoin( $table , $local , $foreign ){
        
        return $this;
    }
    
    public function rightJoin( $table , $local , $foreign ){
        
        return $this;
    }
    /**
     * Orden de la query
     * @param string $field
     * @param string $order
     * @return \Poly\Core\Collection
     */
    public function order( $field , $order = 'ASC' ){
    
        return $this->addClause(self::CLAUSE_ORDER, sprintf('%s %s',$field,$order));
    }
    /**
     * Límite de la query
     * @param int $limit
     * @param int $offset
     * @return \Poly\Core\Collection
     */
    public function limit( $limit  = 0 , $offset = 0 ){
        
        return $this->addClause(self::CLAUSE_LIMIT, $offset > 0 ?
                sprintf('%s, %s',$offset,$limit) :
                $limit );
    }
    /**
     * Obtiene la selección de modelos de la query
     * @param boolean $refresh
     * @return \Poly\Core\Model[]
     */
    public function get( $refresh = FALSE ){
        
        if( count( $this->_collection ) && !$refresh ){
            //retornar la colección generada si ya se ha procesado
            return $this->_collection;
        }
        else{
            //reiniciar la selección
            $this->_collection = [];
        }
        
        $select = $this->getClause(self::CLAUSE_SELECT);
        $where = $this->getClause(self::CLAUSE_WHERE);
        $order = $this->getClause(self::CLAUSE_ORDER);
        $limit = $this->getClause(self::CLAUSE_LIMIT);
        
        $sql_query = count($select ) ?
                sprintf( 'SELECT `%s`' ,  implode('`,`' , $select) ) :
                'SELECT *'; 
        
        if( count( $where ) ){
            $sql_query .= ' WHERE ' . implode(' ', $where );
        }
        
        if( count( $order ) ){
            $sql_query .= ' ORDER BY' . implode(', ', $order);
        }
        
        if( count( $limit ) ){
            $sql_query .= ' LIMIT ' . implode('', $limit);
        }
        
        $db = DataBase::instance();
        
        try{
            $result = $db->query($sql_query);
            
            if( $result !== FALSE ){
                //exportar
                return $this->fill( $db->fetch( $result ) );
            }
        }
        catch (\Exception $ex) {

            die( $ex->getMessage());
        }
        
        return $this->_collection;
    }
    /**
     * @param \Poly\Core\Model $model
     * @return \Poly\Core\Collection
     * @throws Exception
     */
    public static final function createCollection(Model $model) {

        try{
            if(is_null($model)){
                throw new Exception('Required Model');
            }
        }
        catch (\Exception $ex) {
            die($ex->getMessage());
        }
        
        return new Collection( $model );
    }
}



