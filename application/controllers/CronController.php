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

        print "Question ".$question->getId()." tweeted: ".$tweetText.".\n";
    }

    
    public function getrepliesAction() {
        // @TODO: Should be a service: Phpoton_Config->loadConfig()
        // Get status object
        $mapper = new Model_Status_Mapper();
        $config = $mapper->findByPk(1);

        /**
         * @var $twitter Zend_Service_Twitter
         */
        $options = array('since_id' => $config->getSinceId(), 'count' => 100);
        $twitter = Zend_Registry::get('twitter');
        $response = $twitter->statusReplies($options);


        // Return when no status tweets are found...
        if (! isset($response->status)) {
            print "No replies found...";
            return;
        }


        // Iterate over all found statuses
        $odd = true;
        print "<table>";
        print "<tr><th>Thumb</th><th>Time</th><th>Tweet</th></tr>";
        foreach ($response->status as $status) {
            // Always save the highest status ID so we don't have to fetch these the next time
            if ($status->id > $config->getSinceId()) $config->setSinceId($status->id);
            if ($odd) {
                print "<tr style='background-color: #ddd'>";
            } else {
                print "<tr style='background-color: #eee'>";
            }
            $odd = ! $odd;
            print "<td>".$status->user->screen_name."</td>";
            print "<td>".$status->created_at."</td>";
            print "<td>".$status->text."</td>";
            print "</tr>";
        }
        print "</table>";


        // Save the highest since_id back to the config
        // @TODO: Should be a service: Phpoton_Config->saveConfig()
        $mapper->save($config);
    }
    
}

