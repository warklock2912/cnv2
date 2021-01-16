<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Category_List extends Magpleasure_Blog_Block_Content_Category
{
  protected function _prepareLayout()
  {
    $this->_title = $this->_helper()->getSeoTitle();
    parent::_prepareLayout();
    $limit = $this->_helper()->getPostsLimit();
    if(Mage::registry('currentNumberPostCategory')){
      $limit += Mage::registry('currentNumberPostCategory');
    }
    $this->getToolbar()
      ->setPagerObject(Mage::getModel('mpblog/list'))
      ->setLimit($limit)
      ->setCollection($this->getCollection())
      ->setTemplate('mpblog/list/pager.phtml')
    ;

    return $this;
  }

  public function gettotalPostCategory(){
    $collection = Mage::getModel('mpblog/post')->getCollection();
    if (!Mage::app()->isSingleStoreMode()){
      $collection->addStoreFilter(Mage::app()->getStore()->getId());
    }
    $collection->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);
    $collection->setUrlKeyIsNotNull();
    $collection->setDateOrder();

    $this->_checkCategory($collection);
    $this->_checkTag($collection);
    return count($collection);
  }
}
