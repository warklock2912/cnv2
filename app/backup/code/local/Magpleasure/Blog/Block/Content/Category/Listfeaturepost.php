<?php

class Magpleasure_Blog_Block_Content_Category_Listfeaturepost extends Magpleasure_Blog_Block_Content_Category
{
  public function getCollection()
  {
    if (!$this->_collection){
      /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection  $collection  */
      $collection = Mage::getModel('mpblog/post')->getCollection();
      if (!Mage::app()->isSingleStoreMode()){
        $collection->addStoreFilter(Mage::app()->getStore()->getId());
      }
      $collection->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);
      $collection->setUrlKeyIsNotNull();
      $collection->setDateOrder();

      $this->_checkCategory($collection);
      $this->_checkTag($collection);

      $this->_collection = $collection;
    }
    return $this->_collection;
  }

  protected function _checkCategory(Magpleasure_Blog_Model_Mysql4_Post_Collection $collection)
  {
    if (($id = $this->getRequest()->getParam('id')) && $this->getIsCategory()){
      $collection->addCategoryFilter($id);
      $categoryModel = Mage::getModel('mpblog/category')->load($id);
      $postId = $categoryModel->getData('feature_post');
      $collection->addFieldToFilter('post_id', $postId );
    }
    return $this;
  }
}