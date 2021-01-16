<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Adminhtml_Mageworx_Searchsuite_StopwordsController extends Mage_Adminhtml_Controller_Action {

    protected function _construct() {
        $this->setUsedModuleName('MageWorx_SearchSuite');
    }

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('catalog/mageworx_searchsuite')
                ->_addBreadcrumb(Mage::helper('mageworx_searchsuite')->__('Search Suite'), Mage::helper('mageworx_searchsuite')->__('Search Suite'));
        return $this;
    }

    public function indexAction() {

        $this->_title(Mage::helper('mageworx_searchsuite')->__('Search Suite'))->_title(Mage::helper('mageworx_searchsuite')->__('Manage Stopwords'));
        $this->loadLayout()
                ->_setActiveMenu('catalog')
                ->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $this->_title($this->__('Catalog'))->_title(Mage::helper('mageworx_searchsuite')->__('Stopword'));
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('mageworx_searchsuite/stopword');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mageworx_searchsuite')->__('This stopword no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        Mage::register('current_stopword', $model);
        $this->_initAction();
        $this->_title($id ? $model->getWord() : Mage::helper('mageworx_searchsuite')->__('New Stopword'));
        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);
        $this->getLayout()->getBlock('stopwords_edit')->setData('action', $this->getUrl('*/*/save'));
        $this->_addBreadcrumb($id ? Mage::helper('catalog')->__('Edit Stopword') : Mage::helper('catalog')->__('New Stopword'), $id ? Mage::helper('catalog')->__('Edit Stopword') : Mage::helper('catalog')->__('New Stopword'));
        $this->renderLayout();
    }

    public function saveAction() {
        $hasError = false;
        $data = $this->getRequest()->getPost();
        $id = $this->getRequest()->getPost('id', null);
        if ($this->getRequest()->isPost() && $data) {
            $model = Mage::getModel('mageworx_searchsuite/stopword');
            $stopword = $this->getRequest()->getPost('word', false);
            $storeId = $this->getRequest()->getPost('store_id', false);
            try {
                if ($queryText) {
                    $model->setStoreId($storeId);
                    $model->loadByWord($stopword);
                    if ($model->getId() && $model->getId() != $id) {
                        Mage::throwException(
                                Mage::helper('mageworx_searchsuite')->__('Stopword with such word already exists.')
                        );
                    } else if (!$model->getId() && $id) {
                        $model->load($id);
                    }
                } else if ($id) {
                    $model->load($id);
                }
                $model->addData($data);
                $model->save();
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $hasError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('mageworx_searchsuite')->__('An error occurred while saving the stopword.')
                );
                $hasError = true;
            }
        }

        if ($hasError) {
            $this->_getSession()->setPageData($data);
            $this->_redirect('*/*/edit', array('id' => $id));
        } else {
            $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Stopword successfully saved'));
            $this->_redirect('*/*');
        }
    }

    public function deleteAction() {
        $hasError = false;
        $stopwordId = $this->getRequest()->getParam('id', null);
        if ($stopwordId) {
            $model = Mage::getModel('mageworx_searchsuite/stopword')->load($stopwordId);
            if ($model->getId() == $stopwordId) {
                try {
                    $model->delete();
                } catch (Exception $ex) {
                    $this->_getSession()->addException($ex, Mage::helper('mageworx_searchsuite')->__('An error occurred deleting stopword.'));
                    $hasError = true;
                }
            }
        }
        if ($hasError) {
            $this->_redirect('*/*/edit', array('id' => $stopwordId));
        } else {
            $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Stopword successfully deleted'));
            $this->_redirect('*/*');
        }
    }

    public function massDeleteAction() {
        $ids = $this->getRequest()->getPost('stopword_ids', array());
        if (count($ids)) {
            $collection = Mage::getResourceModel('mageworx_searchsuite/stopword_collection');
            $collection->addFieldToFilter('id', array('in' => $ids));
            try {
                $collection->delete($ids);
                $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Stopword successfully deleted'));
            } catch (Exception $ex) {
                $this->_getSession()->addException($ex, Mage::helper('mageworx_searchsuite')->__('An error occurred while deleting the stopwords.')
                );
            }
        }
        $this->_redirect('*/*');
    }

    public function importAction() {
        $this->_title($this->__('Catalog'))->_title(Mage::helper('mageworx_searchsuite')->__('Stopword'));
        $this->_initAction();
        $this->_title(Mage::helper('mageworx_searchsuite')->__('Import Stopwords'));
        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);
        $this->getLayout()->getBlock('stopwords_import')->setData('action', $this->getUrl('*/*/upload'));
        $this->_addBreadcrumb(Mage::helper('catalog')->__('Import Stopwords'));
        $maxUploadSize = Mage::helper('importexport')->getMaxUploadSize();
        $this->_getSession()->addNotice(
                Mage::helper('importexport')->__('Total size of uploadable files must not exceed %s', $maxUploadSize)
        );
        $this->renderLayout();
    }

    public function uploadAction() {
        $info = $_FILES['file'];
        $hasError = false;
        $storeId = $this->getRequest()->getPost('store_id', null);
        if ($info && $info['error'] == 0 && $info['size'] > 0 && $storeId) {
            try {
                $h = fopen($info['tmp_name'], 'r');
                $content = fread($h, $info['size']);
                fclose($h);
                $words = explode("\n", $content);
                $model = Mage::getModel('mageworx_searchsuite/stopword');
                $model->import($storeId, $words);
            } catch (Exception $ex) {
                $this->_getSession()->addException($ex, Mage::helper('mageworx_searchsuite')->__('An error occurred while importing the stopwords.')
                );
            }
        } else {
            $this->_getSession()->addError(Mage::helper('mageworx_searchsuite')->__('The file is empty or has the wrong format.'));
        }
        if ($hasError) {
            $this->_redirect('*/*/import');
        } else {
            $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Stopwords successfully imported'));
            $this->_redirect('*/*/');
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/mageworx_searchsuite/mageworx_searchsuite_stopwords');
    }

}
