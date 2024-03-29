<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    /**
     * @var \Zend_Config_Ini
     */
    protected $_config;

    function __construct($application) {
        /**
         * @var $application Zend_Application
         */
        parent::__construct($application);

        // Set timezone
        date_default_timezone_set("Europe/Amsterdam");

        // Create new config
        // @TODO: Didn't we already do this inside the index.php (Zend_Application)???
        $this->_config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV, true);

        // Merge with user.ini settings if they exists
        if (file_exists(APPLICATION_PATH . '/configs/user.ini')) {
            $userConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/user.ini', APPLICATION_ENV, true);
            $this->_config = $this->_config->merge($userConfig);
        }

        // All done with the merging. Set to readonly
        $this->_config->setReadOnly();

        // Store in registry
        Zend_Registry::set('config', $this->_config);
    }


    /**
     * Initializes zend layout
     */
    protected function _initLayout() {
        Zend_Layout::startMvc(array('layoutPath' => APPLICATION_PATH.'/views/layouts'));
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
     * Initializes twitter object
     *
     * Note: this function MUST be after _initLoaderResource. This is because
     * that function will define the mapper space for phpoton_* classes and
     * _init* functions are loaded top down by Zend...
     */
    protected function _initTwitter() {
        if ($this->_config->settings->twitter->mock == 1) {
            // Mock twitter environment if needed
            $twitter = new Phpoton_Twitter();
        } else {
            // Initialize and save twitter object
            $accessToken = new Zend_Oauth_Token_Access();
            $accessToken->setToken($this->_config->settings->twitter->accessToken)
                        ->setTokenSecret($this->_config->settings->twitter->accessTokenSecret);

            $data = array('username' => $this->_config->settings->twitter->screenName,
                          'accessToken' => $accessToken);
            $twitter = new Zend_Service_Twitter($data);
        }

        Zend_Registry::set('twitter', $twitter);
    }

    protected function _initNavigation()
    {
        Zend_Registry::set('navigation', $this->_config->settings->navigation);
    }

}

