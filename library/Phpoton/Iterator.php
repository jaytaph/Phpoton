<?php

/**
 * Iterator that converts a rowset from zend_db_table into our own iterator of
 * an entity
 */
class Phpoton_Iterator extends Zend_Db_Table_Rowset_Abstract {
    protected $_mapper;
    protected $_rowset;

    function __construct(Model_Mapper $mapper, Zend_Db_Table_Rowset_Abstract $rowset) {
        $this->_mapper = $mapper;
        $this->_rowset = $rowset;
    }

    public function offsetUnset($offset)
    {
        return $this->_rowset->offsetUnset($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->_rowset->offsetSet($offset, $value);
    }

    public function offsetGet($offset)
    {
        return $this->_rowset->offsetGet($offset);
    }

    public function offsetExists($offset)
    {
        return $this->_rowset->offsetExists($offset);
    }

    public function count()
    {
        return $this->_rowset->count();
    }

    public function rewind()
    {
        return $this->_rowset->rewind();
    }

    public function valid()
    {
        return $this->_rowset->valid();
    }

    public function key()
    {
        return $this->_rowset->key();
    }

    public function next()
    {
        return $this->_rowset->next();
    }

    public function seek($position)
    {
        return $this->_rowset->seek($position);
    }

    // We have to convert the record to an object
    function current() {
        $record = $this->_rowset->current();
        return $this->_mapper->fromArray($record->toArray());
    }

}