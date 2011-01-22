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

        // Initialize DB connection
        $db = Zend_Db::factory($config->resources->db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    protected function _initLoaderResource()
    {
        // @TODO: add to application.ini
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
                'basePath'  => APPLICATION_PATH,
                'namespace' => ''
        ));
        $resourceLoader->addResourceTypes(array(
                'model' => array(
                        'namespace' => 'Model',
                        'path'      => 'models'
                )
        ));
        $resourceLoader->addResourceTypes(array(
                'forms' => array(
                        'namespace' => 'Form',
                        'path'      => 'forms'
                )
        ));
        $resourceLoader->addResourceTypes(array(
                'phpoton' => array(
                        'namespace' => 'Phpoton',
                        'path'      => '../library/Phpoton'
                )
        ));
    }

    protected function _initRouters()
    {
        // @TODO: add to application.ini
        $router = new Zend_Controller_Router_Rewrite();

        $router->addRoute('question',
            new Zend_Controller_Router_Route('question/:id', array('controller' => 'index', 'action' => 'question'))
        );

        $controller = Zend_Controller_Front::getInstance();
        $controller->setRouter($router);
    }
}

