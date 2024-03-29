<?php

class Model_Tweep_Mapper extends Model_Mapper {
    protected $_tableName = 'tweeps';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        /**
         * @var $obj Model_Tweep_Entity
         */
        $data = array();
        $data['id'] = $obj->getId();
        $data['screen_name'] = $obj->getScreenName();
        return $data;
    }

    protected function _fromArray(array $data) {
        $obj = new Model_Tweep_Entity();
        $obj->setId($data['id']);
        $obj->setScreenName($data['screen_name']);
        return $obj;
    }
    
}