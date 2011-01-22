<?php

class Phpoton_View_Filter_TwitterLink implements Zend_Filter_Interface
{
    
    public function filter($content)
    {
        return $this->_replaceTwitterLinks($content);
    }

    private function _replaceTwitterLinks($data)
    {
        return preg_replace_callback('|(?:\[twitter:([^\/\]]+)(?:/([0-9]+))?\])|si', array(&$this, "_replaceTwitterLink"), $data);
    }

    private function _replaceTwitterLink($matches)
    {
        if (count($matches) == 3) {
            return '<a href="http://twitter.com/#!/@'.$matches[1].'/status/'.$matches[2].'">@'.$matches[1].'</a>';
        } else {
            return '<a href="http://twitter.com/#!/@'.$matches[1].'">@'.$matches[1].'</a>';
        }
    }
}