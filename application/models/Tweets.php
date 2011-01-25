<?php

// @TODO: Not happy with the current setup.. refactor..

class Model_Tweets {
    const CACHEFILE = "/tmp/tweets.cache";
    const MAX_CACHE_TIME = 60;

    protected $_tweets = array();

    // Return tweets in JSON format
    function getTweets() {
        if ($this->_tweets == null) {
            $cache = $this->_getCache();
            if ($cache == null || time() > $cache['time'] + self::MAX_CACHE_TIME) {
                $this->_tweets = $this->_renewTweetsFromApi();
                $this->_saveCache($this->_tweets);
            } else {
                $this->_tweets = $cache['tweets'];
            }
        }
        return $this->_tweets;
    }


    // Fetch cache or return NULL when no cache is found
    protected function _getCache() {
        $cachefile = self::CACHEFILE;
        $tmp = file_exists($cachefile) ? @unserialize(file_get_contents($cachefile)) : null;
        return $tmp;
    }

    // Save data into cache
    protected function _saveCache($tweets) {
        $tmp = array();
        $tmp['time'] = time();
        $tmp['tweets'] = $tweets;
        file_put_contents(self::CACHEFILE, serialize($tmp));
    }

    // Fetch tweets from API
    protected function _renewTweetsFromApi() {
        /**
         * @var $twitter Zend_Service_Twitter
         */
        $twitter = Zend_Registry::get('twitter');
        $data = $twitter->statusUserTimeline();

        $tmp = array();
        foreach ($data->status as $status) {
            $tmp[] = json_decode(json_encode($status));
        }
        return $tmp;
    }

}