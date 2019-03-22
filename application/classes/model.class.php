<?php namespace Poly\Core;

defined('APP_ROOT') or die;

/**
 * @author Jaume Llopis modelo abstracto para describir las entidades lógicas de la aplicación
 */

abstract class Model extends Dictionary{

    /**
     * Propiedades del modelo
     * @var array
     */
    private $_settings = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = []) {

        //definición de tipos en las subclasses

        if (count($data)) {
            $this->importValues($data);
        }
    }

    /**
     * @return string
     */
    protected static function __getClass() {

        return get_called_class();

        $called = new \ReflectionClass(get_called_class());

        $full_path = strtolower($called->getFileName());

        $file = explode('/', preg_replace('/\\\\/', '/', $full_path));

        $output = $file[count($file) - 1];

        return substr($output, 0, strrpos($output, '.'));
    }

    /**
     * @return string
     */
    public function __toString() {

        return $this->__getClass();
    }

    /**
     * @param string $setting
     * @param mixed $default
     * @return array
     */
    protected final function __getSetting($setting, $default = NULL) {
        return isset($this->_settings[$setting]) ?
                $this->_settings[$setting] :
                $default;
    }

    /**
     * @param string $setting
     * @param mixed $value
     * @return \Poly\Core\Model
     */
    protected final function __setSetting($setting, $value) {
        $this->_settings[$setting] = $value;
        return $this;
    }

    /**
     * Sobrecarga el magicmethod para obtener acceso a los settings
     * @param string $name
     * @return mixed
     */
    public function __get($name) {

        if (strlen($name) > 8 && strrpos($name, '_setting') !== FALSE) {
            //extraer el nombre de la propiedad o setting
            $setting = substr($name, 0, strlen($name) - 8);

            return $this->__getSetting($setting);
        }

        parent::__get($name);
    }
    /**
     * Importa valores en el modelo con opción de marcarlos como actualizados
     * @param array $values
     * @param boolean $update
     * @return \Poly\Core\Model
     */
    public function importValues(array $values , $update = FALSE ) {
        foreach( $this->listFields() as $field ){
            if(array_key_exists( $field , $values ) ){
                $this->setValue($field,$values[$field], $update);
            }
        }
        return $this;
    }
    /**
     * @param string $name
     * @param mixed $value
     * @param boolean $update
     * @return \Poly\Core\Model
     */
    public function setValue($name, $value , $update = FALSE ) {
        
        parent::setValue($name, $value);
        
        if( $update ){
            $this->__setAttribute($name,'updated', TRUE);
        }
        
        return $this;
    }
    /**
     * @param string $field
     * @return boolean
     */
    public function isUpdated( $field ){
        return $this->__getAttribute( $field, 'updated' , FALSE );
    }
    /**
     * Lista los valores acutalizados en el modelo
     * @param boolean $associative
     * @return array
     */
    protected function listUpdated( $associative = FALSE ){
        $output = [];
        foreach( $this->listFields() as $field ){
            if( $this->isUpdated($field)){
                if( $associative ){
                    $output[ $field ] = $this->getValue($field);
                }
                else{
                    $output[] = $this->getValue($field);
                }
            }
        }
        return $output;
    }
    
    /**
     * @param string $table Nombre de la tabla de la BD
     * @param string $index Indice de la tabla
     * @param string $identifier Identificador o cabecera del modelo, habitualmente, título o nombre
     * @return \Poly\Core\Model
     */
    protected final function defineDataSource($table = null, $index = 'id', $identifier = null) {
        //tabla de la BD, por defecto, el mismo nombre de la clase
        $this->_settings['table'] = !is_null($table) ? $table : strval($this);
        //indice para referirse a la tabla de la BD
        $this->_settings['index'] = $index;
        //identificador del modelo o cabecera, usualmente un nombre o título, por defecto ,el ID del registro
        $this->_settings['identifier'] = !is_null($identifier) ? $identifier : $index;

        return $this;
    }

    /**
     * Crea los timestamps
     * @return \Poly\Core\Model
     */
    protected final function defineTimestamps() {

        return $this->addField('date_created', parent::TYPE_DATETIME)
                        ->addField('date_modified', parent::TYPE_DATETIME);
    }
    /**
     * @return boolean
     */
    protected final function hasTimeStamps(){
        
        return $this->hasField('date_modified') && $this->hasField('date_created');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue($name) {

        switch ($this->getType($name)) {
            case parent::TYPE_RELATED:
                return $this->getRelated($name, []);
            default:
                parent::getValue($name);
        }
    }

    /**
     * @return string
     */
    public function getIndex() {
        return $this->_settings['index'];
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->_settings['table'];
    }

    /**
     * @return string
     */
    public function getIdentifier() {
        return $this->_settings['identifier'];
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return \Poly\Core\Model[]
     */
    public function getRelated($name) {
        //no es estrictamente necesario casi todos los sistemas, pero por si acaso
        //forzar la convención CamelCase
        $cap = strtoupper(substr($name, 0, 1));

        $low = strtolower(substr($name, 1, strlen($name) - 1));

        $related = sprintf('get%sRelated', $cap . $low);

        return method_exists($this, $related) ? $this->$related() : [];
    }

    /**
     * Guarda|actualiza un modelo en la bd, en función de si ya existe (tiene un ID) o debe ser creado (sin ID)
     * @return boolean
     */
    public function save() {

        $index = $this->getIndex();

        return !is_null($this->getValue($index)) ?
                $this->update() :
                $this->save();
    }
    /**
     * Actualiza un registro por id
     * @return boolean
     */
    public function update(  ) {
        
        if( $this->hasTimeStamps() ){
            $this->setValue('date_modified', date('Y-m-d H:i:s'),TRUE);
        }
        
        
        //listame los valores acutalizados
        $values = $this->listUpdated(TRUE);

        if( count( $values ) ){
            
            $db = DataBase::instance();

            $updated = $db->update(
                    $this->getTable(),
                    $values,
                    [ $this->getIndex() => $this->getValue($this->getIndex())]);

            return $updated > 0;
        }
        
        return 0;
    }
    /**
     * @return boolean
     */
    public function create() {

        if( $this->hasTimeStamps() ){
            $ts = date('Y-m-d H:i:s');
            $this->setValue('date_created', $ts , TRUE )
                ->setValue('date_updated', $ts , TRUE );
        }
        
        $db = DataBase::instance();
        
        $id = $db->insert( $this->getTable(), $this->listUpdated( TRUE ) );
        
        $this->setValue($this->getIndex(), $id);
        
        //crealo si no tiene ID
        return $id > 0;
    }
    /**
     * @return boolean
     */
    public function remove() {

        $db = DataBase::instance();
        
        return $db->delete($this->getTable(),[$this->getIndex()=>$this->getValue($this->getIndex())]) > 0;
    }

    public function duplicate() {


        //retorna un duplicado del mismo modelo
    }
    /**
     * @param array $columns
     * @return \Poly\Core\Collection
     */
    public static function select(array $columns = null) {

        $class = self::__getClass();
        
        //retorna una colección basada en este modelo
        return Collection::createCollection( class_exists($class) ? new $class( ) : null )->select( $columns );
    }
}



