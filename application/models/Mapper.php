<?php

abstract class Model_Mapper {
    protected $_tableName = '';
    protected $_primaryKey = '';
    protected $_table;

    function __construct() {
        $this->_table = new Zend_Db_Table($this->_tableName);
    }

    /**
     * Find record by primary key or NULL
     * 
     * @param  $value string Value of the primary key
     * @return Model_Entity $object
     */
    function findByPk($value) {
        $select = $this->_table->select()->where($this->_primaryKey.' = ?' , $value);
        $row = $this->_table->fetchRow($select);
        if ($row == null) {
            return null;
        }

        return $this->_fromArray($row->toArray());
    }

    function save(Model_Entity $obj) {
        // @TODO: Check if record exists on PK.
        // If exists, update, otherwise insert
        $data = $this->_toArray($obj);
        $this->_table->insert($data);
    }

    abstract protected function _toArray(Model_Entity $obj);
    abstract protected function _fromArray(array $data);
}