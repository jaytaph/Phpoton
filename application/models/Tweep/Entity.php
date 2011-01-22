<?php

class Model_Tweep_Entity extends Model_Entity {
    protected $_twitter_id;
    protected $_screen_name;

    public function setScreenName($screen_name)
    {
        $this->_screen_name = $screen_name;
    }

    public function getScreenName()
    {
        return $this->_screen_name;
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
