<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    protected $_config;

    function __construct($application) {
        parent::__construct($application);

        // Set timezone
        date_default_timezone_set("Europe/Amsterdam");

        // Load config into register
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('config', $this->_config);
    }


    /**
     * Initializes zend layout
     */
    protected function _initLayout() {
        Zend_Layout::startMvc(array('layoutPath' => APPLICATION_PATH.'/views/layouts'));
    }

    /**
     * Initializes twitter object
     */
    protected function _initTwitter() {
        // Initialize and save twitter object
        $accessToken = new Zend_Oauth_Token_Access();
        $accessToken->setToken($this->_config->settings->twitter->accessToken)
                    ->setTokenSecret($this->_config->settings->twitter->accessTokenSecret);

        $data = array('username' => $this->_config->settings->twitter->screenName,
                      'accessToken' => $accessToken);
        $twitter = new Zend_Service_Twitter($data);

        Zend_Registry::set('twitter', $twitter);
    }

    /**
     * Initializes default database
     */
    protected function _initDatabase() {
        // Initialize DB connection
        $db = Zend_Db::factory($this->_config->resources->db);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    /**
     * Initializes resource loaders
     */
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

    /**
     * Initializes routers
     */
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


    protected function _initNavigation()
    {
        $container = array();
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'index', 'label' => 'Home'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'faq', 'label' => 'F.A.Q.'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'tweets', 'label' => 'Tweets'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'questions', 'label' => 'Questions'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'stats', 'label' => 'Statistics'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'admin', 'action' => 'index', 'label' => 'Admin'));

        Zend_Registry::set('navigation', $container);
    }
}

