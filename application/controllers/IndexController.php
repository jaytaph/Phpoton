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


//    public function statusAction() {
//        $twitter = Zend_Registry::get('twitter');
//        $response = $twitter->directMessage->messages();
//
//        print "<pre>";
//        var_dump ($response);
//
//        print "<table>";
//        print "<tr><th>Thumb</th><th>User</th><th>Tweet</th></tr>";
//        foreach ($response->messages as $status) {
//            print "<tr>";
//            print "<td><img src='".$status->user->profile_image_url."'></td>";
//            print "<td>".$status->user->screen_name."</td>";
//            print "<td>".$status->text."</td>";
//            print "</tr>";
//        }
//        print "</table>";
//
//        exit;
//    }
//
//
//    public function repliesAction()
//    {
//        /**
//         * @var $twitter Zend_Service_Twitter
//         */
//        $twitter = Zend_Registry::get('twitter');
//        $response = $twitter->statusReplies();
////        print "<pre>";
////        print_r ($response->status);
////        exit;
//
//
//        $odd = true;
//        print "<table>";
//        print "<tr><th>Thumb</th><th>Time</th><th>Tweet</th></tr>";
//        foreach ($response->status as $status) {
//            if ($odd) {
//                print "<tr style='background-color: #ddd'>";
//            } else {
//                print "<tr style='background-color: #eee'>";
//            }
//            $odd = ! $odd;
//            print "<td>".$status->user->screen_name."</td>";
//            print "<td>".$status->created_at."</td>";
//            print "<td>".$status->text."</td>";
//            print "</tr>";
//        }
//        print "</table>";
//        exit;
//    }



}

