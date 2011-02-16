<?php

/**
 * Returns score
 */
class Phpoton_Iterator_Score extends Phpoton_Iterator {
    protected $_position = 1;

    function rewind() {
        $this->_position = 1;
    }

    public function seek($position)
    {
        $this->_position = $position + 1;
    }

    function next() {
        $this->_position++;
        parent::next();
    }

    function current() {
        $record = $this->_rowset->current();

        $score = new Model_Score();
        $score->setTwitterId($record['twitter_id']);
        $score->setPoints($record['score_points']);
        $score->setRank($record['rank']);
        return $score;
    }

}