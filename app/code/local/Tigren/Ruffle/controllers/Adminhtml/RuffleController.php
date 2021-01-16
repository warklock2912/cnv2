<?php

/*
* Copyright (c) 2017 www.tigren.com
*/

class Tigren_Ruffle_Adminhtml_RuffleController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ruffle/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }
    /**
    * Acl checking
    *
    * @return bool
    */
    protected function _isAllowed()
    {
      return Mage::getSingleton('admin/session')->isAllowed('ruffle');
    }
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ruffle/ruffle')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('current_ruffle', $model);

            $this->loadLayout();
            $this->_setActiveMenu('ruffle/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('ruffle/adminhtml_ruffle_edit'))
                ->_addLeft($this->getLayout()->createBlock('ruffle/adminhtml_ruffle_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ruffle')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('ruffle/ruffle');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            if(isset($data['product_ids'])){
                $products = Mage::helper('core/string')->parseQueryStr($data['product_ids']);
                $quotaError = $this->checkQuota($products);
                if ($quotaError != 0) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ruffle')->__('General QTY and Vip QTY cannot greater than product QTY.'));
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));

                    return;
                }
                $model->setPostedProducts($products);
            }


            try {

                // $post = $this->getRequest()->getParams();
                // $resource = Mage::getSingleton('core/resource');
                // $writeConnection = $resource->getConnection('core_write');
                // $updateIsInvoice = "UPDATE ruffle SET is_invoice = ".$post['is_invoice']." WHERE ruffle_id =".$post['id'];
                // $writeConnection->query($updateIsInvoice);
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ruffle')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));

                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ruffle')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function checkQuota($products)
    {
        $quotaError = 0;
        foreach ($products as $productId => $value) {
            $qtyValue = base64_decode($value);
            $quota = Mage::helper('core/string')->parseQueryStr($qtyValue);
            $_product = Mage::getModel('catalog/product')->load($productId);
            $productQty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
            $ruffleQuota = (int)$quota['general_qty'] + (int)$quota['vip_qty'];
            if ($ruffleQuota > $productQty) {
                $quotaError++;
            }
        }

        return $quotaError;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('ruffle/ruffle');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ruffleIds = $this->getRequest()->getParam('ruffle');
        if (!is_array($ruffleIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ruffleIds as $ruffleId) {
                    $ruffle = Mage::getModel('ruffle/ruffle')->load($ruffleId);
                    $ruffle->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ruffleIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $ruffleIds = $this->getRequest()->getParam('ruffle');
        if (!is_array($ruffleIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ruffleIds as $ruffleId) {
                    $ruffle = Mage::getSingleton('ruffle/ruffle')
                        ->load($ruffleId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($ruffleIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportAllMemCsvAction()
    {
        $fileName = 'Registered_members.csv';
        $content = $this->getLayout()->createBlock('ruffle/adminhtml_ruffle_edit_tab_allmember')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportAllMemXmlAction()
    {
        $fileName = 'Registered_members.xml';
        $content = $this->getLayout()->createBlock('ruffle/adminhtml_ruffle_edit_tab_allmember')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportCsvAction()
    {
        $fileName = 'winner.csv';
        $content = $this->getLayout()->createBlock('ruffle/adminhtml_ruffle_edit_tab_winner')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'ruffle.xml';
        $content = $this->getLayout()->createBlock('ruffle/adminhtml_ruffle_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function productAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.items')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_items', null));
        $this->renderLayout();
    }

    public function productGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.items')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_items', null));
        $this->renderLayout();
    }

    public function memberAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.member');
        $this->renderLayout();
    }

    public function memberGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.member')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function vipAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.vip')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function vipGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.vip')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function allmemberAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.allmember');
        $this->renderLayout();
    }

    public function allmemberGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.allmember')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function winnerAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.winner');
        $this->renderLayout();
    }

    public function winnerGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.winner');
        $this->renderLayout();
    }

    public function randomMemberAction()
    {
        $ruffleId = $this->getRequest()->getParam('id');
        if ($ruffleId) {
            $winnerCount = 0;
            try {
                $winnerCollectionByGroup = Mage::getModel('ruffle/joiner')->getCollection();
                $winnerCollectionByGroup->addFieldToFilter('ruffle_id', $ruffleId)
                    ->addFieldToFilter('is_winner', 0);
                $winnerCollectionByGroup->getSelect()->group('product_options');
                if ($winnerCollectionByGroup->getData()) {
                    foreach ($winnerCollectionByGroup as $winnerByGroup) {
                        if ($winnerByGroup->getData('product_options')) {
                            //configurable product
                            $options = $winnerByGroup->getData('product_options');
                            $productId = $winnerByGroup->getProductId();
                            $childSelected = $this->getChildProduct($productId, $options);
                            if ($childSelected->getId()) {
                                $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($childSelected->getId(), $ruffleId);
                                $quotaQty = $quota->getGeneralQty();
                                $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollection($childSelected->getId(), $ruffleId, Tigren_Ruffle_Model_Ruffle::RUFFLE_GENERAL_GROUP_ID);
                                $usedQuota = count($usedQuotaCollection);
                                $remainQuota = $quotaQty - $usedQuota;
                                if ($remainQuota) {
                                    $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                    $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                        ->addFieldToFilter('product_options', $winnerByGroup->getData('product_options'))
                                        ->addFieldToFilter('is_winner', 0);
                                    $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                    foreach ($winnerCollection as $winner) {
                                        $winner->setIsWinner(1)
                                            ->save();
                                        $winnerCount++;
                                    }
                                } else {
                                    $this->_getSession()->addError(
                                        $this->__('There was a problem to choose winner for this ruffle. The quota is 0.')
                                    );
                                    $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                                }
                            }
                        } else {
                            //simple product
                            $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($winnerByGroup->getProductId(), $ruffleId);
                            $quotaQty = $quota->getGeneralQty();
                            $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollection($winnerByGroup->getProductId(), $ruffleId, Tigren_Ruffle_Model_Ruffle::RUFFLE_GENERAL_GROUP_ID);
                            $usedQuota = count($usedQuotaCollection);
                            $remainQuota = $quotaQty - $usedQuota;
                            if ($remainQuota) {
                                $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                    ->addFieldToFilter('product_id', $winnerByGroup->getProductId())
                                    ->addFieldToFilter('is_winner', 0);
                                $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                foreach ($winnerCollection as $winner) {
                                    $winner->setIsWinner(1)
                                        ->save();
                                    $winnerCount++;
                                }
                            } else {
                                $this->_getSession()->addError(
                                    $this->__('There was a problem to choose winner for this ruffle. The quota is 0.')
                                );
                                $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('There was a problem to choose winner for this ruffle. Please try again.')
                );
            }
            if ($winnerCount > 0) {
                $this->_getSession()->addSuccess($this->__('Successfully select %s winner for this ruffle from General group', $winnerCount));
            }
        }

        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }

    public function getChildProduct($parentProductId, $optionData)
    {
        $product = Mage::getModel('catalog/product')->load($parentProductId);
        $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(unserialize($optionData), $product);

        return $childProduct;
    }

    public function randomVipAction()
    {
        $ruffleId = $this->getRequest()->getParam('id');
        if ($ruffleId) {
            $winnerCount = 0;
            try {
                $winnerCollectionByGroup = Mage::getModel('ruffle/joiner')->getCollection();
                $winnerCollectionByGroup->addFieldToFilter('ruffle_id', $ruffleId)
                    ->addFieldToFilter('is_winner', 0);
                $winnerCollectionByGroup->getSelect()->group('product_options');
                if ($winnerCollectionByGroup->getData()) {
                    foreach ($winnerCollectionByGroup as $winnerByGroup) {
                        if ($winnerByGroup->getData('product_options')) {
                            //configurable product
                            $options = $winnerByGroup->getData('product_options');
                            $productId = $winnerByGroup->getProductId();
                            $childSelected = $this->getChildProduct($productId, $options);
                            if ($childSelected->getId()) {
                                $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($childSelected->getId(), $ruffleId);
                                $quotaQty = $quota->getVipQty();
                                $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollection($childSelected->getProductId(), $ruffleId, Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID);
                                $usedQuota = count($usedQuotaCollection);
                                $remainQuota = $quotaQty - $usedQuota;
                                if ($remainQuota) {
                                    $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                    $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                        ->addFieldToFilter('product_options', $winnerByGroup->getData('product_options'))
                                        ->addFieldToFilter('is_winner', 0);
                                    $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                    foreach ($winnerCollection as $winner) {
                                        $winner->setIsWinner(1)
                                            ->save();
                                        $winnerCount++;
                                    }
                                } else {
                                    $this->_getSession()->addError(
                                        $this->__('There was a problem to choose winner for this ruffle. The quota is 0.')
                                    );
                                    $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                                }
                            }
                        } else {
                            //simple product
                            $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($winnerByGroup->getProductId(), $ruffleId);
                            $quotaQty = $quota->getVipQty();
                            $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollection($winnerByGroup->getProductId(), $ruffleId, Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID);
                            $usedQuota = count($usedQuotaCollection);
                            $remainQuota = $quotaQty - $usedQuota;
                            if ($remainQuota) {
                                $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                    ->addFieldToFilter('product_id', $winnerByGroup->getProductId())
                                    ->addFieldToFilter('is_winner', 0);
                                $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                foreach ($winnerCollection as $winner) {
                                    $winner->setIsWinner(1)
                                        ->save();
                                    $winnerCount++;
                                }
                            } else {
                                $this->_getSession()->addError(
                                    $this->__('There was a problem to choose winner for this ruffle. The quota is 0.')
                                );
                                $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('There was a problem to choose winner for this ruffle. Please try again.')
                );
            }
            if ($winnerCount > 0) {
                $this->_getSession()->addSuccess($this->__('Successfully select %s winner for this ruffle from VIP group', $winnerCount));
            }
        }

        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }


    public function randomQuotaAction()
    {
        $ruffleId = $this->getRequest()->getParam('id');
        if ($ruffleId) {
            $winnerCount = 0;
            try {
                ///get all loser by admin set
                $loserCollection = Mage::getModel('ruffle/joiner')->getCollection();
                $loserCollection->addFieldToFilter('ruffle_id', $ruffleId)
                    ->addFieldToFilter('is_winner', 5);
                ////
                if($loserCollection->getData())
                {
                    foreach ($loserCollection as $loser)
                    {
                        $winnerCollectionByGroup = Mage::getModel('ruffle/joiner')->getCollection();

                        ///get not yet win user and have same product
                        $winnerCollectionByGroup->addFieldToFilter('ruffle_id', $ruffleId)
                            ->addFieldToFilter('is_winner', 0)
                            ->addFieldToFilter('product_id', $loser->getData('product_id'))
                            ->addFieldToFilter('product_options', $loser->getData('product_options'));
                        if ($winnerCollectionByGroup->getData()) {
                            foreach ($winnerCollectionByGroup as $winnerByGroup) {
                                if ($winnerByGroup->getData('product_options')) {
                                    //configurable product
                                    $options = $winnerByGroup->getData('product_options');
                                    $productId = $winnerByGroup->getProductId();

                                    $childSelected = $this->getChildProduct($productId, $options);
                                    if ($childSelected && $childSelected->getId()) {
                                        $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($childSelected->getId(), $ruffleId);
                                        $quotaQty = $quota->getVipQty() + $quota->getGeneralQty();
                                        $usedQuota = Mage::helper('ruffle')->countUsedQuotaByProductId($productId,$options, $ruffleId);
                                        $remainQuota = $quotaQty - $usedQuota;
                                        if ($remainQuota) {
                                            $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                            $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                                ->addFieldToFilter('product_options', $winnerByGroup->getData('product_options'))
                                                ->addFieldToFilter('is_winner', 0);
                                            $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                            foreach ($winnerCollection as $winner) {
                                                $winner->setIsWinner(1)
                                                    ->save();
                                                $winnerCount++;
                                            }
                                        } else {
                                            $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                                        }
                                    }
                                } else {
                                    //simple product
                                    $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($winnerByGroup->getProductId(), $ruffleId);
                                    $quotaQty = $quota->getVipQty() + $quota->getGeneralQty();
                                    $usedQuota = Mage::helper('ruffle')->countUsedQuotaByProductId($productId,$options, $ruffleId);
                                    $remainQuota = $quotaQty - $usedQuota;
                                    if ($remainQuota) {
                                        $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                        $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                            ->addFieldToFilter('product_id', $winnerByGroup->getProductId())
                                            ->addFieldToFilter('is_winner', 0);
                                        $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                        foreach ($winnerCollection as $winner) {
                                            $winner->setIsWinner(1)
                                                ->save();
                                            $winnerCount++;
                                        }
                                    } else {
                                        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                                    }
                                }
                            }
                        }

                    }
                }


            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('There was a problem to choose winner for this ruffle. Please try again.')
                );
            }
            if ($winnerCount > 0) {
                $this->_getSession()->addSuccess($this->__('Successfully select %s winner for this ruffle from ALL member', $winnerCount));
            }
        }
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }

    public function randomAllmemberAction()
    {
        $ruffleId = $this->getRequest()->getParam('id');
        if ($ruffleId) {
            $winnerCount = 0;
            try {
                $winnerCollectionByGroup = Mage::getModel('ruffle/joiner')->getCollection();
                $winnerCollectionByGroup->addFieldToFilter('ruffle_id', $ruffleId)
                    ->addFieldToFilter('is_winner', 0);
                $winnerCollectionByGroup->getSelect()->group('product_options');
                if ($winnerCollectionByGroup->getData()) {
                    foreach ($winnerCollectionByGroup as $winnerByGroup) {
                        if ($winnerByGroup->getData('product_options')) {
                            //configurable product
                            $options = $winnerByGroup->getData('product_options');
                            $productId = $winnerByGroup->getProductId();

                            $childSelected = $this->getChildProduct($productId, $options);
                            if ($childSelected && $childSelected->getId()) {
                                $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($childSelected->getId(), $ruffleId);
                                $quotaQty = $quota->getVipQty() + $quota->getGeneralQty();
                                $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollectionAll($childSelected->getProductId(), $ruffleId);
                                $usedQuota = count($usedQuotaCollection);
                                $remainQuota = $quotaQty - $usedQuota;
                                if ($remainQuota) {
                                    $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                    $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                        ->addFieldToFilter('product_options', $winnerByGroup->getData('product_options'))
                                        ->addFieldToFilter('is_winner', 0);
                                    $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                    foreach ($winnerCollection as $winner) {
                                        $winner->setIsWinner(1)
                                            ->save();
                                        $winnerCount++;
                                    }
                                } else {
                                    $this->_getSession()->addError(
                                        $this->__('There was a problem to choose winner for this ruffle. The quota is 0.')
                                    );
                                    $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                                }
                            }
                        } else {
                            //simple product
                            $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($winnerByGroup->getProductId(), $ruffleId);
                            $quotaQty = $quota->getVipQty() + $quota->getGeneralQty();
                            $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollectionAll($winnerByGroup->getProductId(), $ruffleId);
                            $usedQuota = count($usedQuotaCollection);
                            $remainQuota = $quotaQty - $usedQuota;
                            if ($remainQuota) {
                                $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
                                $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                                    ->addFieldToFilter('product_id', $winnerByGroup->getProductId())
                                    ->addFieldToFilter('is_winner', 0);
                                $winnerCollection->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($remainQuota);
                                foreach ($winnerCollection as $winner) {
                                    $winner->setIsWinner(1)
                                        ->save();
                                    $winnerCount++;
                                }
                            } else {
                                $this->_getSession()->addError(
                                    $this->__('There was a problem to choose winner for this ruffle. The quota is 0.')
                                );
                                $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('There was a problem to choose winner for this ruffle. Please try again.')
                );
            }
            if ($winnerCount > 0) {
                $this->_getSession()->addSuccess($this->__('Successfully select %s winner for this ruffle from ALL member', $winnerCount));
            }
        }
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }

    public function manualSelectAction()
    {
        $data = $this->getRequest()->getPost();
        // Zend_Debug::dump($data);
        $group = $this->getRequest()->getParam('group');
        if ($data && isset($data['ruffle_id'])) {
            $joinerIds = array();
            if ($group == 'general' && isset($data['general_ids'])) {
                $joinerIds = explode('&', $data['general_ids']);
            } else {
                if ($group == 'vip' && isset($data['vip_ids'])) {
                    $joinerIds = explode('&', $data['vip_ids']);
                }
            }

            if (!empty($joinerIds)) {
                $numberofWinners = $this->_assignWinner($joinerIds, $data['ruffle_id'], $group);


                $this->_getSession()->addSuccess(
                    $this->__('Total of %d member(s) were successfully assigned to winner', $numberofWinners)
                );
            }
        }
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getPost('ruffle_id')));
    }

    protected function _assignWinner($joinerIds, $ruffleId, $type)
    {
        $winners = 0;
        foreach ($joinerIds as $joinerId) {
            $joiner = Mage::getModel('ruffle/joiner')->load($joinerId);
            if ($joiner->getData('product_options')) {
                //configurable product
                $parentProduct = $joiner->getData('product_id');
                $options = $joiner->getData('product_options');
                $childSelected = $this->getChildProduct($parentProduct, $options);
                $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($childSelected->getId(), $ruffleId);
            } else {
                //simple product
                $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($joiner->getProductId(), $ruffleId);
            }
            if ($type == 'general') {
                $groupId = Tigren_Ruffle_Model_Ruffle::RUFFLE_GENERAL_GROUP_ID;
                $quotaQty = $quota->getGeneralQty();
            } else {
                $groupId = Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID;
                $quotaQty = $quota->getVipQty();
            }
            if ($joiner->getData('product_options')) {
                //configurable product
                $parentProduct = $joiner->getData('product_id');
                $options = $joiner->getData('product_options');
                $childSelected = $this->getChildProduct($parentProduct, $options);
                $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollection($childSelected->getId(), $ruffleId, $groupId);
                $usedQuota = count($usedQuotaCollection);
                $remainQuota = $quotaQty - $usedQuota;
            } else {
                //simple product
                $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollection($joiner->getProductId(), $ruffleId, $groupId);
                $usedQuota = count($usedQuotaCollection);
                $remainQuota = $quotaQty - $usedQuota;
            }
            if ($remainQuota >= 1) {
                // update winner status for joiner
                try {
                    $joiner->setIsWinner(1)
                        ->save();
                    $winners++;
                } catch (Exception $e) {
                    throw new Exception($e->getMessage());
                }
            }
        }

        return $winners;
    }

    public function emailToSelectedWinnerAction()
    {
        $data = $this->getRequest()->getPost();
        $winnerIds = explode('&', $data['winner_ids']);
        if (!empty($winnerIds)) {

            $sendEmail = Mage::getModel('ruffle/email')->sendEmailToWinners($winnerIds);
            $this->_getSession()->addSuccess(
                $this->__('Sent email successfully to %d winner(s).', $sendEmail)
            );
        }
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $this->getRequest()->getPost('ruffle_id')));
    }

    public function emailToAllWinnerAction()
    {
        $ruffleId = $this->getRequest()->getParam('id');
        $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
            ->addFieldToFilter('is_winner', 1)
            ->addFieldToFilter('send_email', ['null' => true]);
        $winnerIds = array();
        foreach ($winnerCollection as $winner) {
            $winnerIds[] = $winner->getJoinerId();
        }

        $sendEmail = Mage::getModel('ruffle/email')->sendEmailToWinners($winnerIds);
        $this->_getSession()->addSuccess(
            $this->__('Sent email successfully to %d winner(s).', $sendEmail)
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }

    public function emailToAllLooserAction()
    {
        $ruffleId = $this->getRequest()->getParam('id');
        $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
            ->addFieldToFilter('is_winner', 0)
            ->addFieldToFilter('send_email', [ 'null' => true ]);
        $winnerIds = array();
        foreach ($winnerCollection as $winner) {
            $winnerIds[] = $winner->getJoinerId();
        }

        $sendEmail = Mage::getModel('ruffle/email')->sendEmailToLoosers($winnerIds);
        $this->_getSession()->addSuccess(
            $this->__('Sent email successfully to %d loser(s).', $sendEmail)
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }

    public function clearWinnerAction()
    {
        $ruffleId = $this->getRequest()->getParam('id');
        $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)->addFieldToFilter('is_winner', 1);
        $qty_winner = 0;
        foreach ($winnerCollection as $winner) {
            $winner->setIsWinner(0)->save();
            $qty_winner++;
        }
        $this->_getSession()->addSuccess(
            $this->__('Clear All winner %d Qty.', $qty_winner)
        );
        $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }

    public function createOrderAction(){
        $customerId = $this->getRequest()->getParam('customer_id');
        $productId = $this->getRequest()->getParam('product_id');
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $defaultShippingAddress = Mage::getModel('customer/address')->load($customer->getDefaultShipping())->getData();
        $defaultBillingAddress = Mage::getModel('customer/address')->load($customer->getDefaultBilling())->getData();

        $store = Mage::app()->getStore();

        //Create sales quote object
        $quote = Mage::getModel('sales/quote')->setStoreId($store->getStoreId());


        $shippingAddress = array(
            'customer_address_id' => '',
            'prefix'              => $defaultShippingAddress['prefix'],
            'firstname'           => $defaultShippingAddress['firstname'],
            'middlename'          => '',
            'lastname'            => $defaultShippingAddress['lastname'],
            'suffix'              => '',
            'company'             => $defaultShippingAddress['company'],
            'street'              => $defaultShippingAddress['street'],
            'city'                => $defaultShippingAddress['city'],
            'country_id'          => $defaultShippingAddress['country_id'], // country code
            'region'              => $defaultShippingAddress['region'],
            'region_id'           => $defaultShippingAddress['region_id'],
            'postcode'            => $defaultShippingAddress['postcode'],
            'telephone'           => $defaultShippingAddress['telephone'],
            'fax'                 => $defaultShippingAddress['fax'],
            'save_in_address_book'=> 1
        );

        $billingAddress = array(
            'customer_address_id' => '',
            'prefix'              => $defaultBillingAddress['prefix'],
            'firstname'           => $defaultBillingAddress['firstname'],
            'middlename'          => '',
            'lastname'            => $defaultBillingAddress['lastname'],
            'suffix'              => '',
            'company'             => $defaultBillingAddress['company'],
            'street'              => $defaultBillingAddress['street'],
            'city'                => $defaultBillingAddress['city'],
            'country_id'          => $defaultBillingAddress['country_id'], // country code
            'region'              => $defaultBillingAddress['region'],
            'region_id'           => $defaultBillingAddress['region_id'],
            'postcode'            => $defaultBillingAddress['postcode'],
            'telephone'           => $defaultBillingAddress['telephone'],
            'fax'                 => $defaultBillingAddress['fax'],
            'save_in_address_book'=> 1
        );

        $quote->assignCustomer($customer);
        $quote->setCurrency(Mage::app()->getStore()->getBaseCurrencyCode());
        $product = Mage::getModel('catalog/product')->load($productId);
        $quote->addProduct($product, 1);
        //Add billing address to quote
        $billingAddressData = $quote->getBillingAddress()->addData($billingAddress);

        //Add shipping address to quote
        $shippingAddressData = $quote->getShippingAddress()->addData($shippingAddress);

        //Collect shipping rates on quote
        $shippingAddressData->setCollectShippingRates(true)->collectShippingRates();
        //Set shipping method and payment method on the quote
        $shippingAddressData->setShippingMethod('freeshipping_freeshipping')->setPaymentMethod('omise_gateway');

        //Set payment method for the quote
        $quote->getPayment()->importData(array('method' => 'omise_gateway'));

        try {
            //Collect totals & save quote
            $quote->collectTotals()->save();
            //Create order from quote
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $increment_id = $service->getOrder()->getRealOrderId();

            echo 'Order Id: ' .$increment_id. ' has been successfully created.';

            $this->_redirect('*/*');

        } catch (Exception $e) {
            Mage::logException($e);
        }

    }

    public function unassignWinnerAction()
    {
        $joiner = Mage::getModel('ruffle/joiner')->load($this->getRequest()->getParam('ruffle_joiner'));
        $joiner->setData('is_winner',5);
        $joiner->save();
        $this->_redirect('adminhtml/ruffle/edit', array('id' => $joiner->getData('ruffle_id')));

    }
}
