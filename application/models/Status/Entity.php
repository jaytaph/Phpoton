<?php

class Model_Status_Entity extends Model_Entity {
    protected $_id;
    protected $_since_id;
    protected $_question_id;

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setSinceId($since_id)
    {
        $this->_since_id = $since_id;
    }

    public function getSinceId()
    {
        return $this->_since_id;
    }

    public function setQuestionId($question_id)
    {
        $this->_question_id = $question_id;
    }

    public function getQuestionId()
    {
        return $this->_question_id;
    }
}
