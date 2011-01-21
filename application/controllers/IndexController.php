<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
//        print "<pre>";
//        session_start();
//        var_dump ($_SESSION);
    }

//    public function authenticateAction()
//    {
//        $config = Zend_Registry::get('config');
//        $oauth = new Zend_Oauth_Consumer($config->settings->oauth->config);
//        $requestToken = $oauth->getRequestToken();
////        print "<pre>";
////        var_dump ($requestToken);
//
//        $_SESSION['reqtoken'] = serialize($requestToken);
//        session_write_close();
//
//        print "<a href=".$oauth->getRedirectUrl().">Click here</a>";
//        exit;
//    }

//    public function callbackAction()
//    {
//        $config = Zend_Registry::get('config');
//        $oauth = new Zend_Oauth_Consumer($config->settings->oauth->config);
////        $requestToken = $oauth->getRequestToken();
////        print "<pre>";
////        var_dump ($requestToken);
//
////        $_SESSION['reqtoken'] = serialize($requestToken);
////        session_write_close();
////        exit;
//
//        $requestToken = unserialize($_SESSION['reqtoken']);
//        $accessRequestToken = $oauth->getAccessToken($_GET, $requestToken);
//        var_dump ($accessRequestToken);
//        $_SESSION['acc_req_token'] = serialize($accessRequestToken);
//        sesssion_write_close();
//        exit;
//    }

    public function indexAction() {
        $twitter = Zend_Registry::get('twitter');
        //$response = $twitter->status->friendsTimeline();
        $response = $twitter->directMessage->messages();

        print "<table>";
        print "<tr><th>Thumb</th><th>User</th><th>Tweet</th></tr>";
        foreach ($response->direct_message as $status) {
            print "<tr>";
            print "<td><img src='".$status->sender->profile_image_url."'></td>";
            print "<td>".$status->sender->screen_name."</td>";
            print "<td>".$status->text."</td>";
            print "</tr>";
        }
        print "</table>";

        exit;
    }

    public function tweetAction()
    {
        $twitter = Zend_Registry::get('twitter');

        $twitter->status->update("Bliep! Hello world, again!");
        exit;
    }



}

