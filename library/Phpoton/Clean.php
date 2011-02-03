<?php

class Phpoton_Clean {

    /**
     * Cleans up an answer text
     *
     * @static
     * @return string Cleaned up string
     */
    static function cleanup($text) {
        // Remove @twitter names
        $text = preg_replace("/@\w+/", "", $text);

        // Remove ? (maybe at the end of the line?)
        $text = str_replace("?", "", $text);

        // All lowercased
        $text = strtolower($text);

        // No leading or trailing spaces
        $text = trim($text);
        return $text;
    }

}