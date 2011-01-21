<?php

require_once("forms/Submit.php");

class SubmitController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function indexAction() {
        $form = new Form_Submit();

        if ($this->getRequest()->isPost()) {
            $formdata = $this->getRequest()->getPost();
            if ($form->isValid($formdata)) {
                $this->render("submitted");
                return;
            }
            $form->populate($formdata);
        }

        $this->view->form = $form;
    }

}

