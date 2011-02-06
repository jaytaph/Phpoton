<?php

/**
 * Only returns questions that are visible to be seen by users
 */
class Phpoton_Iterator_Question_Visible extends FilterIterator {

    public function accept()
    {
        $question = parent::current();
        return ($question->isVisible());
    }
}