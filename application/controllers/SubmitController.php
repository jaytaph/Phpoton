<?php

class SubmitController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function indexAction() {
        $form = new Form_Submit();

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                // Process form data
                $this->_process($form, $data);

                // Render submit-view
                $this->render("submitted");
                return;
            }
            $form->populate($data);
        }

        $this->view->form = $form;
    }

    
    protected function _process(Zend_Form $form, array $data) {
        $values = $form->getValidValues($data);

        // Populate entity
        $question = new Model_Question_Entity();
        $question->setQuestion($values['question']);
        $question->setAnswer($values['answer']);
        $question->setAuthor($values['twitter']);
//        $question->setCreateDt(new Zend_Db_Expr('NOW()'));

        // Get mapper and save question
        $mapper = new Model_Question_Mapper();
        $mapper->save($question);
    }

}

