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

    /**
     * Fetch all records
     *
     * @TODO: Create an iterator from this data
     *
     * @return array Model_Entity $object
     */
    function fetchAll() {
        $select = $this->_table->select();

        $iterator = new Phpoton_Iterator($this, $this->_table->fetchAll($select));
        return $iterator;

//        $ret = array();
//        foreach ($this->_table->fetchAll($select) as $record) {
//            $ret[] = $this->_fromArray($record->toArray());
//        }
//        return $ret;
    }

    function save(Model_Entity $obj) {
        $data = $this->_toArray($obj);

        if ($obj->getId() == 0) {
            $row = null;
        } else {
            // Check if record exists on PK.
            $select = $this->_table->select()->where($this->_primaryKey.' = ?' , $obj->getId());
            $row = $this->_table->fetchRow($select);
        }

        if ($row == null) {
            // INSERT, record does not exists
            $this->_table->insert($data);
        } else {
            // UPDATE, record does exists
            $where = $this->_table->getAdapter()->quoteInto($this->_primaryKey.' = ?', $obj->getId());
            $this->_table->update($data, $where);
        }
    }

    public function fromArray(array $data) {
        return $this->_fromArray($data);
    }

    abstract protected function _toArray(Model_Entity $obj);
    abstract protected function _fromArray(array $data);
}