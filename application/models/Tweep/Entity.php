<?php

class Model_Tweep_Entity extends Model_Entity {
    protected $_id;
    protected $_screen_name;


    public function setScreenName($screen_name)
    {
        $this->_screen_name = $screen_name;
    }

    public function getScreenName()
    {
        return $this->_screen_name;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

}
