<?php

class Model_Answer_Entity extends Model_Entity {
    protected $_id;
    protected $_twitter_id;
    protected $_status_id;
    protected $_answer;
    protected $_question_id;
    protected $_receive_dt;


    public function setAnswer($answer)
    {
        $this->_answer = $answer;
    }

    public function getAnswer()
    {
        return $this->_answer;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setQuestionId($question_id)
    {
        $this->_question_id = $question_id;
    }

    public function getQuestionId()
    {
        return $this->_question_id;
    }

    public function setReceiveDt($receive_dt)
    {
        $this->_receive_dt = $receive_dt;
    }

    public function getReceiveDt()
    {
        return $this->_receive_dt;
    }

    public function setTwitterId($twitter_id)
    {
        $this->_twitter_id = $twitter_id;
    }

    public function getTwitterId()
    {
        return $this->_twitter_id;
    }

    public function setStatusId($status_id)
    {
        $this->_status_id = $status_id;
    }

    public function getStatusId()
    {
        return $this->_status_id;
    }

}