<?php

class Form_Login extends Zend_Form {
    public function __construct($options = null) {
        parent::__construct($options);

        $this->setName('login');

        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('User')
                ->setRequired(true)
                ->setAttrib('maxlength', 50)
                ->addFilter('StripTags');

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password')
                ->setRequired(true)
                ->addValidator('NotEmpty');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Login');

        $this->addElements(array($username, $password, $submit));
    }
}

