<?php

class IndexController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->addFilter('TwitterLink');
    }

    public function init()
    {
        $this->_helper->layout()->getView()->headTitle('@PHPoton');

        $container = Zend_Registry::get('navigation');
        $this->view->navigation(new Zend_Navigation($container));
    }

    /**
     * Displays FAQ
     *
     * @return void
     */
    public function faqAction()
    {
    }


    public function indexAction()
    {
    }

    
    public function scoreAction() {
        $scoreboard = new Model_Scoreboard_Mapper();
        $this->view->topscore = $scoreboard->getTopScore(20);
    }


    public function tweetsAction() {
        $tweets = new Model_Tweets();
        $this->view->tweets = $tweets->getTweets();
    }


    /**
     * Display status of each question
     * @return void
     */
    public function questionAction() {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id == 0) {
            $mainStatus = Phpoton_Status::loadStatus();
            $id = $mainStatus->getQuestionId();
        }

        $mapper = new Model_Question_Mapper();
        $question = $mapper->findByPk($id);
        $this->view->question = $question;

        if ($question == null) {
            $this->render("question/notfound");
            return;
        }

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
        $mapper = new Model_Question_Mapper();

        $this->view->stats = array();

        foreach ($mapper->fetchAll() as $question) {
            if (! $question->isVisible()) continue;

            $stat = new StdClass();
            $stat->id = $question->getId();
            $stat->replies = $question->getReplyCount();
            $stat->correct = $question->getCorrectReplyCount();
            if ($question->isActive()) {
                $stat->winner = "none yet";
            } else if ($question->isTimedOut()) {
                $stat->winner = "nobody knew";
            } else if ($question->isAnswered()) {
                $stat->winner = $question->getWinnerTweep()->getScreenName();
            } else {
                $stat->winner = "&nbsp;";
            }
            $stat->question = $question->getQuestion();
            if ($question->isFinished()) {
                $stat->answer = $question->getAnswer();
            } else {
                $stat->answer = "&nbsp;";
            }
            $stat->tweetedat = $question->getTweetDt();

            $this->view->stats[] = $stat;
        }
    }

}

