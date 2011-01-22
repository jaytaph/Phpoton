<?php

class Model_Status_Mapper extends Model_Mapper {
    protected $_tableName = 'status';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        $data = array();
        $data['id'] = $obj->getId();
        $data['since_id'] = $obj->getSinceId();
        return $data;
    }

    protected function _fromArray(array $data) {
        $obj = new Model_Status_Entity();
        $obj->setId($data['id']);
        $obj->setSinceId($data['since_id']);
        return $obj;
    }
    
}