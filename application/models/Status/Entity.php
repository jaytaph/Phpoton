<?php

class Model_Tweep_Entity extends Model_Entity {
    protected $_id;
    protected $_since_id;

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
}
