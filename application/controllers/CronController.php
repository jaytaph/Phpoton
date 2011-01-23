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

    
    public function retrieverepliesAction() {
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
        $questionId = (int) $this->getRequest()->getParam('id');
        if ($questionId == null) {
            print "No question Id";
            return;
        }


        
        // Fetch active question
        $mapper = new Model_Question_Mapper();
        $question = $mapper->findByPk($questionId);

        if ($question == null) {
            print "Question not found...";
            return;
        }

        if ($question->getStatus() != "done") {
            // Do nothing when question is not done yet...
            print "Nothing to do...";
            return;
        }

        // Shorten URL
        $url = Phpoton_Shortener::shorten("http://phpoton.com/question/".$question->getId());

        // Generate tweet text
        $tweetText = "Points for Q".$question->getId()." go to @".$question->getWinnerTweep()->getScreenName()." #phpoton ".$url;

        // Send message to twitter
        $twitter = Zend_Registry::get('twitter');
        $result = $twitter->status->update($tweetText);

        print "<pre>";
        var_dump($result);
    }

    public function parseanswersAction() {
        $question_mapper = new Model_Question_Mapper();
        $question = $question_mapper->getActiveQuestion();

        if ($question == null) {
            print "No active questions!";
            return;
        }

        print "<pre>";
        print "Question: <b>".$question->getQuestion()."</b>: ".$question->getAnswer()."<br><br>";

        $mapper = new Model_Answer_Mapper();
        $answers = $mapper->fetchSequentialAnswersForQuestion($question);
        foreach ($answers as $answer) {
            /**
             * @var $answer Model_Answer_Entity
             */
            print "Answer [twitter:@".$answer->getTweep()->getScreenName()."/".$answer->getStatusId()."] by ".$answer->getTweep()->getScreenName()." : ".$answer->getAnswer()." : ";

            if ($question->isCorrectAnswer($answer)) {
                print "Correct answer!";

                // Set winner
                $question->setStatus('done');
                $question->setWinningAnswerId($answer->getId());
                $question_mapper->save($question);

                $scoreboard = new Model_Scoreboard_Mapper();
                $scoreboard->increaseScore($answer->getTweep());
                break;
            }
            print "<br>";
        }
    }

}

