<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

class Amasty_Customform_Adminhtml_FieldController extends Mage_Adminhtml_Controller_Action
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

        /** @var Amasty_Customform_Model_Field $field */
        $field = Mage::getModel('amcustomform/field');

        $id  = $this->getRequest()->getParam('id');
        if ($id) {
            $field->load($id);

            if (!$field->getId()) {
                $this->_getSession()->addError($this->__('This field is no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }
        $frontendClass = $field->getFrontendClass();
        if($frontendClass){
            $field->setFrontendClass(explode(' ',$frontendClass));
        }
        Mage::register('amcustomform_current_field', $field);

        $title = $field->getId() ? $field->getLabel() : $this->__('New Field');
        $this->_title($title);

        $breadcrumb = $id ? $this->__('Edit Field') : $this->__('New Field');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);

        $editBlock = $this->getLayout()->createBlock('amcustomform/adminhtml_field_edit');
        $editBlock->setData('action', $this->getUrl('*/*/save'));
        $this->_addContent($editBlock);

        $tabsBlock = $this->getLayout()->createBlock('amcustomform/adminhtml_field_edit_tabs');
        $this->_addLeft($tabsBlock);

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $redirectBack = !! $this->getRequest()->getParam('back', false);
            if(isset($postData['frontend_class']) && is_array($postData['frontend_class']) && !empty($postData['frontend_class'])){
                $postData['frontend_class'] = implode(' ',$postData['frontend_class']);
            }else{
                $postData['frontend_class'] = '';
            }
            /** @var Amasty_Customform_Model_Field $field */
            $field = Mage::getModel('amcustomform/field');
            $id = isset($postData['id']) ? $postData['id'] : null;
            if($id){
                $field->load($id);
                $inputType = $field->getInputType();
            }else{

                $inputType = isset($postData['input_type']) ? $postData['input_type'] : '';
            }
            $defaultValueField = $field->getDefaultValueByInput(
                $inputType
            );
            if ($defaultValueField) {
                $postData['default_value'] = isset($postData[$defaultValueField]) ? $postData[$defaultValueField] : '';
            }

            $field->setData($postData);
            $field->realizeRelationData();

            try {
                $field->save();
                $fieldId = $field->getId();
                $this->_getSession()->addSuccess($this->__('The field has been saved.'));
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('An error occurred while saving this field.'));
            }
            if ($redirectBack && $fieldId) {
                $this->_redirect('*/*/edit', array(
                    'id' => $fieldId,
                    '_current' => true,
                ));
            } else {
                $this->_redirect('*/*/');
            }

        }
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setData('error', false);

        $id = $this->getRequest()->get('id');
        if (!$id) {
            $code = $this->getRequest()->get('code');

            $field = Mage::getModel('amcustomform/field');
            $field->load($code, 'code');

            if ($field->getId()) {
                $response->setData(array(
                    'error' => true,
                    'attribute' => 'code',
                    'message'   => $this->__('Field with same code already present in database. Please use an unique value.'),
                ));
            }
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var Amasty_Customform_Model_Field $field */
        $field = Mage::getModel('amcustomform/field');
        $field->load($id);

        if (!$field->getId()) {
            $this->_getSession()->addError($this->__('This field is no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        try {
            $connection->beginTransaction();
            $field->setIsDeleted(1);
            $field->save();

            $this->_deleteDatedData();

            $connection->commit();

            $this->_getSession()->addSuccess($this->__('The field has been deleted.'));
        }
        catch (Mage_Core_Exception $e) {
            $connection->rollback();
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $connection->rollback();
            $this->_getSession()->addError($this->__('An error occurred while deleting this field.'));
        }

        $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $body = $this->getLayout()->createBlock('amcustomform/adminhtml_field_grid')->toHtml();
        $this->getResponse()->setBody($body);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/amcustomform_customforms/amcustomform_fields')
            ->_title($this->__('Custom Form'))->_title($this->__('Field Management'))
        ;
        $this
            ->_addBreadcrumb($this->__('Custom Form'), $this->__('Custom Form'))
            ->_addBreadcrumb($this->__('Field Management'), $this->__('Field Management'))
        ;

        return $this;
    }

    protected function _deleteDatedData()
    {
        /** @var Amasty_Customform_Helper_Data $helper */
        $helper = Mage::helper('amcustomform');
        $helper->deleteDatedData();
    }
}