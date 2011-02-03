<?php

class Phpoton_Shortener {

    /**
     * Shortens URL through an 3rd party url shortener provider
     * 
     * @static
     * @return string $url
     */
    static function shorten($url) {
        $ch = curl_init("http://is.gd/api.php?longurl=".urlencode($url));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $tinyUrl = curl_exec($ch);
        curl_close($ch);

        // No response, use original URL
        if (empty($tinyUrl)) $tinyUrl = $url;

        return $tinyUrl;
    }
    
}