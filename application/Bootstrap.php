<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    function __construct($application) {
        parent::__construct($application);

        // Set timezone
        date_default_timezone_set("Europe/Amsterdam");

        // Load config into register
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('config', $config);

        // Initialize and save twitter object
        $accessToken = new Zend_Oauth_Token_Access();
        $accessToken->setToken($config->settings->twitter->accessToken)
                    ->setTokenSecret($config->settings->twitter->accessTokenSecret);

        $data = array('username' => $config->settings->twitter->screenName,
                      'accessToken' => $accessToken);
        $twitter = new Zend_Service_Twitter($data);

        Zend_Registry::set('twitter', $twitter);
    }
}

