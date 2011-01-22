<?php

class Model_Answer_Mapper extends Model_Mapper {
    protected $_tableName = 'answers';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        $data = array();
        $data['id'] = $obj->getId();
        $data['twitter_id'] = $obj->getTwitterId();
        $data['answer'] = $obj->getAnswer();
        $data['question_id'] = $obj->getQuestionId();
        $data['receive_dt'] = $obj->getRecieveDt();
        return $data;
    }

    protected function _fromArray(array $data) {
        $obj = new Model_Answer_Entity();
        $obj->setId($data['id']);
        $obj->setTwitterId($data['twitter_id']);
        $obj->setAnswer($data['answer']);
        $obj->setQuestionId($data['question_id']);
        $obj->setReceiveDt($data['receive_dt']);
        return $obj;
    }
    
}