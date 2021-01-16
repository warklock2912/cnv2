<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Adminhtml_SubmitController extends Mage_Adminhtml_Controller_Action
{
    protected $_publicActions = array('view');

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function viewAction()
    {
        $this->_initAction();

        /** @var Amasty_Customform_Model_Form_Submit $submit */
        $submit = Mage::getModel('amcustomform/form_submit');

        $id  = $this->getRequest()->getParam('id');
        $submit->load($id);

        if (!$submit->getId()) {
            $this->_getSession()->addError($this->__('This submit is no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        Mage::register('amcustomform_current_submit', $submit);

        /** @var Amasty_Customform_Model_Form $form */
        $form = Mage::getModel('amcustomform/form');
        $form->load($submit->getFormId());

        $title = $form->getCode() . ' ' . $this->__('submitted data');
        $this->_title($title);

        $breadcrumb = $this->__('Submitted Data');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $submitIds = $this->getRequest()->getParam('submit_ids');
        if (!is_array($submitIds)) {
            $this->_getSession()->addError($this->__('Please select submit(s).'));
        } else {
            if (!empty($submitIds)) {
                try {
                    foreach ($submitIds as $submitId) {
                        $submit = Mage::getModel('amcustomform/form_submit')->load($submitId);
                        $submit->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($submitIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }


    public function gridAction()
    {
        $this->loadLayout();
        $body = $this->getLayout()->createBlock('amcustomform/adminhtml_submit_grid')->toHtml();
        $this->getResponse()->setBody($body);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/amcustomform_customforms/amcustomform_submits')
            ->_title($this->__('Form Data'))
        ;
        $this
            ->_addBreadcrumb($this->__('View Data'), $this->__('View Data'))
            ->_addBreadcrumb($this->__('Forms Management'), $this->__('Form Management'))
        ;

        return $this;
    }
}