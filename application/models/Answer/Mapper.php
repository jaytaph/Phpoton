<?php

class Model_Answer_Mapper extends Model_Mapper {
    protected $_tableName = 'answers';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        /**
         * @var $obj Model_Answer_Entity
         */
        $data = array();
        $data['id'] = $obj->getId();
        $data['twitter_id'] = $obj->getTwitterId();
        $data['status_id'] = $obj->getStatusId();
        $data['answer'] = $obj->getAnswer();
        $data['question_id'] = $obj->getQuestionId();
        $data['receive_dt'] = $obj->getReceiveDt();
        return $data;
    }

    protected function _fromArray(array $data) {
        $obj = new Model_Answer_Entity();
        $obj->setId($data['id']);
        $obj->setTwitterId($data['twitter_id']);
        $obj->setStatusId($data['status_id']);
        $obj->setAnswer($data['answer']);
        $obj->setQuestionId($data['question_id']);
        $obj->setReceiveDt($data['receive_dt']);
        return $obj;
    }


    public function fetchSequentialAnswersForQuestion(Model_Question_Entity $question) {
        $select = $this->_table->select()
            ->where('question_id = ?', $question->getId())
            ->order('status_id ASC');

        $ret = array();
        foreach ($this->_table->fetchAll($select) as $record) {
            $ret[] = $this->_fromArray($record->toArray());
        }
        return $ret;
    }

}