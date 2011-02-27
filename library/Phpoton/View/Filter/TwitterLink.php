<?php

class Phpoton_View_Filter_TwitterLink implements Zend_Filter_Interface
{
    
    public function filter($content)
    {
        return $this->_replaceTwitterLinks($content);
    }

    private function _replaceTwitterLinks($data)
    {
        $data = preg_replace_callback('|(?:\[twitter:([^\/\]]+)(?:/([0-9]+))?\])|si', array(&$this, "_replaceTwitterLink"), $data);
        $data = preg_replace_callback('|(?:\[twitter_id:([^\/\]]+)(?:/([0-9]+))?\])|si', array(&$this, "_replaceTwitterIdLink"), $data);
        return $data;
    }

    private function _replaceTwitterLink($matches)
    {
        if (count($matches) == 3) {
            return '<a href="http://twitter.com/#!/@'.$matches[1].'/status/'.$matches[2].'">@'.$matches[1].'</a>';
        } else {
            return '<a href="http://twitter.com/#!/@'.$matches[1].'">@'.$matches[1].'</a>';
        }
    }

    private function _replaceTwitterIdLink($matches)
    {
        $mapper = new Model_Tweep_Mapper();
        $entity = $mapper->findByPk($matches[1]);
        if (! $entity instanceof Model_Tweep_Entity) return;

        if (count($matches) == 3) {
            return '<a href="http://twitter.com/#!/@'.$entity->getScreenName().'/status/'.$matches[2].'">@'.$entity->getScreenName().'</a>';
        } else {
            return '<a href="http://twitter.com/#!/@'.$entity->getScreenName().'">@'.$entity->getScreenName().'</a>';
        }
    }
}