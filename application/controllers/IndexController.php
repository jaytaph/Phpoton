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

        print "<table>";
        print "<tr>";
        print "<th>#</th>";
        print "<th>Replies</th>";
        print "<th>Correct</th>";
        print "<th>Winner</th>";
        print "<th>Question</th>";
        print "<th>Answer</th>";
        print "<th>Tweeted on</th>";
        print "</tr>";

        $odd = false;
        foreach ($mapper->fetchAll() as $question) {
            if (! $question->isVisible()) continue;

            /**
             * @var $question Model_Question_Entity
             */
            print "<tr style='background-color: ".(($odd = ! $odd)?"#ddd":"#eee")."'>";
            print "<td align=right>".$question->getId()."</td>";
            print "<td align=right>".$question->getReplyCount()."</td>";
            print "<td align=right>".$question->getCorrectReplyCount()."</td>";
            if ($question->isActive()) {
                print "<td>none yet</td>";
            } else if ($question->isTimedOut()) {
                print "<td>nobody knew</td>";
            } else if ($question->isAnswered()) {
                print "<td>".$question->getWinnerTweep()->getScreenName()."</td>";
            } else {
                print "<td>&nbsp;</td>";
            }
            print "<td>".$question->getQuestion()."</td>";
            if ($question->isFinished()) {
                print "<td>".$question->getAnswer()."</td>";
            } else {
                print "<td>&nbsp;</td>";
            }
            print "<td>".$question->getTweetDt()."</td>";
            print "</tr>";
        }
        print "</table>";
        exit;
    }

}

