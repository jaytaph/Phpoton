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
     * @param int $count number of items we want to return
     * @return Photon_Iterator_Score
     */
    function getTopScore($count = 20) {
        // Initialize values
        $this->_table->getAdapter()->query("SET @rownum = 0, @rank = 0, @prev_val = NULL");

        /*
         * We must use this (complex) query to calculate the ranking properly. We do this from
         * the database so the iterator can easily seek. This is needed since we want to apply
         * limititerators to our iterator so we can split score (like in 2 different columns for
         * example) It would be difficult to do this inside the iterator itself.
         *
         * Query is taken from MySQL cookbook 
         */
        $select = $this->_table->select()
            ->from($this->_tableName,
                array(
                    '*',
                    'row' => new Zend_Db_Expr('@rownum := @rownum + 1'),
                    'rank' => new Zend_Db_Expr('@rank := IF(@prev_val!=score_points,@rownum,@rank)'),
                    'score_points' => new Zend_Db_Expr('@prev_val := score_points'),
                    )
                )
            ->order('score_points DESC')
            ->limit($count);
        return new Phpoton_Iterator_Score($this, $this->_table->fetchAll($select));
    }


    /**
     * Increases the points for specified user, or inserts the new user
     *
     * @param $user string Username to increase
     * @param int $time Additional time. Not used
     *
     * @return void
     */
    function increaseScore(Model_Tweep_Entity $twitter, $score = 1, $time = 0) {
        // @TODO: Must lock this record before we increase points since it's not atomic

        $select = $this->_table->select()->where('twitter_id LIKE ?', $twitter->getId());
        $row = $this->_table->fetchRow($select);

        if ($row == null) {
            // Does not exists, insert new user
            $data = array('twitter_id' => $twitter->getId(), 'score_points' => $score, 'score_time' => 0);
            $this->_table->insert($data);
        } else {
            $data = array(
                'score_points' => ($row['score_points'] + $score),
                'score_time' => ($row['score_time'] + $time));
            $where = $this->_table->getAdapter()->quoteInto('twitter_id = ?', $twitter->getId());
            $this->_table->update($data, $where);
        }
    }

}