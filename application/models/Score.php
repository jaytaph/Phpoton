<?php

// @TODO: We could create an iterator for this. We just need to load all records into the iterator (fetchAll())?
class Model_Score {
    protected $_rank;
    protected $_name;
    protected $_points;
    
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
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
}
