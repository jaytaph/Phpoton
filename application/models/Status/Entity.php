<?php

class Model_Status_Entity extends Model_Entity {
    protected $_id;
    protected $_since_id;
    protected $_sleeptime;

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setSinceId($since_id)
    {
        $this->_since_id = $since_id;
    }

    public function getSinceId()
    {
        return $this->_since_id;
    }

    public function setSleeptime($sleeptime)
    {
        $this->_sleeptime = $sleeptime;
    }

    public function getSleeptime()
    {
        return $this->_sleeptime;
    }
}
