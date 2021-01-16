<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Adminhtml_FormController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initAction();

        /** @var Amasty_Customform_Model_Form $form */
        $form = Mage::getModel('amcustomform/form');

        $id  = $this->getRequest()->getParam('id');
        if ($id) {
            $form->load($id);

            if (!$form->getId()) {
                $this->_getSession()->addError($this->__('This form is no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::register('amcustomform_current_form', $form);

        $title = $form->getId() ? $form->getTitle() : $this->__('New Form');
        $this->_title($title);

        $breadcrumb = $id ? $this->__('Edit Form') : $this->__('New Form');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $editBlock = $this->getLayout()->createBlock('amcustomform/adminhtml_form_edit');
        $this->_addContent($editBlock);

        $tabsBlock = $this->getLayout()->createBlock('amcustomform/adminhtml_form_edit_tabs');
        $this->_addLeft($tabsBlock);

        $this->renderLayout();
    }

    private function processingData(&$data){
        if(isset($data['success_url']) and !empty($data['success_url'])){
            $data['success_url'] = "/".trim($data['success_url'],"/");
        } else {
            $data['success_url'] = "/";
        }
    }

    public function saveAction()
    {
        $formId = $this->getRequest()->getParam('id');
        $redirectBack = !! $this->getRequest()->getParam('back', false);

        if ($postData = $this->getRequest()->getPost()) {
            try {
                $this->processingData($postData);
                /** @var Amasty_Customform_Model_Form $form */
                $form = Mage::getModel('amcustomform/form');
                $form->setData($postData);
                $form->realizeRelationData();

                $form->save();
                $formId = $form->getId();

                /** @var Amasty_Customform_Helper_Data $helper */
                $helper = Mage::helper('amcustomform');
                $helper->deleteDatedData();

                $this->_getSession()->addSuccess($this->__('The form has been saved.'));
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while saving this form.'));
                $redirectBack = true;
            }
        }

        if ($redirectBack && $formId) {
            $this->_redirect('*/*/edit', array(
                'id' => $formId,
                '_current' => true,
            ));
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setData('error', false);

        $id = $this->getRequest()->get('id');
        if (!$id) {
            $code = $this->getRequest()->get('code');

            $field = Mage::getModel('amcustomform/form');
            $field->load($code, 'code');

            if ($field->getId()) {
                $response->setData(array(
                    'error' => true,
                    'attribute' => 'code',
                    'message'   => $this->__('Form with same code already present in database. Please use an unique value.'),
                ));
            }
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $form = Mage::getModel('amcustomform/form');
        $form->load($id);

        if (!$form->getId()) {
            $this->_getSession()->addError($this->__('This form is no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();
        try {
            $form->delete();

            /** @var Amasty_Customform_Helper_Data $helper */
            $helper = Mage::helper('amcustomform');
            $helper->deleteDatedData();
            $connection->commit();

            $this->_getSession()->addSuccess($this->__('The form has been deleted.'));
        }
        catch (Mage_Core_Exception $e) {
            $connection->rollback();
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $connection->rollback();
            $this->_getSession()->addError($this->__('An error occurred while deleting this form.'));
        }

        $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $body = $this->getLayout()->createBlock('amcustomform/adminhtml_form_grid')->toHtml();
        $this->getResponse()->setBody($body);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/amcustomform_customforms/amcustomform_forms')
            ->_title($this->__('Custom Form'))->_title($this->__('Form Management'))
        ;
        $this
            ->_addBreadcrumb($this->__('Custom Form'), $this->__('Custom Form'))
            ->_addBreadcrumb($this->__('Forms Management'), $this->__('Form Management'))
        ;

        return $this;
    }
}