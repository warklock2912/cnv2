<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Adminhtml_Mpblog_PostController extends Magpleasure_Blog_Controller_Adminhtml_Filterable {

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper() {
        return Mage::helper('mpblog');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed() {
        $aclRoute = 'cms/mpblog/posts';

        $this
                ->_getSession()
                ->setControlRoutePath($aclRoute)
        ;

        return Mage::getSingleton('admin/session')->isAllowed($aclRoute);
    }

    /**
     * Initialize layout prefer any action
     *
     * @return $this
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('cms/mpblog/posts')
                ->_addBreadcrumb($this->_helper()->__('Blog'), $this->_helper()->__('Blog'));
        return $this;
    }

    protected function _resetDraft() {
        $postId = $this->getRequest()->getParam('id');
        $user = $this->_helper()->getAdminSession()->getUser();
        if ($user) {
            $userId = $user->getId();
        }
        /** @var Magpleasure_Blog_Model_Draft $draft */
        $draft = Mage::getModel('mpblog/draft');

        if ($postId) {
            $draft->clearForPost($postId, $userId);
        } else {
            $draft->clearUnassigned($userId);
        }

        return $this;
    }

    public function indexAction() {
        $this->_prepareStoreFilter();

        $this->_initAction()
                ->renderLayout();
    }

    public function newAction() {
        $this->_prepareStoreFilter();

        $this->_forward('edit');
    }

    protected function _restoreFromDraft(Magpleasure_Blog_Model_Post $post) {
        # 1. Find draft
        $postId = $this->getRequest()->getParam('id');
        /** @var Mage_Admin_Model_User $user */
        $user = $this->_helper()->getAdminSession()->getUser();
        $userId = $user->getId();
        $draft = Mage::getModel('mpblog/draft');

        $keysToCheck = array(
            'full_content',
            'short_content'
        );

        if ($postId) {

            $draft->loadByPostAndUser($postId, $userId);
        } else {

            $draft->loadLatestUnassigned($userId);
        }

        if ($draft->getId()) {

            foreach ($keysToCheck as $key) {
                if ($draft->getData($key)) {

                    if ($draft->getData($key) !== $post->getData($key)) {
                        $post->setData($key, $draft->getData($key));
                    }
                }
            }

            # 2. Create message
            $draftId = $draft->getId();
            $rejectDraftUrl = $this->getUrl('*/*/rejectDraft', array(
                'id' => $draftId
            ));

            $messageText = $this->_helper()->__("Your content has been restored from the unsaved draft. You may reject it and return to the latest saved copy.");
            $link = "<a href=\"" . $rejectDraftUrl . "\">" . $this->_helper()->__("Reject") . "</a>";
            $actionText = $this->_helper()->__("%s the Draft or continue editing.", $link);

            $this->_getSession()->addNotice($messageText . " " . $actionText);
        }


        return $this;
    }

    public function rejectDraftAction() {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $draft = Mage::getModel('mpblog/draft');
            $draft->load($id);
            if ($draft->getId()) {
                $draft->delete();
            }
        }

        $this->_redirectReferer();
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        /** @var Magpleasure_Blog_Model_Post $post */
        $post = Mage::getModel('mpblog/post');
        if ($id) {
            $post->load($id);
        }

        if ($post->getId() || !$id) {
            Mage::register('current_post', $post);
            $data = Mage::getSingleton('adminhtml/session')->getPostData(true);

            if (!empty($data)) {
                $post->setData($data);
            }

            if ($post->getId() && $post->isHidden()) {
                $postClone = clone $post;

                $stores = $postClone->getStores();
                if ($stores && is_array($stores) && count($stores) && isset($stores[0])) {
                    $storeId = $stores[0];
                } else {
                    $storeId = Mage::app()->getDefaultStoreView()->getId();
                }

                $postClone->setStoreId($storeId);
                $postUrl = $postClone->getPostUrl();

                $this->_getSession()->addNotice($this->__("This Post is hidden but you can see it here - <a href='%s' target='_blank'>%s</a>.", $postUrl, $postUrl));
            }

            if ($post->getId()) {
                $post->processMediaTemplates();
                $post->processCutter();
            }

            $this->_restoreFromDraft($post);
        } else {
            $this->_getSession()->addError($this->_helper()->__('Post is not exists.'));
            $this->_redirect('*/*/index');
        }

        $this->loadLayout();

        $this->_setActiveMenu('cms/mpblog/posts');
        $this->renderLayout();
    }

    public function backAction() {
        $this->_resetDraft();
        $params = $this->_getCommonParams();
        $this->_redirect("*/*/index", $params);
    }

    public function resetAction() {
        $this->_resetDraft();
        $params = $this->_getCommonParams();
        if ($this->getRequest()->getParam('id')) {
            $params['id'] = $this->getRequest()->getParam('id');
            $this->_redirect('*/*/edit', $params);
        } else {
            $this->_redirect('*/*/new', $params);
        }
    }

    public function saveAction() {

        $this->_resetDraft();
        $requestPost = $this->getRequest()->getPost();

        /** @var Magpleasure_Blog_Model_Post $post */
        $post = Mage::getModel('mpblog/post');
        if ($id = $this->getRequest()->getParam('id')) {
            $post->load($id);
        }

        try {
            $post->addData($requestPost);

            # Process Timezone
            if (($post->getUserDefinePublish() || $post->getId() || $post->getId())) {
                $datetimeFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $publishedAt = new Zend_Date($post->getPublishedAt(), $datetimeFormat);
                $publishedAt->addSecond($this->_helper()->getTimezoneOffset());
                $post->setPublishedAt($publishedAt->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
            }

            $post->save();
            $post_id = $post->getData('post_id');
            if (isset($_FILES['magebuzz_input2']['name']) && $_FILES['magebuzz_input2']['name'] != '') {
                $images = $_FILES['magebuzz_input2']['name'];
                $this->saveImages($post_id, $images);
            }
            $this->_getSession()->addSuccess($this->_helper()->__("Post was successfully saved."));

            $params = $this->_getCommonParams();
            if ($this->getRequest()->getParam('back')) {
                $params['id'] = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : $post->getId();
                $params['tab'] = $this->getRequest()->getParam('tab');
                $this->_redirect('*/*/edit', $params);
            } else {
                $this->_redirect('*/*/index', $params);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->setPostData($requestPost);
            $this->_getSession()->addError($this->_helper()->__("Error while saving the post. %s", $e->getMessage()));
            $this->_redirectReferer();
        }
    }

    /**
     * Delete slide
     * @param int|string $id
     * @return boolean
     */
    protected function _delete($id) {
        $post = Mage::getModel('mpblog/post')->load($id);
        if ($post->getId()) {
            try {
                $post->delete();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Duplicate form
     * @param int|string $id
     * @return boolean
     */
    protected function _duplicate($id) {
        /** @var Magpleasure_Blog_Model_Post $post */
        $post = Mage::getModel('mpblog/post')->load($id);
        if ($post->getId()) {
            try {
                $newPost = $post->duplicate();
                return $newPost;
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    protected function _updateStatus($id, $status) {
        if ($id) {
            try {
                $post = Mage::getModel('mpblog/post')->load($id);
                $post->setStatus($status);
                $post->save();
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    public function massStatusAction() {
        $posts = $this->getRequest()->getPost('posts');
        $status = $this->getRequest()->getPost('status');
        if ($posts) {
            $success = 0;
            $error = 0;
            foreach ($posts as $postId) {
                if ($this->_updateStatus($postId, $status)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_getSession()->addSuccess($this->_helper()->__("%s posts were successfully updated.", $success));
            }
            if ($error) {
                $this->_getSession()->addError($this->_helper()->__("%s posts weren't updated.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction() {
        $posts = $this->getRequest()->getPost('posts');
        if ($posts) {
            $success = 0;
            $error = 0;
            foreach ($posts as $postId) {
                if ($this->_delete($postId)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_getSession()->addSuccess($this->_helper()->__("%s posts were successfully deleted.", $success));
            }
            if ($error) {
                $this->_getSession()->addError($this->_helper()->__("%s posts weren't deleted.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function massDuplicateAction() {
        $posts = $this->getRequest()->getPost('posts');
        if ($posts) {
            $success = 0;
            $error = 0;
            foreach ($posts as $postId) {
                if ($this->_duplicate($postId)) {
                    $success++;
                } else {
                    $error++;
                }
            }
            if ($success) {
                $this->_getSession()->addSuccess($this->_helper()->__("%s posts were successfully duplicated.", $success));
            }
            if ($error) {
                $this->_getSession()->addError($this->_helper()->__("%s posts weren't duplicated.", $error));
            }
        }
        $this->_redirectReferer();
    }

    public function duplicateAction() {
        $this->_resetDraft();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $newPost = $this->_duplicate($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Post was successfully duplicated."));

                $params = $this->_getCommonParams();
                $params['id'] = $newPost->getId();

                $this->_redirect('*/*/edit', $params);
            } catch (Exception $e) {

                $this->_getSession()->addError($this->_helper()->__("Post wasn't duplicated. %s", $e->getMessage()));
                $this->_helper()->getCommon()->getException()->logException($e);
                $this->_redirectReferer();

                return;
            }
        }
    }

    public function deleteAction() {
        $this->_resetDraft();

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_delete($id);
                $this->_getSession()->addSuccess($this->_helper()->__("Post was successfully deleted."));
            } catch (Exception $e) {
                $this->_getSession()->addError($this->_helper()->__("Post wasn't deleted (%s).", $e->getMessage()));
                $this->_redirectReferer();
                return;
            }
        }
        $params = $this->_getCommonParams();
        $this->_redirect('*/*/index', $params);
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function tagsAction() {
        if ($q = $this->getRequest()->getParam('q')) {
            /** @var Magpleasure_Blog_Model_Mysql4_Tag_Collection $collection */
            $collection = Mage::getModel('mpblog/tag')->getCollection();

            $collection
                    ->addFieldToFilter('name', array('like' => "%{$q}%"));

            if ($this->isStoreFilterApplied()) {
                $collection->addWieghtData($this->getAppliedStoreId());
            }

            if ($limit = $this->getRequest()->getParam('limit')) {
                $collection->setPageSize($limit);
            }

            foreach ($collection as $tag) {
                $key = $tag->getName();
                $name = $tag->getName();
                $this->getResponse()->setBody("{$name}|{$key}\n");
            }
        };
    }

    public function massUpdateAttributeAction() {
        $posts = $this->getRequest()->getPost('posts');
        if ($posts && is_array($posts)) {

            Mage::register(
                    Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Posts::REGISTRY_POSTS, $posts, true
            );

            $this
                    ->loadLayout()
                    ->_setActiveMenu('cms/mpblog/post')
                    ->renderLayout();
        } else {
            $this->_getSession()->addError($this->_helper()->__("Oops! There are no any posts to update."));
            $this->_redirectReferer();
        }

        return $this;
    }

    public function massUpdateAttributeGoAction() {
        $postIds = $this->getRequest()->getPost('post_ids');
        $data = $this->getRequest()->getPost();

        if ($postIds) {

            try {

                $attributeHelper = $this->_helper()->getAttribute();
                $result = $attributeHelper->apply($data, $postIds);

                if ($result['errors']) {

                    foreach ($result['errors'] as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                }

                if ($result['skip']) {

                    $this->_getSession()->addNotice(
                            $this->_helper()->__(
                                    "%s posts weren't changed. There are no changes required.", $result['skip']
                            )
                    );
                }

                if ($result['success']) {

                    $this->_getSession()->addSuccess(
                            $this->_helper()->__(
                                    "%s posts were successfully updated", $result['success']
                            )
                    );
                }



                if (Mage::getSingleton('adminhtml/session')->hasAttributeUpdateData()) {
                    Mage::getSingleton('adminhtml/session')->unsetAttributeUpdateData();
                }



                $this->_redirect('*/*/index', $this->_getCommonParams());
            } catch (Exception $e) {

                Mage::getSingleton('adminhtml/session')->setAttributeUpdateData($data);
                $this->_getSession()->addError(
                        $this->_helper()->__("Something went wrong. %s", $e->getMessage())
                );
                $this->_redirect('*/*/index', $this->_getCommonParams());
            }
        } else {

            $this->_getSession()->addError(
                    $this->_helper()->__("Something went wrong. There are no posts defined.")
            );
            $this->_redirect('*/*/index', $this->_getCommonParams());
        }

        return $this;
    }

    public function uploadImageAction() {
        $result = array();

        $imageProvider = $this->_helper()->getImagesProvider();
        $errorCodes = array(
            1 => $this->_helper()->__("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
            2 => $this->_helper()->__("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
            3 => $this->_helper()->__("The uploaded file was only partially uploaded"),
            4 => $this->_helper()->__("No file was uploaded"),
            6 => $this->_helper()->__("Missing a temporary folder"),
            7 => $this->_helper()->__("Failed to write file to disk"),
            8 => $this->_helper()->__("A PHP extension stopped the file upload"),
        );

        $fileType = strtolower($_FILES['file']['type']);
        $errorCode = strtolower($_FILES['file']['error']);
        $allowedFileTypes = array(
            'image/png',
            'image/jpg',
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
        );

        if ($errorCode) {
            $result['error'] = @$errorCodes[$errorCode];
        } elseif (in_array($fileType, $allowedFileTypes)) {

            $fileName = $imageProvider->getNewName($fileType);
            $movePath = $imageProvider->getFilePath($fileName);
            move_uploaded_file($_FILES['file']['tmp_name'], $movePath);
            $result['filelink'] = $imageProvider->getDestinationUrl($fileName);
        } else {
            $result['error'] = $this->_helper()->__("Files of this type are not allowed.");
        }

        $this->_ajaxResponse($result);
    }

    public function clipboardAction() {
        $result = array();
        $imageProvider = $this->_helper()->getImagesProvider();
        $fileName = $imageProvider->getNewName("image/jpg");

        if ($this->getRequest()->getPost('data')) {

            $movePath = $imageProvider->getFilePath($fileName);
            $data = base64_decode($this->getRequest()->getPost('data'));
            file_put_contents($movePath, $data);
            $result['filelink'] = $imageProvider->getDestinationUrl($fileName);
        } else {

            $result['error'] = $this->_helper()->__("Clipboard request is empty.");
        }

        $this->_ajaxResponse($result);
    }

    public function autosaveAction() {
        $result = array(
            'error' => false,
        );
        $postId = $this->getRequest()->getParam('id');
        $paramName = $this->getRequest()->getPost('name');
        $value = urldecode($this->getRequest()->getPost($paramName));

        /** @var Mage_Admin_Model_User $user */
        $user = $this->_helper()->getAdminSession()->getUser();
        if ($user->getId()) {

            $userId = $user->getId();

            if ($value) {

                try {

                    /** @var Magpleasure_Blog_Model_Draft $draft */
                    $draft = Mage::getModel('mpblog/draft');
                    if ($postId) {

                        $post = Mage::getModel('mpblog/post');
                        $post->load($postId);
                        if ($post->getId()) {

                            if ($post->getData($paramName) != $value) {

                                $draft->loadByPostAndUser($postId, $userId);
                                $draft->addData(array(
                                    'post_id' => $postId,
                                    'user_id' => $userId,
                                    $paramName => $value,
                                ))->save();
                            }
                        }
                    } else {

                        $draft->loadLatestUnassigned($userId);
                        $draft->addData(array(
                            'user_id' => $userId,
                            $paramName => $value,
                        ))->save();
                    }
                } catch (Exception $e) {
                    $result['error'] = true;
                    $result['message'] = $e->getMessage();
                }
            }
        } else {

            $result['error'] = true;
            $result['message'] = $this->_helper()->__("Session is expired.");
        }

        $this->_ajaxResponse($result);
    }

    /**
     * Prepare images for WYSIWYG editor
     *
     * @return void
     */
    public function imageListJsonAction() {
        $imageProvider = $this->_helper()->getImagesProvider();
        $this->_ajaxResponse($imageProvider->getImageList());
    }

    public function magentoLinksJsonAction() {
        $links = array(
            array(
                "name" => $this->_helper()->__("Base URL"),
                "url" => "{{store url=''}}"
            ),
            array(
                "name" => $this->_helper()->__("Contact Us"),
                "url" => "{{store url='contacts'}}"
            ),
            array(
                "name" => $this->_helper()->__("Sign In"),
                "url" => "{{store url='customer/account/login'}}"
            ),
            array(
                "name" => $this->_helper()->__("Sign Up"),
                "url" => "{{store url='customer/account/create'}}"
            ),
            array(
                "name" => $this->_helper()->__("Blog Pro"),
                "url" => $this->_helper()->_url(Mage::app()->getDefaultStoreView()->getId())->getUrl()
            ),
            array(
                "name" => $this->_helper()->__("Media URL"),
                "url" => "{{media url='image_name.jpg'}}"
            ),
            array(
                "name" => $this->_helper()->__("Custom Page"),
                "url" => "{{store url=''}}custom_page.html"
            ),
        );
        $this->_ajaxResponse($links);
    }

    public function saveImages($post_id, $images) {

        $names = $images;
        unset($names[sizeof($names) - 1]);
        foreach ($names as $key => $name) {
            try {
                $uploadInfo = array(
                    'name' => $_FILES['magebuzz_input2']['name'][$key],
                    'type' => $_FILES['magebuzz_input2']['type'][$key],
                    'tmp_name' => $_FILES['magebuzz_input2']['tmp_name'][$key],
                    'error' => $_FILES['magebuzz_input2']['error'][$key],
                    'size' => $_FILES['magebuzz_input2']['size'][$key]
                );
                $uploader = new Mage_Core_Model_File_Uploader($uploadInfo);
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $path = Mage::getBaseDir('media') . DS . 'magebuzz';
                $name = $this->characterSpecial($name);
                $name = preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', $name);;
                $newName = $name;
                $result = $uploader->save($path, $newName);
                $images[$key] = $newName;
            } catch (Exception $e) {
                $images[$key] = $name;
            }
        }
        try {
            for ($i = 0; $i < count($images) - 1; $i++) {
                $model = Mage::getModel('mpblog/blogimages');
                $data['post_id'] = $post_id;
                $data['images'] = $images[$i];
                $model->addData($data)->save();
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    /**
     *
     */
    public function removeimageAction() {
        $block = 0;
        $image_name = $this->getRequest()->getParam('value');
        $id = $this->getRequest()->getParam('id');
        $gifttemplate = Mage::getModel('mpblog/post')->load($id, 'post_id');

        $datas = Mage::getModel('mpblog/blogimages')->getCollection()->addFieldToFilter('images', array('finset' => $image_name));
        $model = Mage::getModel('mpblog/blogimages')->load($id);
        if(empty($image_name)) {
            $datasNull = Mage::getModel('mpblog/blogimages')->getCollection()
                ->addFieldToFilter('post_id', $id)
                ->addFieldToFilter('images', array('null' => true));
            foreach ($datasNull as $item) {
                $model->setId($item->getId())->delete();
            }
        }
        $dir_image = Mage::getBaseDir('media') . DS . 'magebuzz' . DS . $image_name;
        if (file_exists($dir_image))
            $image = Mage::getBaseDir('media'). 'magebuzz' . $image_name;
        try {
            foreach ($datas as $data) {
                $model->setId($data->getId())->delete();
            }
        } catch (Exception $exc) {

        }
        Mage::helper('mpblog')->deleteTemplateImageFile($image);
        $result['html'] = $block;
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
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

    public function resendNotificationAction(){
        $id = $this->getRequest()->getParam('id');
        $post = Mage::getModel('mpblog/post')->load($id);
        try {
            $now = new Zend_Date();
            if ($post->getPublishedAt() >= $now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT) && $post->getStatus() == true){
                $customerIdsArr = array();
                $categories = $post->getCategories();
                foreach ($categories as $categoryId):;
                    $newsNotificationList = Mage::getModel('newsnotification/newsnotification')->getCollection()->addFieldToFilter('category_id', $categoryId);
                    foreach ($newsNotificationList as $item) {
                        if (!in_array($item['customer_id'], $customerIdsArr)) {
                            $customerIdsArr[] = $item['customer_id'];
                        }
                    }
                endforeach;
                $data = array(
                    "type" => '1',
                    'content_id' => '' . $post->getId(),
                    'id' => '' . $post->getId(),
                );
                Mage::helper('pushnotification')->sendAction($customerIdsArr, $post->getTitle(), $post->getShortContent(),null,$data );
                echo  $this->_helper()->__("Success");
            } else {
                echo "Cannot resend notification because status or published date wrong.";
            }
        } catch (Exception $e) {
            echo  $this->_helper()->__("Error while resend notification. %s", $e->getMessage());
        }

    }
}
