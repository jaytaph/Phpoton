<?php

/**
 * This controller can only be accessed by CLI sapi
 */

class CronController extends Zend_Controller_Action
{

    public function init()
    {
        // @TODO: check SAPI, must be CLI

        // Disable renderer and layout
        // $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction()
    {
        // Does nothing
    }

    public function tweetquestionAction() {
        // Find a pending question
        $mapper = new Model_Question_Mapper();
        $question = $mapper->getPendingQuestion();
        if ($question == null) {
            print "No more pending questions...";
            return;
        }

        // Generate twitter message
        $tweetText = "Q".$question->getId().": ".$question->getQuestion();

        // Send message to twitter
        $twitter = Zend_Registry::get('twitter');
        $twitter->status->update($tweetText);

        // Save current tweet time into the database
        $question->setTweetDt(new Zend_Db_Expr('NOW()'));
        $mapper->save($question);

        print "Question ".$question->getId()." tweeted: ".$tweetText.".\n";
    }
    
}

