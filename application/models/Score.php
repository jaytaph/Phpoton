<?php

class Model_Score {
    protected $_rank;
    protected $_twitter_id;
    protected $_points;

    /**
     * @return Model_Tweep_Entity
     */
    public function getTwitterInfo() {
        $mapper = new Model_Tweep_Mapper();
        $entity = $mapper->findByPk($this->getTwitterId());
        return $entity;
    }

    public function setPoints($points)
    {
        $this->_points = $points;
    }

    public function getPoints()
    {
        return $this->_points;
    }

    public function setRank($rank)
    {
        $this->_rank = $rank;
    }

    public function getRank()
    {
        return $this->_rank;
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
