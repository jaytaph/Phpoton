<?php

class Phpoton_Tweep {

    /**
     * @static
     * @return Model_Tweep_Entity
     */
    static function getTweep($twitter_id) {
        $mapper = new Model_Tweep_Mapper();
        $tweep = $mapper->findByPk($twitter_id);
        if ($tweep == null) {
            // Not found in DB cache, load from twitter
            $tweep = self::cacheTweep($twitter_id);
        }
        return $tweep;
    }


    /**
     * Retrieves tweep info from twitter and stores into DB
     *
     * @param  $twitter_id
     * @return Model_Tweep_entity
     */
    static function cacheTweep($twitter_id) {
        $twitter = Zend_Registry::get('twitter');
        $result = $twitter->user->show($twitter_id);
        
        $tweep = new Model_Tweep_Entity();
        $tweep->setId($result->id);
        $tweep->setScreenName($result->screen_name);

        $mapper = new Model_Tweep_Mapper();
        $mapper->save($tweep);

        return $tweep;
    }
}
