<?php

/**
 * This controller can only be accessed by CLI sapi
 */

class CronController extends Zend_Controller_Action
{
    public function init()
    {
        $config = Zend_Registry::get('config');

        // @TODO: Make sure cron script can run from CLI
//         Make sure we're being called as a CLI script
//        if ($config->settings->cron->cli_only == 1 && php_sapi_name() != 'cli') {
//            throw new Exception('Cannot be called from the web');
//        }

        // Disable renderer and layout
        $this->_helper->viewRenderer->setNoRender(true);
    }


    /**
     * Main cron entry
     */
    public function indexAction()
    {
        $this->_retrieveReplies();
        $this->_parseAnswers();
        $this->_handleQuestion();
    }


    /**
     * Sorts out everything concerning a (new) question
     */
    protected function _handleQuestion()
    {
        print ('HandleQuestion<br>');
        $config = Phpoton_Status::loadStatus();

        $mapper = new Model_Question_Mapper();
        $question = $mapper->getActiveQuestion();

        // No active question found?
        if (! $question instanceof Model_Question_Entity) {
            print('No active question found<br>');
            print "QT: ".$config->getSleepTime()."<br>";
            print "CT: ".time()."<br>";
            // See if it's time to ask a new question
            if ($config->getSleepTime() < time()) {
                print('Passed sleep time: '.$config->getSleepTime().' < '.time().'<br>\n');
                // Activate and tweet next pending question
                $question = $mapper->getNextPendingQuestion();
                if (! $question instanceof Model_Question_Entity) {
                    // No pending question found
                    return;
                }
                $question->markAsActive();
                $this->_tweetQuestion($question);
            } else {
                print('Not yet passed<br>');
            }
        } else {
            print('Active question detected<br>');
            // Question is active

            /* Check if the question has timed out (we already parsed
             * incoming answers in a previous check, so we can safely
             * timeout the question at this point */
            print "QT: ".$question->getTimeLimit()."<br>";
            print "CT: ".time()."<br>";
            if ($question->getTimeLimit() <= time()) {
                print('Question is done<br>');
                // Question timed out
                $question->setStatus("done");
                $mapper->save($question);

                // Tweet winner (in this case, nobody - timeout)
                $this->_tweetWinner($question);
                
                // Set idle time for 5 minutes
                $config->setSleepTime(time()+3600+rand(0, 600));
                Phpoton_Status::saveStatus($config);
            } else {
                print('Question is not yet done<br>');
            }
        }
    }

    protected function _tweetQuestion(Model_Question_Entity $question) {
        // Generate twitter message
        $tweetText = "Q".$question->getId().": ".$question->getQuestion();

        // Send message to twitter
        $twitter = Zend_Registry::get('twitter');
        $twitter->statusUpdate($tweetText);
    }


