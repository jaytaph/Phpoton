<?php

class Form_Submit extends Zend_Form {
    const MAX_QUESTION_LENGTH = 120;

    public function __construct($options = null) {
        parent::__construct($options);

        $this->setName('submit_a_question');

        $fullname = new Zend_Form_Element_Text('fullname');
        $fullname->setRequired(true)
                 ->setDecorators(array('ViewHelper','Errors'))
                 ->setAttrib('maxlength', 50)
                 ->addFilter('StripTags');
        
        $twitter = new Zend_Form_Element_Text('twitter');
        $twitter->setAttrib('maxlength', 35)
                ->setDecorators(array('ViewHelper','Errors'))
                ->addValidator('NotEmpty')
                ->addFilter('StripTags');

        $question = new Zend_Form_Element_Textarea('question');
        $question->setRequired(true)
                 ->setDecorators(array('ViewHelper','Errors'))
                 ->addValidator('NotEmpty')
                 ->setAttrib('cols', 50)
                 ->setAttrib('rows', 3)
                 ->setAttrib('onKeyDown','limitText(this.form.question,this.form.countdown,'.self::MAX_QUESTION_LENGTH.');')
                 ->setAttrib('onKeyUp','limitText(this.form.question,this.form.countdown,'.self::MAX_QUESTION_LENGTH.');');

        $countdown = new Zend_Form_Element_Text('countdown');
        $countdown->setAttrib('readonly', 'readonly')
                  ->setDecorators(array('ViewHelper','Errors'))
                  ->setAttrib('size', 3)
                  ->setValue(self::MAX_QUESTION_LENGTH)
                  ->setDescription('chars left');

        $answer = new Zend_Form_Element_Text('answer');
        $answer->setRequired(true)
               ->setDecorators(array('ViewHelper','Errors'))
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

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Submit question to phpoton')
               ->setDecorators(array('ViewHelper','Errors'));

        $this->addElements(array($fullname, $twitter, $question, $countdown, $answer, $captcha, $submit));

        $this->setDecorators(array(array('ViewScript', array('viewScript' => 'submit/submitform.phtml'))));
    }
}
 
