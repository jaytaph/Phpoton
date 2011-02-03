<?php

class Model_Question_Mapper extends Model_Mapper {
    protected $_tableName = 'questions';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        /**
         * @var $obj Model_Question_Entity
         */
        $data = array();
        $data['id'] = $obj->getId();
        $data['question'] = $obj->getQuestion();
        $data['answer'] = $obj->getAnswer();
        $data['fullname'] = $obj->getFullname();
        $data['twitter_id'] = $obj->getTwitterId();
        $data['create_dt'] = $obj->getCreateDt();
        $data['tweet_dt'] = $obj->getTweetDt();
        $data['status'] = $obj->getStatus();
        $data['winning_answer_id'] = $obj->getWinningAnswerId();
        $data['timelimit'] = $obj->getTimeLimit();
        return $data;
    }

    protected function _fromArray(array $data) {
        $obj = new Model_Question_Entity();
        $obj->setId($data['id']);
        $obj->setQuestion($data['question']);
        $obj->setAnswer($data['answer']);
        $obj->setFullname($data['fullname']);
        $obj->setTwitterId($data['twitter_id']);
        $obj->setCreateDt($data['create_dt']);
        $obj->setTweetDt($data['tweet_dt']);
        $obj->setStatus($data['status']);
        $obj->setWinningAnswerId($data['winning_answer_id']);
        $obj->setTimeLimit($data['timelimit']);
        return $obj;
    }

    public function getActiveQuestion() {
        $select = $this->_table->select()
                ->where('status = ?', 'active')
                ->limit(1);
        $row = $this->_table->fetchRow($select);
        if ($row == null) {
            return null;
        }

        return $this->_fromArray($row->toArray());
    }

    public function getNextPendingQuestion() {
        $select = $this->_table->select()
                ->where('tweet_dt IS NULL')
                ->where('status = ?', 'pending')
                ->limit(1);
        $row = $this->_table->fetchRow($select);
        if ($row == null) {
            return null;
        }

        return $this->_fromArray($row->toArray());
    }

}