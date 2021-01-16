<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 10/08/2018
 * Time: 14:18
 */

require_once '../app/Mage.php';
require_once 'functions.php';

try {
    $cpBlock = Mage::app()->getLayout()->getBlockSingleton('Magpleasure_Blog_Block_Content_Post');

    $post = $cpBlock->getPost();
    $data = array();
    if ($post->getId()) {
        $categories = $cpBlock->getCategories($post->getId());
        $categoryList = null;
        foreach ($categories as $category):
            $categoryList[] = $category->getName();
        endforeach;
        $data['post_category'] = $categoryList;
        $data['post_name'] = $cpBlock->escapeHtml($post->getTitle());
        $blogimages = Mage::getModel('mpblog/blogimages')->getCollection()->addFieldToFilter('post_id', $post->getPostId());
        if ($blogimages->getSize()) {
            foreach ($blogimages as $blogimage) {
                $data['post_image'][] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'magebuzz' . DS . $blogimage->getImages();
            }
        } else {
            $data['post_image'] = null;
        }
        $data['post_created'] = strtotime($post->getPublishedAt()) . '';
        $data['post_by'] = $post->getPostedBy();
        $data['post_view'] = $post->getViews();
        $data['post_url'] = $post->getPostUrl();
        $data['post_sort_content'] = $post->getShortContent();
        $data['post_full_content'] = $post->getFullContent();
        //var_dump($data);
        //get recent post
        $recentPostBlock = Mage::app()->getLayout()->getBlockSingleton('Magpleasure_Blog_Block_Sidebar_Recentpost');
        $dataRecentPost = array();
        $dataArr = array();
        if(count($recentPostBlock->getCollection())){
            foreach ($recentPostBlock->getCollection() as $postRecent): $i++;
                $dataRecentPost['post_id'] = $postRecent->getId();
                $dataRecentPost['post_url'] = $postRecent->getPostUrl();
                $_postTitleStripped = $cpBlock->escapeHtml($postRecent->getTitle());
                $dataRecentPost['post_name'] = $_postTitleStripped;
                $dataRecentPost['post_image'] = $postRecent->getListThumbnailSrc();
                $dataRecentPost['post_created'] = strtotime($post->getPublishedAt()) . '';
                $dataRecentPost['post_by'] = $postRecent->getPostedBy();
                $dataRecentPost['post_view'] = $postRecent->getViews();
                $categoryRecentList = null;
                $categoriesRecents = $cpBlock->getCategories($postRecent->getId());
                foreach ($categoriesRecents as $categoriesRecent):
                    $categoryRecentList[] =$categoriesRecent->getName();
                endforeach;
                $dataRecentPost['post_category'] = $categoryRecentList;
                $dataArr[] = $dataRecentPost;
            endforeach;
            $data['recent_post'] = $dataArr;
            //var_dump($data);
            //echo json_encode(array('status' => 'valid', 'postData' => $dataArr));
        }else{
            $data['recent_post'] = null;
        }


        $helper = Mage::helper('core/http');
        $now = new Zend_Date();

        $view = Mage::getModel('mpblog/view');

        $view
            ->setPostId($postRecent->getId())
            ->setCustomerId(Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')->getCustomerId() : null)
            ->setSessionId(Mage::getSingleton('customer/session')->getSessionId())
            ->setRemoteAddr($helper->getRemoteAddr(true))
            ->setStoreId(getStoreId())
            ->setCreatedAt($now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
            ->setRefererUrl(null)
            ->save()
        ;

        http_response_code(200);
        echo json_encode(array('status_code' => 200, 'message' => 'valid', 'postData' => $data));
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
}
