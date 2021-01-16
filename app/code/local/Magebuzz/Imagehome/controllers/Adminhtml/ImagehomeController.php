<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Imagehome_Adminhtml_ImagehomeController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('imagehome/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_redirect('*/*/edit', array('id' => 1));
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('imagehome/imagehome')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('imagehome_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('imagehome/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('imagehome/adminhtml_imagehome_edit'))
                    ->_addLeft($this->getLayout()->createBlock('imagehome/adminhtml_imagehome_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('imagehome')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        $data = $this->getRequest()->getParams();
        $id = $this->getRequest()->getParam('id');
        if ($data = $this->getRequest()->getPost()) {
            if (isset($_FILES['magebuzz_mage']['name']) && $_FILES['magebuzz_mage']['name'] != '') {
                $images = $_FILES['magebuzz_mage']['name'];
                $names = $images;
                foreach ($names as $key => $name) {
                    try {
                        $uploadInfo = array(
                            'name' => $_FILES['magebuzz_mage']['name'][$key],
                            'type' => $_FILES['magebuzz_mage']['type'][$key],
                            'tmp_name' => $_FILES['magebuzz_mage']['tmp_name'][$key],
                            'error' => $_FILES['magebuzz_mage']['error'][$key],
                            'size' => $_FILES['magebuzz_mage']['size'][$key]
                        );
                        $uploader = new Mage_Core_Model_File_Uploader($uploadInfo);
                        $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . 'magebuzz';
                        $name = $this->characterSpecial($name);
                        $newName = $name;
                        $result = $uploader->save($path, $newName);
                        $images[$key] = $newName;
                    } catch (Exception $e) {
                        $images[$key] = $name;
                    }
                }
            }
            $bun = '[' . $data['imagehome_grid'] . ']';
            $bun = json_decode($bun, true);
            $model_ = Mage::getModel('imagehome/imagehome')->load($id);
            if ($model_ && isset($model_)) {
                $image_ = $model_->getImagehomeGrid();
                $image_ = json_decode($image_, true);
                //custom block is on top and have no image count and move pass it
                $countCustomBlock=$this->countCustomBlock($image_,count($image_),$bun,$data);

                for ($i = 0; $i < count($images); $i++) {
                    if (!$images[$i]) {
                        $images[$i] = $image_[$i+$countCustomBlock]['image'];
                    }
                }
            }
            $html = $data['magebuzz_html'];
            for ($i = 0; $i < count($bun); $i++) {
                if($data['category_id'][$bun[$i]['index']] || $data['banner_id'][$bun[$i]['index']])
                {
                    $bun[$i]['image'] = '';

                }else
                {
                    $bun[$i]['image'] = $images[$i];
                }
                $bun[$i]['html'] = $html[$i];
                ////data for category
                $bun[$i]['category_name_attr'] = 'category_id[' . $bun[$i]['index'] . ']';
                $bun[$i]['category'] = $data['category_id'][$bun[$i]['index']];
                $bun[$i]['category_title'] = $data['category_title'][$bun[$i]['index']] ? $data['category_title'][$bun[$i]['index']] : '';
                $bun[$i]['category_title_name_attr'] =  'category_title[' . $bun[$i]['index'] . ']';
                $bun[$i]['category_url'] = $data['category_url'][$bun[$i]['index']] ? $data['category_url'][$bun[$i]['index']] : '';
                $bun[$i]['category_url_name_attr'] =  'category_url[' . $bun[$i]['index'] . ']';
                ////data for banner
                $bun[$i]['banner_name_attr'] = 'banner_id[' . $bun[$i]['index'] . ']';
                $bun[$i]['banner'] = $data['banner_id'][$bun[$i]['index']];
                $bun[$i]['banner_title'] = $data['banner_title'][$bun[$i]['index']] ? $data['banner_title'][$bun[$i]['index']] : '';
                $bun[$i]['banner_title_name_attr'] =  'banner_title[' . $bun[$i]['index'] . ']';
                $bun[$i]['banner_url'] = $data['banner_url'][$bun[$i]['index']] ? $data['banner_url'][$bun[$i]['index']] : '';
                $bun[$i]['banner_url_name_attr'] =  'banner_url[' . $bun[$i]['index'] . ']';

            }
            for ($i = 0; $i < count($bun); $i++) {
                if($bun[$i]['category'] !="" ||$bun[$i]['banner'] !="")
                {
                    $new_bun=$bun[$i];
                    unset($bun[$i]);
                    array_unshift($bun, $new_bun);
                    $new_image=$images[$i];
                    unset($images[$i]);
                    array_unshift($images, $new_image);
                }
            }
            $model = Mage::getModel('imagehome/imagehome');
            $data['imagehome_image'] = json_encode($images, true);

            $data['imagehome_grid'] = json_encode($bun, true);
          
            $model->setData('html',$html);
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('imagehome')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('imagehome')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }
    public function countCustomBlock($image_,$countImg,$bun,$data)
    {
         $numberOfCustomBlock=0;
        for ($i = 0; $i < count($bun); $i++)
        {
            if($data['banner_id'][$bun[$i]['index']]!= "" || $data['category_id'][$bun[$i]['index']] != "")
            {
                $numberOfCustomBlock++;
            }
        }
        $numberOfCustomBlockinDB=0;
        for ($i = 0; $i < $countImg; $i++) {
            if ($image_[$i]['category'] != "" ||$image_[$i]['banner'] != "") {
                $numberOfCustomBlockinDB++;
            }
        }
        $move=0;
        if($numberOfCustomBlock < $numberOfCustomBlockinDB)
        {
            $move=$numberOfCustomBlockinDB-$numberOfCustomBlock;
        }
        return $move;
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('imagehome/imagehome');

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
        $imagehomeIds = $this->getRequest()->getParam('imagehome');
        if (!is_array($imagehomeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($imagehomeIds as $imagehomeId) {
                    $imagehome = Mage::getModel('imagehome/imagehome')->load($imagehomeId);
                    $imagehome->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($imagehomeIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $imagehomeIds = $this->getRequest()->getParam('imagehome');
        if (!is_array($imagehomeIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($imagehomeIds as $imagehomeId) {
                    $imagehome = Mage::getSingleton('imagehome/imagehome')
                            ->load($imagehomeId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($imagehomeIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $fileName = 'imagehome.csv';
        $content = $this->getLayout()->createBlock('imagehome/adminhtml_imagehome_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'imagehome.xml';
        $content = $this->getLayout()->createBlock('imagehome/adminhtml_imagehome_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    /**
     * 
     * @param type $character
     * @return type
     */
    public function characterSpecial($character) {
        $character1 = array("ñ", " ", "à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă", "ằ", "ắ"
            , "ặ", "ẳ", "ẵ", "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề", "ế", "ệ", "ể", "ễ", "ì", "í", "ị", "ỉ", "ĩ",
            "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ"
            , "ờ", "ớ", "ợ", "ở", "ỡ",
            "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ",
            "ỳ", "ý", "ỵ", "ỷ", "ỹ",
            "đ",
            "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă"
            , "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ",
            "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ",
            "Ì", "Í", "Ị", "Ỉ", "Ĩ",
            "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ"
            , "Ờ", "Ớ", "Ợ", "Ở", "Ỡ",
            "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ",
            "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ",
            "Đ", "ê", "ù", "à");
        $character2 = array("n", "", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a"
            , "a", "a", "a", "a", "a", "a",
            "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e",
            "i", "i", "i", "i", "i",
            "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o"
            , "o", "o", "o", "o", "o",
            "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u",
            "y", "y", "y", "y", "y",
            "d",
            "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A"
            , "A", "A", "A", "A", "A",
            "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E",
            "I", "I", "I", "I", "I",
            "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O"
            , "O", "O", "O", "O", "O",
            "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U",
            "Y", "Y", "Y", "Y", "Y",
            "D", "e", "u", "a");
        return str_replace($character1, $character2, $character);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('imagehome');
    }
}
