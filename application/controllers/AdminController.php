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
        // Handle post request if needed
        if ($this->getRequest()->isPost()) {
            $this->_handlePost($_POST);
        }

        $mapper = new Model_Question_Mapper();

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Iterator($mapper->fetchAll()));
        $paginator->setDefaultScrollingStyle('Sliding');
        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->_getParam('page'));

        $this->view->questions = $paginator;
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


    public function setuptwitterAction() {
        $config = array(
            'callbackUrl' => 'http://'.$_SERVER['HTTP_HOST'].'/admin/setuptwitter',
            'siteUrl' => 'http://twitter.com/oauth',
            'consumerKey' => 'LnyRgd4Ya7tst2VNuuH7w',
            'consumerSecret' => 'w7xf0jdkfSGoWEzknfYV0zUSlGgglzCral1yx0Qojic');
        $consumer = new Zend_Oauth_Consumer($config);

        if (empty($_GET)) {
            // No request yet, initiate oauth to redirect to twitter
            $token = $consumer->getRequestToken();
            $_SESSION['TWITTER_REQUEST_TOKEN'] = serialize($token);
            $consumer->redirect();
        } else {
            // Back from twitter, we have our access token
            $token = $consumer->getAccessToken($_GET, unserialize($_SESSION['TWITTER_REQUEST_TOKEN']));
            print "Please fill in these items in your application.ini:<br>";
            print "settings.twitter.accessToken: <b>".$token->getToken()."</b><br>";
            print "settings.twitter.accessTokenSecret: <b>".$token->getTokenSecret()."</b><br>";
        }
        exit;
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


    protected function _handlePost($data) {
        if (! isset ($data['q'])) {
            if ($_POST['tweetnow']) {
                $mapper = new Model_Question_Mapper();
                $question = $mapper->getActiveQuestion();
                if ($question != null) {
                    // Already a question active. No tweet
                    return;
                }

                // Set sleeptime to now
                $mainStatus = Phpoton_Status::loadStatus();
                $mainStatus->setSleepTime(time());
                Phpoton_Status::saveStatus($mainStatus);
                return;
            }
            return;
        }



        /**
         * @var $question Model_Question_Entity
         */
        // Find question
        $mapper = new Model_Question_Mapper();
        $question = $mapper->findByPk($data['q']);
        if (! $question instanceof Model_Question_Entity) {
            return;
        }

        // Set correct mode
        switch ($_POST['status']) {
            case "accept" :
                if ($question->canChangeStatus()) {
                    $question->setStatus("pending");
                    $mapper->save($question);
                }
                break;
            case "notaccept" :
            default :
                if ($question->canChangeStatus()) {
                    $question->setStatus("notapproved");
                    $mapper->save($question);
                }
                break;
        }
    }

}