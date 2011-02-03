<?php

class Model_Scoreboard_Mapper extends Model_Mapper {
    protected $_tableName = 'scoreboard';
    protected $_primaryKey = 'id';

    protected function _toArray(Model_Entity $obj) {
        /**
         * @var $obj Model_Scoreboard_Entity
         */
        $data = array();
        $data['id'] = $obj->getId();
        $data['twitter_id'] = $obj->getTwitterId();
        $data['score_points'] = $obj->getScorePoints();
        $data['score_time'] = $obj->getScoreTime();
        return $data;
    }

    protected function _fromArray(array $data) {
        $obj = new Model_Scoreboard_Entity();
        $obj->setId($data['id']);
        $obj->setTwitterId($data['twitter_id']);
        $obj->setScorePoints($data['score_points']);
        $obj->setScoreTime($data['score_time']);
        return $obj;
    }


    /**
     * Return array of Model_Score with top X users
     *
     * @todo Model_Score should be iterator instead of returning an array
     *
     * @param int $count
     * @return array Array of Model_Score
     */
    function getTopScore($count = 20) {
        $select = $this->_table->select()
                ->order('score_points DESC')
                ->limit($count);

        $oldscore = -1;
        $rank = 1;
        $internal_rank = 0;
        foreach ($this->_table->fetchAll($select) as $record) {
            $internal_rank++;
            if ($oldscore != $record['score_points']) $rank = $internal_rank;

            $score = new Model_Score();
            $score->setTwitterId($record['twitter_id']);
            $score->setPoints($record['score_points']);
            $score->setRank($rank);
            $scoreboard[] = $score;

            $oldscore = $record['score_points'];
        }

        return $scoreboard;
    }


    /**
     * Increases the points for specified user, or inserts the new user
     *
     * @param $user string Username to increase
     * @param int $time Additional time. Not used
     *
     * @return void
     */
    function increaseScore(Model_Tweep_Entity $twitter, $time = 0) {
        // @TODO: Must lock this record

        $select = $this->_table->select()->where('twitter_id LIKE ?', $twitter->getId());
        $row = $this->_table->fetchRow($select);

        if ($row == null) {
            // Does not exists, insert new user
            $data = array('twitter_id' => $twitter->getId(), 'score_points' => 1);
            $this->_table->insert($data);
        } else {
            $data = array(
                'score_points' => ($row['score_points'] + 1),
                'score_time' => ($row['score_time'] + 1));
            $where = $this->_table->getAdapter()->quoteInto('twitter_id ?', $twitter->getId());
            $this->_table->update($data, $where);
        }
    }

}