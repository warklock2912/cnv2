<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Adminhtml_Mageworx_Searchsuite_SynonymsController extends Mage_Adminhtml_Controller_Action {

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

        $this->_title($this->__('Search Suite'))->_title($this->__('Manage Synonums'));
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
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('catalogsearch/query');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('This search no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        Mage::register('current_catalog_search', $model);
        $this->_initAction();
        $this->_title($model->getQueryText());
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);
        $this->getLayout()->getBlock('synonyms_edit')
                ->setData('action', $this->getUrl('*/*/save'));
        $this->_addBreadcrumb(($id) ? (Mage::helper('mageworx_searchsuite')->__('Edit Synonyms')) : (Mage::helper('mageworx_searchsuite')->__('Add new synonyms')));
        $this->renderLayout();
    }

    public function saveAction() {
        $hasError = false;
        $data = $this->getRequest()->getPost();
        $queryId = $this->getRequest()->getPost('query_id', null);

        $queryText = $this->getRequest()->getPost('query_text', null);
        if ($this->getRequest()->isPost() && $data && $queryText) {
            try {
                $queryModel = Mage::getModel('catalogsearch/query');
                $this->_initQueryModel($queryModel);
                if (!$queryModel->getId()) {
                    Mage::throwException(
                            Mage::helper('mageworx_searchsuite')->__('An error occurred saving synonyms.')
                    );
                }

                $t = explode(',', $this->getRequest()->getPost('synonyms', ''));
                $synonyms = array();
                foreach ($t as $word) {
                    $word = trim($word);
                    if (strlen($word) > 0 && $word != $queryModel->getQueryText()) {
                        $synonyms[] = $word;
                    }
                }


                $collection = Mage::getResourceModel('mageworx_searchsuite/synonym_collection');
                $collection->addFilter('query_id', $queryModel->getId());
                if (count($synonyms) == 0) {
                    $collection->delete();
                } else {
                    foreach ($synonyms as $word) {
                        $synonym = Mage::getResourceModel('mageworx_searchsuite/synonym_collection');
                        $synonym->addSynonymFilter($word, $queryModel->getStoreId());
                        if ($synonym->count() > 1) {
                            $synonym->delete();
                        }
                        $model = $synonym->getFirstItem();
                        if ($model->getId()) {
                            if ($model->getQueryId() != $queryModel->getId()) {
                                $model->setQueryId($queryModel->getId());
                                $model->save();
                            }
                        } else {
                            $model->setData(array('query_id' => $queryModel->getId(), 'synonym' => $word));
                            $model->save();
                        }
                    }
                    foreach ($collection as $item) {
                        if (!in_array($item->getSynonym(), $synonyms)) {
                            $item->delete();
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $hasError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('mageworx_searchsuite')->__('An error occurred saving synonyms.')
                );
                $hasError = true;
            }
        }

        if ($hasError) {
            $this->_getSession()->setPageData($data);
            $this->_redirect('*/*/edit', array('id' => $queryId));
        } else {
            $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Synonyms successfully saved'));
            $this->_redirect('*/*');
        }
    }
	
	public function deleteAction() {
        $hasError = false;
        $queryId = $this->getRequest()->getParam('id');
        if ($queryId) {
            try {
                $collection = Mage::getResourceModel('mageworx_searchsuite/synonym_collection');
                $collection->addFilter('query_id', $queryId);
                $collection->delete();
            } catch (Exception $ex) {
                $this->_getSession()->addException($ex, Mage::helper('mageworx_searchsuite')->__('An error occurred deleting synonyms.'));
                $hasError = true;
            }
        }
        if ($hasError) {
            $this->_redirect('*/*/edit', array('id' => $queryId));
        } else {
            $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Synonym successfully deleted'));
            $this->_redirect('*/*');
        }
    }
	
	public function massDeleteAction() {
        $ids = $this->getRequest()->getPost('synonym_ids', array());
        if (count($ids)) {
            $collection = Mage::getResourceModel('mageworx_searchsuite/synonym_collection');
            $collection->addFieldToFilter('query_id', array('in' => $ids));
            try {
                $collection->delete($ids);
                $this->_getSession()->addSuccess(Mage::helper('mageworx_searchsuite')->__('Synonym successfully deleted'));
            } catch (Exception $ex) {
                $this->_getSession()->addException($ex, Mage::helper('mageworx_searchsuite')->__('An error occurred while deleting the synonyms.')
                );
            }
        }
        $this->_redirect('*/*');
    }
	
    protected function _initQueryModel(Mage_CatalogSearch_Model_Query $queryModel) {
        $queryId = $this->getRequest()->getPost('query_id', null);
        $storId = $this->getRequest()->getPost('store_id', null);
        $queryText = $this->getRequest()->getPost('query_text', null);
        if ($queryId) {
            $queryModel->load($queryId);
            if ($queryModel->getId() == $queryId && $queryModel->getStoreId() == $storId && $queryModel->getQueryText() == $queryText) {
                return;
            }
            $queryModel->unsetData();
        } else {
            $queryModel->setStoreId($storId);
            $queryModel->loadByQueryText($queryText);
            if ($queryModel->getId()) {
                return;
            }
        }
        $queryModel->setStoreId($storId);
        $queryModel->setQueryText($queryText);
        $queryModel->save();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/mageworx_searchsuite/mageworx_searchsuite_synonyms');
    }

}
