<?php

/**
 * Returns score
 */
class Phpoton_Iterator_Score extends Phpoton_Iterator {

    function current() {
        $record = $this->_rowset->current();

        // @TODO: Fix the ranking
        $score = new Model_Score();
        $score->setTwitterId($record['twitter_id']);
        $score->setPoints($record['score_points']);
        $score->setRank(1);
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