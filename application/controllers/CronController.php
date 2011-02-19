<?php

/**
 * This controller can only be accessed by CLI sapi
 */

class CronController extends Zend_Controller_Action
{
    public function init()
    {
        $config = Zend_Registry::get('config');

        if ($config->settings->cron->cli_only == 1 && php_sapi_name() != 'cli') {
            throw new Exception('Cannot be called from the web');
        }

        // Add [twitter:] parser
        $this->view->addFilter('TwitterLink');

        // Disable renderer and layout
        $this->_helper->viewRenderer->setNoRender(true);
    }


    /**
     * Main cron entry
     */
    public function indexAction()
    {
        $this->_retrievePublicReplies();
        $this->_retrieveDirectMessageReplies();
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
            print "QuestionTime: ".$config->getSleepTime()."<br>";
            print "CurrentTime: ".time()."<br>";
            // See if it's time to ask a new question
            if ($config->getSleepTime() < time()) {
                print('Passed sleep time: '.$config->getSleepTime().' < '.time().'<br>');
                // Activate and tweet next pending question
                $question = $mapper->getNextPendingQuestion();
                if (! $question instanceof Model_Question_Entity) {
                    print "No new pending question found<br>";
                    // No pending question found
                    return;
                }
                print "Question found: ".$question->getQuestion()."<br>";
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
            print "QuestionTime: ".$question->getTimeLimit()."<br>";
            print "CurrentTime: ".time()."<br>";
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

    protected function _tweetQuestion(Model_Question_Entity $question)
    {
        // Generate twitter message
        $tweetText = "Q".$question->getId().": ".$question->getQuestion();

        // Send message to twitter
        $twitter = Zend_Registry::get('twitter');
        $twitter->statusUpdate($tweetText);
    }


    /**
     * Retrieves replies from twitter
     */
    protected function _retrievePublicReplies()
    {
        print ('FetchReplies<br>');

        $mainStatus = Phpoton_Status::loadStatus();
        $twitter = Zend_Registry::get('twitter');

        /**
         * @var $twitter Zend_Service_Twitter
         */
        // Get latest 100 tweets since specified time
        $options = array('since_id' => $mainStatus->getSinceId(), 'count' => 100);
        $response = $twitter->statusReplies($options);

        // Return when no status tweets are found...
        if (! isset($response->status)) return;

        $mapper = new Model_Question_Mapper();
        $question = $mapper->getActiveQuestion();

        // Don't parse question when no question is found
        if (! $question instanceof Model_Question_Entity) return;
        
        // We cannot get directly the array from zend_rest_result. Which is another reason
        // zend_rest must die a horrible death. We move everything back to an array,
        // reverse it since twitter returns our messages in newest-first order..
        $messages = array();
        foreach ($response->status as $message) {
            $messages[] = $message;
        }
        $messages = array_reverse($messages);


        print "OLD SINCE ID: ".$mainStatus->getSinceId()."<br>";

        // Iterate over all found statuses
        foreach ($messages as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time

            /* Since these ID's are 64bit numbers, we cannot do standard integer
             * compare on 32 bit systems. We need to compare stringwise */
            if (strcmp($status->id,$mainStatus->getSinceId()) >= 1) {
                $mainStatus->setSinceId($status->id);
            }

            print ('New reply found : '.$status->id.' '.$status->user->screen_name.' says: '.$status->text.'<br>');

            // Add twitter user to our friends-list
            $twitter->friendshipCreate($status->user->id);

            // Skip if user already answered the question
            $mapper = new Model_Answer_Mapper();
            if ($mapper->hasAlreadyAnswered($status->user->id, $question)) continue;

            // Save question
            $answer = new Model_Answer_Entity();
            $answer->setAnswer(Phpoton_Clean::cleanup($status->text));
            $answer->setTwitterId($status->user->id);
            $answer->setStatusId($status->id);
            $answer->setQuestionId($question->getId());
            $answer->setReceiveDt(date ("Y-m-d H:i:s", strtotime($status->created_at)));
            $answerMapper = new Model_Answer_Mapper();
            $answerMapper->save($answer);
        }

        // Save the highest since_id back to the status
        print "NEW SINCE ID: ".$mainStatus->getSinceId()."<br>";
        Phpoton_Status::saveStatus($mainStatus);
    }

    /**
     * Retrieves replies from twitter
     */
    protected function _retrieveDirectMessageReplies()
    {
        print ('FetchReplies<br>');

        $mainStatus = Phpoton_Status::loadStatus();
        $twitter = Zend_Registry::get('twitter');

        // Get latest 100 direct tweets since specified time
        $options = array('since_id' => $mainStatus->getSinceDmId(), 'count' => 100);
        $response = $twitter->directMessageMessages($options);

        // Return when no status tweets are found...
        if (! isset($response->direct_message)) return;

        $mapper = new Model_Question_Mapper();
        $question = $mapper->getActiveQuestion();

        // Don't parse question when no question is found
        if (! $question instanceof Model_Question_Entity) return;

        // We cannot get directly the array from zend_rest_result. Which is another reason
        // zend_rest must die a horrible death. We move everything back to an array,
        // reverse it since twitter returns our messages in newest-first order..
        $directMessages = array();
        foreach ($response->direct_message as $message) {
            $directMessages[] = $message;
        }
        $directMessages = array_reverse($directMessages);


//        print "OLD SINCE ID: ".$mainStatus->getSinceDmId()."<br>";

        // Iterate over all found messages
        foreach ($directMessages as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time
            /* Since these ID's are 64bit numbers, we cannot do standard integer
             * compare on 32 bit systems. We need to compare stringwise */
            if (strcmp($status->id,$mainStatus->getSinceId()) >= 1) {
                $mainStatus->setSinceDmId($status->id);
            }
            print ('New DM reply found : '.$status->id.' '.$status->user->screen_name.' says: '.$status->text.'<br>');

            // Skip if user already answered the question
            $mapper = new Model_Answer_Mapper();
            if ($mapper->hasAlreadyAnswered($status->sender->id, $question)) continue;

            print "Not yet answered. Saving<br>";

            // Save answer
            $answer = new Model_Answer_Entity();
            $answer->setAnswer(Phpoton_Clean::cleanup($status->text));
            $answer->setTwitterId($status->sender->id);
            $answer->setStatusId($status->id);
            $answer->setQuestionId($question->getId());
            $answer->setReceiveDt(date ("Y-m-d H:i:s", strtotime($status->created_at)));
            $answerMapper = new Model_Answer_Mapper();
            $answerMapper->save($answer);
        }

        // Save the highest since_id back to the status
//        print "NEW SINCE ID: ".$mainStatus->getSinceDmId()."<br>";
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
        $config = Zend_Registry::get('config');
        $host = $config->settings->hosturl;
        $url = Phpoton_Shortener::shorten($host . "/index/question/id/".$question->getId());

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

            // @TODO: Only one answer will be marked. What about others?
            break;
        }
    }

}
