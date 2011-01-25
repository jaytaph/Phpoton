<?php

class SubmitController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->addFilter('TwitterLink');
    }

    public function init()
    {
        $this->_helper->layout()->getView()->headTitle('Submit your question to @PHPoton');
    }

    public function indexAction() {
        $this->_helper->layout()->getView()->headScript()->appendFile('/js/textlimit.js');
        
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
        $question->setFullname($values['fullname']);
        // @TODO: If twitter name is set, we must fetch the twitter ID
        $question->setTwitterId(0);
        $question->setCreateDt(new Zend_Db_Expr("NOW()"));
        $question->setStatus("moderation");

        // Get mapper and save question
        $mapper = new Model_Question_Mapper();
        $mapper->save($question);
    }

}

