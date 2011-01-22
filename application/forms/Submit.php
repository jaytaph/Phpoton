<?php

class Form_Submit extends Zend_Form {
    public function __construct($options = null) {
        parent::__construct($options);

        $this->setName('submit_a_question');

        $twitter = new Zend_Form_Element_Text('twitter');
        $twitter->setLabel('Your twitter account')
                ->setAttrib('maxlength', 35)
                ->addValidator('NotEmpty')
                ->addFilter('StripTags');

        $fullname = new Zend_Form_Element_Text('fullname');
        $fullname->setLabel('Your full name')
                ->setRequired(true)
                ->setAttrib('maxlength', 50)
                ->addFilter('StripTags');

        $question = new Zend_Form_Element_Textarea('question');
        $question->setLabel('Question')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setAttrib('cols', 50)
                ->setAttrib('rows', 3);

        $answer = new Zend_Form_Element_Text('answer');
        $answer->setLabel('Answer')
               ->setRequired(true)
               ->addValidator('NotEmpty')
               ->addFilter('StripTags')
               ->setAttrib('maxlength', 50)
               ->setAttrib('size', 50);


        $options = array('captcha' => array(
            'captcha' => 'Image',
            'wordLen' => 3,
            'font' => APPLICATION_PATH. '/../Tuffy.ttf',
            'imgDir' => APPLICATION_PATH . "/../public/images/captcha"));

        $captcha = new Zend_Form_Element_Captcha('captcha', $options);
        $captcha->setLabel('I do not tolerate other bots. Proof you are human!');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Submit question to phpoton');

        $this->addElements(array($twitter, $fullname, $question, $answer, $captcha, $submit));
    }
}
 
