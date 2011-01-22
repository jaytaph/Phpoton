<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
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


    public function statusAction() {
        $twitter = Zend_Registry::get('twitter');
        $response = $twitter->directMessage->messages();

        print "<pre>";
        var_dump ($response);

        print "<table>";
        print "<tr><th>Thumb</th><th>User</th><th>Tweet</th></tr>";
        foreach ($response->messages as $status) {
            print "<tr>";
            print "<td><img src='".$status->user->profile_image_url."'></td>";
            print "<td>".$status->user->screen_name."</td>";
            print "<td>".$status->text."</td>";
            print "</tr>";
        }
        print "</table>";

        exit;
    }

    
    public function repliesAction()
    {
        /**
         * @var $twitter Zend_Service_Twitter
         */
        $twitter = Zend_Registry::get('twitter');
        $response = $twitter->statusReplies();
//        print "<pre>";
//        print_r ($response->status);
//        exit;


        $odd = true;
        print "<table>";
        print "<tr><th>Thumb</th><th>Time</th><th>Tweet</th></tr>";
        foreach ($response->status as $status) {
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
        exit;
    }



}

