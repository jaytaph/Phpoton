<?php

/**
 * Returns score
 */
class Phpoton_Iterator_Score extends Phpoton_Iterator {
    protected $_oldscore = -1;
    protected $_rank = 1;
    protected $_internal_rank = 0;

    protected $_position = 1;

    function rewind() {
        $this->_oldscore = -1;
        $this->_rank = 1;
        $this->_internal_rank = 0;

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

        $this->_internal_rank++;
        if ($this->_oldscore != $record['score_points']) $this->_rank = $this->_internal_rank;

        $score = new Model_Score();
        $score->setTwitterId($record['twitter_id']);
        $score->setPoints($record['score_points']);
        //$score->setRank($this->_rank);
        $score->setRank($this->_position);
        return $score;

//        $oldscore = -1;
//        $rank = 1;
//        $internal_rank = 0;
//        foreach ($this->_table->fetchAll($select) as $record) {
//            $internal_rank++;
//            if ($oldscore != $record['score_points']) $rank = $internal_rank;
//
//            $score = new Model_Score();
//            $score->setTwitterId($record['twitter_id']);
//            $score->setPoints($record['score_points']);
//            $score->setRank($rank);
//            $scoreboard[] = $score;
//
//            $oldscore = $record['score_points'];
//        }
//
//        return $scoreboard;
    }

}