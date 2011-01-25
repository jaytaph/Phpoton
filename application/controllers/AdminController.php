<?php

class AdminController extends Zend_Controller_Action
{
    protected $_publicActions = array("login");

    public function preDispatch() {
        // Return when we are requesting a public action (without login)
        if (in_array($this->getRequest()->getActionName(), $this->_publicActions)) {
            return;
        }

        // Check login, if not available, throw exception
        $auth = Zend_Auth::getInstance();
        if (! $auth->hasIdentity()) {
            $this->_helper->redirector('login');
        }
    }


    public function init() {
        // Add [twitter:] parser
        $this->view->addFilter('TwitterLink');

        // Set html title
        $this->_helper->layout()->getView()->headTitle('@PHPoton - Administration Panel');

        // Set navigation
        $container = Zend_Registry::get('navigation');
        $this->view->navigation(new Zend_Navigation($container));
    }

    /**
     * Global admin index
     * 
     * @return void
     */
    public function indexAction() {
        
    }

    /**
     * Logs the current user out of the admin backend
     *
     * @return void
     */
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index');
    }


    /**
     * Public action. Can be called without being logged in (obviously)
     * @return void
     */
    public function loginAction() {
        // Check if we are already logged in
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->_helper->redirector('index');
        }

        $form = new Form_Login();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                // Process form data
                if ($this->_process($data)) {
                    // All ok
                    $this->_helper->redirector('index');
                }
            }
            $form->populate($data);
        }

        $this->view->form = $form;
    }


    /**
     * Creates and returns authentication adapter
     * 
     * @return Zend_Auth_Adapter_DbTable
     */
    protected function _getAuthAdapter() {
        $authAdapter = new Zend_Auth_Adapter_DbTable(null, 'auth', 'username', 'password');
        // We use SHA1 for password hashing (no salt)
        $authAdapter->setCredentialTreatment('SHA1(?)');
        return $authAdapter;
    }


    protected function _process($data) {
        // Store user/pass into the adapter
        $adapter = $this->_getAuthAdapter();
        $adapter->setIdentity($data['username'])->setCredential($data['password']);

        // Authenticate through the adapter
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            // Valid result, store authentication information (user-record)
            $user = $adapter->getResultRowObject();
            $auth->getStorage()->write($user);
            return true;
        }
        return false;
    }

}