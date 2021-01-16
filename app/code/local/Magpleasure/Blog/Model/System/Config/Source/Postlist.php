<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Model_System_Config_Source_Postlist
{
  public function toOptionArray($categoryId)
  {
    $result = array(
      array('value'=>'-', 'label'=>Mage::helper('mpblog')->__('Please Select Feature Post')),
    );


    /** @var Magpleasure_Blog_Model_Mysql4_Category_Collection $categories  */
    $collection = Mage::getModel('mpblog/post')->getCollection();
    $collection->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);
    $collection->setDateOrder();

    $collection->addCategoryFilter($categoryId);
    foreach ($collection as $post){
      $result[] = array('value'=>$post->getPostId(), 'label'=>$post->getTitle());
    }

    return $result;
  }

}

