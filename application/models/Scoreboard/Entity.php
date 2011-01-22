<?php

class Model_Scoreboard_Entity extends Model_Entity {
    protected $_id;
    protected $_twitter_id;
    protected $_score_points;
    protected $_score_time;

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setScorePoints($score_points)
    {
        $this->_score_points = $score_points;
    }

    public function getScorePoints()
    {
        return $this->_score_points;
    }

    public function setScoreTime($score_time)
    {
        $this->_score_time = $score_time;
    }

    public function getScoreTime()
    {
        return $this->_score_time;
    }

    public function setTwitterId($twitter_id)
    {
        $this->_twitter_id = $twitter_id;
    }

    public function getTwitterId()
    {
        return $this->_twitter_id;
    }
}