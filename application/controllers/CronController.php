<?php

/**
 * This controller can only be accessed by CLI sapi
 */

class CronController extends Zend_Controller_Action
{

    public function init()
    {
        $config = Zend_Registry::get('config');

        // Make sure we're being called as a CLI script
        if ($config->settings->cron->cli_only == 1 && php_sapi_name() != 'cli') {
            throw new Exception('Cannot be called from the web');
        }

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

        // Get status object
        $mainStatus = Phpoton_Status::loadStatus();
        $mainStatus->setQuestionId($question->getId());
        Phpoton_Status::saveStatus($mainStatus);

        print "Question ".$question->getId()." tweeted: ".$tweetText.".\n";
    }

    
    public function retreiverepliesAction() {
        // Get status object
        $mainStatus = Phpoton_Status::loadStatus();

        /**
         * @var $twitter Zend_Service_Twitter
         */
        $options = array('since_id' => $mainStatus->getSinceId(), 'count' => 100);
        $twitter = Zend_Registry::get('twitter');
        $response = $twitter->statusReplies($options);


        // Return when no status tweets are found...
        if (! isset($response->status)) {
            print "No replies found...";
            return;
        }


        // Iterate over all found statuses
        $odd = false;
        print "<table>";
        print "<tr><th>Thumb</th><th>Time</th><th>Tweet</th></tr>";
        foreach ($response->status as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time
            if ($status->id > $mainStatus->getSinceId()) $mainStatus->setSinceId($status->id);

            $answer = new Model_Answer_Entity();
            $answer->setAnswer($status->text);
            $answer->setTwitterId($status->user->id);
            $answer->setStatusId($status->id);
            $answer->setQuestionId($mainStatus->getQuestionId());
            $answer->setReceiveDt($status->created_at);
            $answerMapper = new Model_Answer_Mapper();
            $answerMapper->save($answer);

            print "<tr style='background-color: ".(($odd = ! $odd)?"#ddd":"#eee")."'>";
            print "<td>".$status->user->screen_name."</td>";
            print "<td>".$status->created_at."</td>";
            print "<td>".$status->text."</td>";
            print "</tr>";
        }
        print "</table>";


        // Save the highest since_id back to the status
        Phpoton_Status::saveStatus($mainStatus);
    }


    public function tweetwinnerAction() {
        $tweetText = "Points for Q1 goes to @jaytaph #phpoton http://bit.ly/fHbi7B";

        // Send message to twitter
        $twitter = Zend_Registry::get('twitter');
        $twitter->status->update($tweetText);
    }

}

