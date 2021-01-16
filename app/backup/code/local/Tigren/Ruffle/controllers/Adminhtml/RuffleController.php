<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Adminhtml_RuffleController extends Mage_Adminhtml_Controller_Action {
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('ruffle/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('ruffle/ruffle')->load($id);

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
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('ruffle/ruffle');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			if($data['product_ids'] === NULL){
        $products = NULL;
      }else{
        $products = Mage::helper('core/string')->parseQueryStr($data['product_ids']);
        $quotaError = $this->checkQuota($products);
        if ($quotaError != 0) {
          Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ruffle')->__('General QTY and Vip QTY cannot greater than product QTY.'));
          $this->_redirect('*/*/edit', array('id' => $model->getId()));
          return;
        }
      }
      
			$model->setPostedProducts($products);
			try {				
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

  public function checkQuota($products){
    $quotaError = 0;
      foreach ($products as $productId => $value) {
        $qtyValue = base64_decode($value);
        $quota = Mage::helper('core/string')->parseQueryStr($qtyValue);
        $_product = Mage::getModel('catalog/product')->load($productId);
        $productQty = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
        $ruffleQuota = (int)$quota['general_qty'] + (int)$quota['vip_qty'];
        if($ruffleQuota > $productQty){ 
          $quotaError ++;
        }
      }

      return $quotaError;
  }
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
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

	public function massDeleteAction() {
		$ruffleIds = $this->getRequest()->getParam('ruffle');
		if(!is_array($ruffleIds)) {
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
	
	public function massStatusAction() {
		$ruffleIds = $this->getRequest()->getParam('ruffle');
		if(!is_array($ruffleIds)) {
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
  
	public function exportCsvAction() {
		$fileName   = 'winner.csv';
		$content    = $this->getLayout()->createBlock('ruffle/adminhtml_ruffle_edit_tab_winner')
			->getCsv();

		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportXmlAction() {
		$fileName   = 'ruffle.xml';
		$content    = $this->getLayout()->createBlock('ruffle/adminhtml_ruffle_grid')
			->getXml();

		$this->_sendUploadResponse($fileName, $content);
	}

	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK','');
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

	public function productAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.items')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_items', null));
        $this->renderLayout();
    }

    public function productGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.items')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_items', null));
        $this->renderLayout();
    }

    public function memberAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.member');
        $this->renderLayout();
    }

    public function memberGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.member')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function vipAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.vip')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function vipGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.vip')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function allmemberAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.allmember');
        $this->renderLayout();
    }

    public function allmemberGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.allmember')
            ->setRuffleItems($this->getRequest()->getPost('ruffle_member', null));
        $this->renderLayout();
    }

    public function winnerAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.winner');
        $this->renderLayout();
    }

    public function winnerGridAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('ruffle.edit.tab.winner');
        $this->renderLayout();
    }

    public function randomMemberAction() {
    	$ruffleId = $this->getRequest()->getParam('id');
    	if ($ruffleId) {
    		$winnerCount = 0;
			try {
        $winnerCollectionByGroup = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollectionByGroup->addFieldToFilter('ruffle_id', $ruffleId)
          ->addFieldToFilter('is_winner', 0);
        $winnerCollectionByGroup->getSelect()->group('product_options');
        if($winnerCollectionByGroup->getData()){
          foreach ($winnerCollectionByGroup as $winnerByGroup){
            if($winnerByGroup->getData('product_options')){
              //configurable product
              $options = $winnerByGroup->getData('product_options');
              $productId = $winnerByGroup->getProductId();
              $childSelected = $this->getChildProduct($productId,$options);
              if($childSelected->getId()){
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
            }else{
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
			}
			catch (Exception $e) {
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

  public function getChildProduct($parentProductId,$optionData)
  {
    $product = Mage::getModel('catalog/product')->load($parentProductId);
    $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(unserialize($optionData), $product);
    return $childProduct;
  }

    public function randomVipAction() {
    	$ruffleId = $this->getRequest()->getParam('id');
    	if ($ruffleId) {
    		$winnerCount = 0;
			try {
        $winnerCollectionByGroup = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollectionByGroup->addFieldToFilter('ruffle_id', $ruffleId)
          ->addFieldToFilter('is_winner', 0);
        $winnerCollectionByGroup->getSelect()->group('product_options');
        if($winnerCollectionByGroup->getData()){
          foreach ($winnerCollectionByGroup as $winnerByGroup){
            if($winnerByGroup->getData('product_options')){
              //configurable product
              $options = $winnerByGroup->getData('product_options');
              $productId = $winnerByGroup->getProductId();
              $childSelected = $this->getChildProduct($productId,$options);
              if($childSelected->getId()){
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
            }else{
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
			}
			catch (Exception $e) {
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

    public function randomAllmemberAction() {
      $ruffleId = $this->getRequest()->getParam('id');
      if ($ruffleId) {
        $winnerCount = 0;
      try {
        $winnerCollectionByGroup = Mage::getModel('ruffle/joiner')->getCollection();
        $winnerCollectionByGroup->addFieldToFilter('ruffle_id', $ruffleId)
          ->addFieldToFilter('is_winner', 0);
        $winnerCollectionByGroup->getSelect()->group('product_options');
        if($winnerCollectionByGroup->getData()){
          foreach ($winnerCollectionByGroup as $winnerByGroup){
            if($winnerByGroup->getData('product_options')){
              //configurable product
              $options = $winnerByGroup->getData('product_options');
              $productId = $winnerByGroup->getProductId();
              $childSelected = $this->getChildProduct($productId,$options);
              if($childSelected->getId()){
                $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($childSelected->getId(), $ruffleId);
                $quotaQty = $quota->getVipQty()+$quota->getGeneralQty();
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
            }else{
              //simple product
              $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($winnerByGroup->getProductId(), $ruffleId);
              $quotaQty = $quota->getVipQty()+$quota->getGeneralQty();
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
      }
      catch (Exception $e) {
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

    public function manualSelectAction() {
    	$data = $this->getRequest()->getPost(); 
    	// Zend_Debug::dump($data);
    	$group = $this->getRequest()->getParam('group');
    	if ($data && isset($data['ruffle_id'])) {
    		$joinerIds = array();
    		if ($group == 'general' && isset($data['general_ids'])) {
    			$joinerIds = explode('&', $data['general_ids']);
    		}
    		else if ($group == 'vip' && isset($data['vip_ids'])) {
    			$joinerIds = explode('&', $data['vip_ids']);
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

    protected function _assignWinner($joinerIds, $ruffleId, $type) {
    	$winners = 0;
    	foreach ($joinerIds as $joinerId) {
    		$joiner = Mage::getModel('ruffle/joiner')->load($joinerId);
        if($joiner->getData('product_options')){
          //configurable product
          $parentProduct = $joiner->getData('product_id');
          $options = $joiner->getData('product_options');
          $childSelected = $this->getChildProduct($parentProduct, $options);
          $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($childSelected->getId(), $ruffleId);
        }else{
          //simple product
    		  $quota = Mage::helper('ruffle')->getWinnerQuotaByProductId($joiner->getProductId(), $ruffleId);
        }
    		if ($type == 'general') {
				$groupId = Tigren_Ruffle_Model_Ruffle::RUFFLE_GENERAL_GROUP_ID;
				$quotaQty = $quota->getGeneralQty();
    		}
    		else {
    			$groupId = Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID;
    			$quotaQty = $quota->getVipQty();
    		}
        if($joiner->getData('product_options')){
          //configurable product
          $parentProduct = $joiner->getData('product_id');
          $options = $joiner->getData('product_options');
          $childSelected = $this->getChildProduct($parentProduct, $options);
          $usedQuotaCollection = Mage::helper('ruffle')->getUsedQuotaCollection($childSelected->getId(), $ruffleId, $groupId);
          $usedQuota = count($usedQuotaCollection);
          $remainQuota = $quotaQty - $usedQuota;
        }else{
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
    			}
    			catch (Exception $e) {
    				throw new Exception($e->getMessage());
    			}
    		}
    	}
    	return $winners;
    }

    public function emailToSelectedWinnerAction(){
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

    public function emailToAllWinnerAction(){
      $ruffleId = $this->getRequest()->getParam('id');
      $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
      $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                  ->addFieldToFilter('is_winner', 1);
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
    public function emailToAllLooserAction(){
      $ruffleId = $this->getRequest()->getParam('id');
      $winnerCollection = Mage::getModel('ruffle/joiner')->getCollection();
      $winnerCollection->addFieldToFilter('ruffle_id', $ruffleId)
                  ->addFieldToFilter('is_winner', 0);
      $winnerIds = array();
      foreach ($winnerCollection as $winner) {
        $winnerIds[] = $winner->getJoinerId();
      }

      $sendEmail = Mage::getModel('ruffle/email')->sendEmailToLoosers($winnerIds);
      $this->_getSession()->addSuccess(
          $this->__('Sent email successfully to %d looser(s).', $sendEmail)
        );
      $this->_redirect('*/*/edit', array('_secure' => true, 'id' => $ruffleId));
    }
    public function clearWinnerAction(){
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
}