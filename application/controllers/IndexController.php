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

        $container = array();
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'index', 'label' => 'Home'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'faq', 'label' => 'F.A.Q.'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'tweets', 'label' => 'Tweets'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'questions', 'label' => 'Questions'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'index', 'action' => 'stats', 'label' => 'Statistics'));
        $container[] = new Zend_Navigation_Page_Mvc(array('controller' => 'admin', 'action' => 'index', 'label' => 'Admin'));

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


    public function indexAction() {
        $scoreboard = new Model_Scoreboard_Mapper();
        $this->view->topscore = $scoreboard->getTopScore(20);
    }


    /**
     * Display status of each question
     * @return void
     */
    public function questionAction() {
        $id = (int) $this->getRequest()->getParam('id');

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
            print "<tr style='background-color: ".(($odd = ! $odd)?"#ddd":"#eee")."'>";
            print "<td align=right>".$question->getId()."</td>";
            print "<td align=right>".$question->getReplyCount()."</td>";
            print "<td align=right>".$question->getCorrectReplyCount()."</td>";
            print "<td>".$question->getWinner()."</td>";
            print "<td>".$question->getQuestion()."</td>";
            print "<td>".$question->getAnswer()."</td>";
            print "<td>".$question->getTweetDt()."</td>";
            print "</tr>";
        }
        print "</table>";
        exit;
    }

}

