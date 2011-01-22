<?php

class Model_Question_Mapper extends Model_Mapper {
    protected $_tableName = 'questions';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        $data = array();
        $data['id'] = $obj->getId();
        $data['question'] = $obj->getQuestion();
        $data['answer'] = $obj->getAnswer();
        $data['fullname'] = $obj->getFullname();
        $data['twitter_id'] = $obj->getTwitterId();
        $data['create_dt'] = $obj->getCreateDt();
        $data['tweet_dt'] = $obj->getTweetDt();
        $data['moderated'] = $obj->getModerated();
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
        $obj->setModerated($data['moderated']);
        return $obj;
    }


    public function getPendingQuestion() {
        $select = $this->_table->select()
                ->where('tweet_dt IS NULL')
                ->where('moderated = 1')
                ->limit(1);
        $row = $this->_table->fetchRow($select);
        if ($row == null) {
            return null;
        }

        return $this->_fromArray($row->toArray());
    }

}