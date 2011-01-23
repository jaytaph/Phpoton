<?php

class Model_Question_Entity extends Model_Entity {
    protected $_id;
    protected $_question;
    protected $_answer;
    protected $_fullname;
    protected $_twitter_id;
    protected $_create_dt;
    protected $_tweet_dt;
    protected $_status;
    protected $_winning_answer_id;

    protected $_winning_tweep = null;      // Lazy loading tweep info

    public function getReplyCount() {
        // @TODO
        return rand(1, 100);
    }

    public function getCorrectReplyCount() {
        // @TODO: Change into correct reply count
        return rand(1, 100);
    }

    /**
     * @return Model_Tweep_Entity
     */
    public function getWinnerTweep() {
        /**
         * @var $answer Model_Answer_Entity
         */
        if ($this->_winning_tweep == null) {
            $answer_id = $this->getWinningAnswerId();
            if ($answer_id == null) {
                return null;
            }

            $mapper = new Model_Answer_Mapper();
            $answer = $mapper->findByPk($answer_id);
            if ($answer == null) {
                return null;
            }

            $twitter_id = $answer->getTwitterId();
            $this->_winning_tweep = Phpoton_Tweep::getTweep($twitter_id);
        }
        
        return $this->_winning_tweep;
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

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function getStatus()
    {
        return $this->_status;
    }



    public function isCorrectAnswer(Model_Answer_Entity $answer) {
        $text = $this->getAnswer();
        $text = strtolower($text);
        $text = trim($text);

        return ($answer->getCleanAnswer() == $text);
    }

    public function setWinningAnswerId($winning_answer_id)
    {
        $this->_winning_answer_id = $winning_answer_id;
    }

    public function getWinningAnswerId()
    {
        return $this->_winning_answer_id;
    }

}