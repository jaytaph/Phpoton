<?php

class IndexController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        // All these views support the [twitter:] markup
        $this->view->addFilter('TwitterLink');
    }

    public function init()
    {
        // Set title of all views
        $this->_helper->layout()->getView()->headTitle('@PHPoton');

        // Set navigation
        $container = Zend_Registry::get('navigation');
        $this->view->navigation(new Zend_Navigation($container));
    }

    /**
     * Displays FAQ
     */
    public function faqAction()
    {
    }


    /**
     * Displays main screen
     */
    public function indexAction()
    {
    }


    /**
     * Display scoreboard
     */
    public function scoreAction() {
        $scoreboard = new Model_Scoreboard_Mapper();

        $iterator = $scoreboard->getTopScore();
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Iterator($iterator));
        $paginator->setDefaultScrollingStyle('Sliding');
        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->_getParam('page'), 1);
        
        $this->view->score = $paginator;
    }


    /**
     * Display tweets
     */
    public function tweetsAction() {
        $tweets = new Model_Tweets();
        $this->view->tweets = $tweets->getTweets();
    }


    /**
     * Display status of a question
     */
    function questionAction() {
        // Get question parameter
        $id = $this->_getParam('id');
        if (! $id) {
            // Redirect back to stats when no ID is found
            $this->_redirect("/index/stats");
            return;
        }

        // Fetch question
        $mapper = new Model_Question_Mapper();
        $question = $mapper->findByPk($id);
        if (! $question instanceof Model_Question_Entity) {
            $this->render("question/notfound");
            return;
        }
        
        $this->view->question = $question;

        switch ($question->getStatus()) {
            case "moderation" :
                $this->render("question/moderation");
                break;
            case "pending" :
                $this->render("question/pending");
                break;
            case "active" :
                $this->render("question/active");
                break;
            case "done" :
                $this->render("question/done");
                break;
            default :
                $this->render("question/notfound");
                break;
        }
    }


    public function statsAction() {

        /**
         * @var $question Model_Question_Entity
         */
        $mapper = new Model_Question_Mapper();
        $iterator = $mapper->fetchAll();
        $iterator = new Phpoton_Iterator_Question_Visible($iterator);
        foreach ($iterator as $question) {
            $stat = new StdClass();
            $stat->id = $question->getId();
            $stat->replies = $question->getReplyCount();
            $stat->correctReplies = $question->getCorrectReplyCount();
            if ($question->isActive()) continue;

            if ($question->isTimedOut()) {
                $stat->winner = "timed out";
            } else if ($question->isAnswered()) {
                if ($question->getWinnerTweep() == null) {
                    $stat->winner = "Unknown";
                } else {
                    $stat->winner = "[twitter:".$question->getWinnerTweep()->getScreenName()."]";
                }
            } else {
                $stat->winner = "";
            }
            $stat->question = $question->getQuestion();
            if ($question->isFinished()) {
                $stat->answer = $question->getAnswer();
            } else {
                $stat->answer = "";
            }
            $stat->tweetedat = $question->getTweetDt();

            // @TODO: change this into a wonat
            $stat->wonat = $question->getTweetDt();

            $stats[] = $stat;
        }

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($stats));
        $paginator->setDefaultScrollingStyle('Sliding');
        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->_getParam('page'), 1);
        $this->view->stats = $paginator;

    }

}

