<?php

class Crystal_BlockSlide_Adminhtml_BlockslideController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()
			->_setActiveMenu('cms/blockslide')
			->_addBreadcrumb(
				Mage::helper('adminhtml')->__('Block Slide Manager'),
				Mage::helper('adminhtml')->__('Block Slide Manager')
			);
		return $this;
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	public function indexAction()
	{
		$this->_initAction()->renderLayout();
	}

	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('blockslide/blockslide')->load($id);
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('blockslide_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('cms/blockslide');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
			$this->_addContent($this->getLayout()->createBlock('blockslide/adminhtml_blockslide_edit'))
				->_addLeft($this->getLayout()->createBlock('blockslide/adminhtml_blockslide_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blockslide')->__('Item does not exist'));
			$this->_redirect('*/*/index');
		}
	}

	public function saveAction()
	{
		$model = Mage::getModel('blockslide/blockslide');
		if ($data = $this->getRequest()->getPost()) {
			//start store image
			if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
				try {
					//rename image in case image name has space
					$image_name = $_FILES['image']['name'];
					$new_image_name = Mage::helper('blockslide')->renameImage($image_name);

					$uploader = new Varien_File_Uploader('image');
					$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
					$uploader->setAllowRenameFiles(TRUE);
					$uploader->setFilesDispersion(FALSE);

					$path = Mage::getBaseDir('media') . DS . 'blockslide' . DS . 'images';
					if (!is_dir($path)) {
						mkdir($path, 0777, TRUE);
					}

					if (!file_exists($path . DS . $new_image_name)) {
						$uploader->save($path, $new_image_name);
					}
				} catch (Exception $e) {
					Mage::log($e->getMessage());
				}
				$data['image'] = $new_image_name;
			} else {
				if (isset($data['image']['delete']) && $data['image']['delete'] == 1) {
					$data['image'] = '';
				} else {
					unset($data['image']);
				}
			}
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			try {
//				$currentTime = Varien_Date::now();
//				if ($model->getCreatedAt == NULL) {
//					$model->setCreatedAt($currentTime);
//				} else {
//					$model->setUpdatedAt($currentTime);
//				}

				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('blockslide')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/index');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blockslide')->__('Unable to find item to save'));
		$this->_redirect('*/*/index/');
	}

	public function deleteAction()
	{
		$model = Mage::getModel('blockslide/blockslide');
		if ($this->getRequest()->getParam('id') > 0) {
			try {

				$model->setId($this->getRequest()->getParam('id'))
					->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/index/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/index');
	}


	public function massDeleteAction()
	{
		$slideIds = $this->getRequest()->getParam('blockslide');
		if (!is_array($slideIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		} else {
			try {
				foreach ($slideIds as $slideId) {
					$slide = Mage::getModel('blockslide/blockslide')
						->load($slideId);
					$slide->delete();
				}
				Mage::getSingleton('adminhtml/session')
					->addSuccess(Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($slideIds)));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

}