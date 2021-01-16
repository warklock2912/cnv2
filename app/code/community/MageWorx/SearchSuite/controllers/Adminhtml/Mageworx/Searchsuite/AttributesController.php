<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Adminhtml_Mageworx_Searchsuite_AttributesController extends Mage_Adminhtml_Controller_Action {

    protected function _construct() {
        $this->setUsedModuleName('MageWorx_SearchSuite');
    }

    public function indexAction() {

        $this->_title($this->__('Search Suite'))->_title($this->__('Manage Attributes'));
        $this->loadLayout()
                ->_setActiveMenu('catalog')
                ->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction() {
        $post = $this->getRequest()->getPost();
        if ($this->getRequest()->isPost() && $post) {
            $data = array();
            $search = array();
            if ($post['is_attributes_search']) {
                $search = $post['is_attributes_search'];
            }
            if ($post['quick_search_priority']) {
                foreach ($post['quick_search_priority'] as $attr => $value) {
                    if (!isset($data[$attr])) {
                        $data[$attr] = array();
                    }
                    if ($value == 0) {
                        $data[$attr]['quick_search_priority'] = 5;
                        $data[$attr]['is_searchable'] = 0;
                    } else {
                        $data[$attr]['quick_search_priority'] = $value;
                        $data[$attr]['is_searchable'] = 1;
                    }
                    if ($search[$attr]) {
                        $data[$attr]['is_attributes_search'] = (string) filter_var($search[$attr], FILTER_VALIDATE_BOOLEAN);
                    } else {
                        $data[$attr]['is_attributes_search'] = 0;
                    }
                }
            }
            if (count($data)) {
                try {
                    foreach ($data as $attr => $attrItem) {
                        $model = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attr);
                        if ($model->getId()) {
                            $model->setData(array_merge($model->getData(), $attrItem));
                            $model->save();
                        }
                    }
                    $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Attributes have been updated.'));
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->addException($e, Mage::helper('mageworx_searchsuite')->__('An error occurred while saving attributes.'));
                }
            }
        }
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/mageworx_searchsuite');
    }

}