    /**
     * Retrieves replies from twitter
     */
    protected function _retrieveReplies()
    {
        print ('FetchReplies<br>');
        // Get status object
        $mainStatus = Phpoton_Status::loadStatus();
        
        /**
         * @var $twitter Zend_Service_Twitter
         */
        // Get latest 100 tweets since specified time
        $options = array('since_id' => $mainStatus->getSinceId(), 'count' => 100);
        $twitter = Zend_Registry::get('twitter');
        $response = $twitter->statusReplies($options);

        // Return when no status tweets are found...
        if (! isset($response->status)) return;

        $mapper = new Model_Question_Mapper();
        $question = $mapper->getActiveQuestion();

        // Iterate over all found statuses
        foreach ($response->status as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time
            if ($status->id > $mainStatus->getSinceId()) $mainStatus->setSinceId($status->id);

            print ('New reply found : '.$status->user->screen_name.' says: '.$status->text.'<br>');

            // Add twitter user to our friends-list
            $tmp = $twitter->friendshipCreate($status->user->id);

            // Don't save answers when there is no current question
            if ($question instanceof Model_Question_Entity) {
                $answer = new Model_Answer_Entity();
                $answer->setAnswer(Phpoton_Clean::cleanup($status->text));
                $answer->setTwitterId($status->user->id);
                $answer->setStatusId($status->id);
                $answer->setQuestionId($question->getId());
                $answer->setReceiveDt(date ("Y-m-d H:i:s", strtotime($status->created_at)));
                $answerMapper = new Model_Answer_Mapper();
                $answerMapper->save($answer);
            }
        }

        // Save the highest since_id back to the status
        Phpoton_Status::saveStatus($mainStatus);


        // Get latest 100 direct tweets since specified time
        $options = array('since_id' => $mainStatus->getSinceDmId(), 'count' => 100);
        $response = $twitter->directMessageMessages($options);

        // Return when no status tweets are found...
        if (! isset($response->direct_message)) return;

        // Iterate over all found statuses
        foreach ($response->direct_message as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time
            if ($status->id > $mainStatus->getSinceDmId()) $mainStatus->setSinceDmId($status->id);

            print ('New DM reply found : '.$status->user->screen_name.' says: '.$status->text.'<br>');

            // Don't save answers when there is no current question
            if ($question instanceof Model_Question_Entity) {
                $answer = new Model_Answer_Entity();
                $answer->setAnswer(Phpoton_Clean::cleanup($status->text));
                $answer->setTwitterId($status->sender->id);
                $answer->setStatusId($status->id);
                $answer->setQuestionId($question->getId());
                $answer->setReceiveDt(date ("Y-m-d H:i:s", strtotime($status->created_at)));
                $answerMapper = new Model_Answer_Mapper();
                $answerMapper->save($answer);
            }
        }

        // Save the highest since_id back to the status
        Phpoton_Status::saveStatus($mainStatus);
    }


    /**
     * Tweet a winner
     */
    protected function _tweetWinner(Model_Question_Entity $question)
    {
        // Sanity check to see if the question is really done
        if ($question->getStatus() != "done") return;

        // Shorten URL
        $url = Phpoton_Shortener::shorten("http://phpoton.com/index/question/id/".$question->getId());

        // Generate tweet text
        if ($question->getWinnerTweep() == null) {
            $tweetText = "Nobody answered Q".$question->getId()." correctly. #phpoton ".$url;
        } else {
            $tweetText = "Points for Q".$question->getId()." go to @".$question->getWinnerTweep()->getScreenName()." #phpoton ".$url;
        }

        // Send message to twitter
        $twitter = Zend_Registry::get('twitter');
        $result = $twitter->statusUpdate($tweetText);
    }


    /**
     * Parse answers and check for a winner
     */
    protected function _parseAnswers()
    {
        print "parse answers<br>\n";

        // Don't parse when no question is active
        $question_mapper = new Model_Question_Mapper();
        $question = $question_mapper->getActiveQuestion();
        if (! $question instanceof Model_Question_Entity) return;

        /**
         * @var $question Model_Question_Entity
         */
        // Fetch answers in sequential order for current question
        $mapper = new Model_Answer_Mapper();
        $answers = $mapper->fetchCorrectAnswers($question);
        foreach ($answers as $answer) {
            // Found correct answers.

            // @TODO: Only one answer will be marked. What about others?
            
            print "Correct answer: ".$answer->getAnswer()."<br>\n";
            
            /**
             * @var $answer Model_Answer_Entity
             */

            // Set winner
            $question->setStatus('done');
            $question->setWinningAnswerId($answer->getId());
            $question_mapper->save($question);

            // Increase winner score
            $scoreboard = new Model_Scoreboard_Mapper();
            $scoreboard->increaseScore($answer->getTweep());

            // Tweet winner
            // @TODO: we tweet the winner immediately. Maybe it's nicer to wait a while?
            $this->_tweetWinner($question);

            // Set idle time for 5 minutes (+ rand 5 minutes)
            $mainStatus = Phpoton_Status::loadStatus();
            $mainStatus->setSleepTime(time()+3600+rand(0, 600));
            Phpoton_Status::saveStatus($mainStatus);

            break;
        }
    }

}
