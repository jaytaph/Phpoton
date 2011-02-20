<?php

/**
 * This controller can only be accessed by CLI sapi
 */

class CronController extends Zend_Controller_Action
{
    public function init()
    {
        $config = Zend_Registry::get('config');

        // Check if we can be called through the web
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
        // Retrieve replies
        $this->_retrievePublicReplies();
        $this->_retrieveDirectMessageReplies();

        // Handle the question
        $this->_handleQuestion();
    }


    /**
     * Sorts out everything concerning a (new) question
     */
    protected function _handleQuestion()
    {
        print ('HandleQuestion<br>');

        // Fetch active question
        $mapper = new Model_Question_Mapper();
        $question = $mapper->getActiveQuestion();

        // No question was found
        if (! $question instanceof Model_Question_Entity) {
            print('No active question found<br>');
            $this->_handleNewQuestion();
        } else {
            // Handle active question
            print('Active question found<br>');
            $this->_handleCurrentQuestion();
        }
    }

    // Called when a new question can be tweeted.
    function _handleNewQuestion() {
        $mainStatus = Phpoton_Status::loadStatus();

        // Check if the sleep time has passed. If not, return
        print "Question Time: ".$mainStatus->getSleepTime()."<br>";
        print "Current  Time: ".time()."<br>";
        if ($mainStatus->getSleepTime() > time()) return;

        // Time to tweet new question

        print('Passed sleep time: '.$mainStatus->getSleepTime().' < '.time().'<br>');
        // Activate and tweet next pending question
        $mapper = new Model_Question_Mapper();
        $question = $mapper->getNextPendingQuestion();
        if (! $question instanceof Model_Question_Entity) {
            print "No new pending question found<br>";
            // No pending question found
            return;
        }
        print "Question found: ".$question->getQuestion()."<br>";
        $question->markAsActive();
        $this->_tweetQuestion($question);
    }

    // Called when a current question is active
    function _handleCurrentQuestion() {
        $mainStatus = Phpoton_Status::loadStatus();
        $config = Zend_Registry::get('config');

        // Fetch current active question
        $mapper = new Model_Question_Mapper();
        $question = $mapper->getActiveQuestion();

        // Check if the question time has passed. If not, return
        print "Question Time: ".$question->getTimeLimit()."<br>";
        print "Current  Time: ".time()."<br>";
        if ($question->getTimeLimit() > time()) return;

        print('Question is done<br>');

        $question->setStatus("done");
        $mapper->save($question);

        // Do the scoring of the question
        $this->_parseAnswers($question);

        // Reload question. The winner tweep might have been changed
        $question = $mapper->findByPk($question->getId());

        // Tweet winner (or if nobody won, display timeout)
        $this->_tweetQuestionResult($question);

        // Set idle time before next question will be tweeted
        $mainStatus->setSleepTime(
            time()+rand(
                $config->settings->questions->inactivity_min,
                $config->settings->questions->inactivity_max)
        );
        Phpoton_Status::saveStatus($mainStatus);
    }


    /**
     * Tweets question to the outside world
     * 
     * @param Model_Question_Entity $question
     * @return void
     */
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


        // Iterate over all found statuses
        foreach ($messages as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time

            /* Since these ID's are 64bit numbers, we cannot do standard integer
             * compare on 32 bit systems. We need to compare stringwise */
            if (strcmp($status->id, $mainStatus->getSinceId()) >= 1) {
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


        // Iterate over all found messages
        foreach ($directMessages as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time
            /* Since these ID's are 64bit numbers, we cannot do standard integer
             * compare on 32 bit systems. We need to compare stringwise */
            if (strcmp($status->id, $mainStatus->getSinceDmId()) >= 1) {
                $mainStatus->setSinceDmId($status->id);
            }
            print ('New DM reply found : '.$status->id.' '.$status->user->screen_name.' says: '.$status->text.'<br>');

            // Skip if user already answered the question
            $mapper = new Model_Answer_Mapper();
            if ($mapper->hasAlreadyAnswered($status->sender->id, $question)) continue;

            print "Tweep has not yet answered this question. Saving<br>";

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
        Phpoton_Status::saveStatus($mainStatus);
    }


    /**
     * Tweet a winner
     */
    protected function _tweetQuestionResult(Model_Question_Entity $question)
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
            $correctAnswers = $question->getCorrectReplyCount() - 1;
            $tweetText = "Points for Q".$question->getId()." go to @".$question->getWinnerTweep()->getScreenName()." and ".$correctAnswers." other".($correctAnswers==1?"":"s").". #phpoton ".$url;
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
        $config = Zend_Registry::get('config');

        // Don't parse when no question is active
        $question_mapper = new Model_Question_Mapper();
        $question = $question_mapper->getActiveQuestion();
        if (! $question instanceof Model_Question_Entity) return;

        // Fetch answers in sequential order for current question
        $answer_mapper = new Model_Answer_Mapper();
        $answers = $answer_mapper->fetchCorrectAnswers($question);

        // No correct answers were found. Do nothing
        if (count($answers) == 0) return;

        $points = explode(",", $config->settings->questions->points);

        // Iterate over all answers
        foreach ($answers as $position => $answer) {
            /**
             * @var $answer Model_Answer_Entity
             */

            // First entry is the overall winner
            if ($position == 0) {
                $question->setWinningAnswerId($answer->getId());
                $question_mapper->save($question);
            }

            // Get score
            $score = isset($points[$position]) ? $points[$position] : 1;

            // Increase winner score
            $scoreboard = new Model_Scoreboard_Mapper();
            $scoreboard->increaseScore($answer->getTweep(), $score);

            print "Correct answer: $position : ".$answer->getTwitterId()." ".$answer->getAnswer()." ".$score."<br>\n";
        }
    }

}
