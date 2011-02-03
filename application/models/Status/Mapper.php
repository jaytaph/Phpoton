<?php

class Model_Status_Mapper extends Model_Mapper {
    protected $_tableName = 'status';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        /**
         * @var $obj Model_Status_Entity
         */
        $data = array();
        $data['id'] = $obj->getId();
        $data['since_id'] = $obj->getSinceId();
        $data['sleeptime'] = $obj->getSleeptime();
        return $data;
    }

    protected function _fromArray(array $data) {
        $obj = new Model_Status_Entity();
        $obj->setId($data['id']);
        $obj->setSinceId($data['since_id']);
        $obj->setSleeptime($data['sleeptime']);
        return $obj;
    }
    
}