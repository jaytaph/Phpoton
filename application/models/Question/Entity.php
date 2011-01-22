<?php

class Model_Question_Entity extends Model_Entity {
    protected $_id;
    protected $_question;
    protected $_answer;
    protected $_fullname;
    protected $_twitter_id;
    protected $_create_dt;
    protected $_tweet_dt;
    protected $_moderated;

    public function getStatus() {
        return "answered";
    }

    public function getReplyCount() {
        // @TODO
        return rand(1, 100);
    }

    public function getCorrectReplyCount() {
        return rand(1, 100);
    }

    public function getWinner() {
        return "[twitter:JayTaph]  [twitter:JayTaph/123456]";
    }


    public function setAnswer($answer)
    {
        $this->_answer = $answer;
    }

    public function getAnswer()
    {
        return $this->_answer;
    }

    public function setCreateDt($create_dt)
    {
        $this->_create_dt = $create_dt;
    }

    public function getCreateDt()
    {
        return $this->_create_dt;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setQuestion($question)
    {
        $this->_question = $question;
    }

    public function getQuestion()
    {
        return $this->_question;
    }

    public function setTweetDt($tweet_dt)
    {
        $this->_tweet_dt = $tweet_dt;
    }

    public function getTweetDt()
    {
        return $this->_tweet_dt;
    }

    public function setModerated($moderated)
    {
        $this->_moderated = $moderated;
    }

    public function getModerated()
    {
        return $this->_moderated;
    }

    public function setTwitterId($twitter_id)
    {
        $this->_twitter_id = $twitter_id;
    }

    public function getTwitterId()
    {
        return $this->_twitter_id;
    }

    public function setFullname($fullname)
    {
        $this->_fullname = $fullname;
    }

    public function getFullname()
    {
        return $this->_fullname;
    }


}