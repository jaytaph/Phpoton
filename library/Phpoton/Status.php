<?php

class Phpoton_Status {

    /**
     * @static
     * @return Model_Status_Entity
     */
    static function loadStatus() {
        $mapper = new Model_Status_Mapper();
        $status = $mapper->findByPk(1);
        return $status;
    }

    /**
     * @static
     * @param Model_Status_Entity $status
     * @return void
     */
    static function saveStatus(Model_Status_Entity $status) {
        $mapper = new Model_Status_Mapper();
        $mapper->save($status);
    }
}
