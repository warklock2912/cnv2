<?php
/*
* Copyright (c) 2017 www.tigren.com 
*/
class Tigren_Ruffle_Helper_Data extends Mage_Core_Helper_Abstract {
  public function isRuffleProduct($productId) {
    return Mage::getModel('ruffle/ruffle')->isRuffleProduct($productId);
  }
  public function generateRuffleNumber($ruffle) {
    $number = '';
    $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
    $_prefix = date('ym', $currentTimestamp);

    do {
      $number = $_prefix . '-' . $ruffle->getId() . '-' . mt_rand(100000, 999999);
      $maskCheck = Mage::getModel('ruffle/joiner')->getCollection()
        ->addFieldToFilter('ruffle_number', $number);
    }
    while(count($maskCheck));
    return $number;
  }

  public function getWinnerQuotaByProductId($productId, $ruffleId) {
    return Mage::getModel('ruffle/product')->getWinnerQuotaByProductId($productId, $ruffleId);
  }

  public function getUsedQuotaCollection($productId, $ruffleId, $groupId) {
    $usedQuotaCollection = Mage::getModel('ruffle/joiner')->getCollection();
    $usedQuotaCollection->addFieldToFilter('is_winner', 1)
      ->addFieldToFilter('ruffle_id', $ruffleId)
      ->addFieldToFilter('product_id', $productId);
    $usedQuotaCollection->getSelect()
      ->join(
        array('c' => Mage::getSingleton('core/resource')->getTableName('customer/entity')), 
        'main_table.customer_id=c.entity_id',
        array('c.group_id')
      )
      ->where('c.group_id=?', $groupId);
    return $usedQuotaCollection;
  }

  public function getUsedQuotaCollectionAll($productId, $ruffleId, $groupId) {
    $usedQuotaCollection = Mage::getModel('ruffle/joiner')->getCollection();
    $usedQuotaCollection->addFieldToFilter('is_winner', 1)
      ->addFieldToFilter('ruffle_id', $ruffleId)
      ->addFieldToFilter('product_id', $productId);
    $usedQuotaCollection->getSelect()
      ->join(
        array('c' => Mage::getSingleton('core/resource')->getTableName('customer/entity')), 
        'main_table.customer_id=c.entity_id',
        array('c.group_id')
      )
      ->where('c.group_id in (1,4)');
    return $usedQuotaCollection;
  }
    public function addFilterbyIsAll($ruffleCollection, $groupId){
    if($groupId == Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID){
        $ruffleCollection->getSelect()->where("(is_all = 1 and (vip_qty + general_qty > 0)) or (is_all = 0 and vip_qty > 0)");
    }elseif($groupId == Tigren_Ruffle_Model_Ruffle::RUFFLE_GENERAL_GROUP_ID){
        $ruffleCollection->getSelect()->where("(is_all = 1 and (vip_qty + general_qty > 0)) or (is_all = 0 and general_qty > 0)");
    }
    return $ruffleCollection;
  }
  public function checkJoinerRuffle($ruffle_id,$customer_id){
    
    $joinerData = null;
    $joinerCollection = Mage::getModel('ruffle/joiner')->getCollection()
      ->addFieldToFilter('customer_id',$customer_id)
      ->addFieldToFilter('ruffle_id', $ruffle_id);
    //$ruffleData = $joinerCollection->getSelect()->__toString();
    if($joinerCollection->getSize()){
      $joinerData = $joinerCollection->getSize();
    }

    if(empty($joinerData)){
      return false;
    }

    return true;

  }

  public function checkWinnerRuffle($product,$customer_id){
    $joinerData = null;
    if($customer_id){
      $joinerCollection = Mage::getModel('ruffle/joiner')->getCollection()
        ->addFieldToFilter('customer_id',$customer_id )
        ->addFieldToFilter('product_id', $product->getId())
        ->addFieldToFilter('product_sku', $product->getSku())
        ->addFieldToFilter('is_winner', 1)
        ->getFirstItem();
      if(isset($joinerCollection) && $joinerCollection){
        $joinerData = $joinerCollection->getSize();
      }

      if(empty($joinerData)){ 
        return null;
      }

      return true;
    }

    
  }

  public function checkWinnerRuffleBunbleProduct($parent_product, $options){
    $joinerData = null;
    if(Mage::getSingleton('customer/session')->isLoggedIn()){
      $customerID = Mage::getSingleton('customer/session')->getId();
      $joinerCollection = Mage::getModel('ruffle/joiner')->getCollection()
        ->addFieldToFilter('customer_id',$customerID )
        ->addFieldToFilter('product_id', $parent_product->getId())
        ->addFieldToFilter('product_options', $options)
        ->addFieldToFilter('is_winner', 1)
        ->getFirstItem();
    }

    if(isset($joinerCollection) && $joinerCollection){
      $joinerData = $joinerCollection->getData();
    }

    if(empty($joinerData)){
      return null;
    }

    return $joinerCollection;
  }

  public function checkWinnerBoughtProduct($product){
    $customer_id = Mage::getSingleton('customer/session')->getCustomerId();
    $id = $product->getId();
    $sku = $product->getSku();
    $collection = Mage::getModel('sales/order')->getCollection();
    $collection->getSelect()
      ->join( array("order_item"=> "sales_flat_order_item"), "`order_item`.order_id = `main_table`.entity_id and `order_item`.sku = '".$sku."' and `order_item`.product_id = ".$id." and `main_table`.customer_id = ".$customer_id, array('order_item.sku'));
    $ruffleData = $collection->getSelect()->__toString();
    $isBought = false;
    if($collection->getSize()){
      $isBought = true;
    }
    return $isBought;
  }

  public function checkProductRunningRuffle($product){
    
    $ruffleData = null;
    // $customer = Mage::getSingleton('customer/session')->getCustomer();
    // $groupId = $customer->getCustomerGroupId();
    $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
    $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
    $date = date('Y-m-d', $currentTimestamp); 
    if($product){
      $ruffleCollection = Mage::getModel('ruffle/product')->getCollection()
                          ->addFieldToFilter('product_id', $product->getId());
      $ruffleCollection->getSelect()
        ->join(
          array('r' =>Mage::getSingleton('core/resource')->getTableName('ruffle')),
            'main_table.ruffle_id = r.ruffle_id',
            array('r.start_date', 'r.end_date')
            );
//      $ruffleCollection->addFieldToFilter('start_date', array('lteq' => $date))
//                      ->addFieldToFilter('end_date', array('gteq' => $date));
      $ruffleCollection->addFieldToFilter('is_active', 1);
      $ruffleCollection=$this->addFilterbyIsAll($ruffleCollection, $groupId);
      
    }

    if($ruffleCollection){
      $ruffleData = $ruffleCollection->getSize();
    }

    if(empty($ruffleData)){ 
      return false;
    }

    return true;
  }

  // public function checkRuffleAvailableDate($product){
  //   $ruffleData = null;
  //   $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
  //   // $customer = Mage::getSingleton('customer/session')->getCustomer();
  //   // $groupId = $customer->getCustomerGroupId();
  //   $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
  //   $date = date('Y-m-d', $currentTimestamp);
  //   if($product){
  //     $ruffleCollection = Mage::getModel('ruffle/product')->getCollection()
  //       ->addFieldToFilter('product_id', $product->getId());
  //     $ruffleCollection->getSelect()
  //       ->join(
  //         array('r' =>Mage::getSingleton('core/resource')->getTableName('ruffle')),
  //         'main_table.ruffle_id = r.ruffle_id',
  //         array('r.start_date', 'r.end_date')
  //       );
  //     $ruffleCollection->addFieldToFilter('start_date', array('lteq' => $date))
  //                     ->addFieldToFilter('end_date', array('gteq' => $date));
  //     $ruffleCollection->addFieldToFilter('is_active', 1);
  //     // if($groupId == Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID){
  //     //   $ruffleCollection->getSelect()->where("(is_all = 1 and (vip_qty + general_qty > 0)) or (is_all = 0 and vip_qty > 0)");
  //     // }else{
  //     //   $ruffleCollection->getSelect()->where('general_qty > 0');
  //     // }

  //     $ruffleCollection = $this->addFilterbyIsAll($ruffleCollection, $groupId);
  //   }

  //   if($ruffleCollection){
  //     $ruffleData = $ruffleCollection->getSize();
  //   }

  //   if(empty($ruffleData)){
  //     return false;
  //   }

  //   return true;
  // }

  public function checkRuffleAvailableDate($ruffle_id){
    $ruffleData = null;
    $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
    $date = date('Y-m-d', $currentTimestamp);
    if($ruffle_id){
      $read = Mage::getSingleton('core/resource')->getConnection('core_read');
      $query = "SELECT * FROM ruffle
                WHERE (ruffle_id = $ruffle_id) AND (start_date <= '$date') AND (end_date >= '$date') AND (is_active = '1')";
      $ruffleData = $read->fetchAll($query);
      if(empty($ruffleData)){
          return false;
      }

      return true;
    }
  }
  public function checkUserCanJoin($product){
    $ruffleData = null;
    $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
    $productIds = array();
    if($product){
      if($product->getTypeId() == 'configurable'){
        $childProducts = Mage::getModel('catalog/product_type_configurable')
          ->getUsedProducts(null,$product);
        foreach($childProducts as $child) {
          $productIds[] = $child->getId();
        }
      }else{
        $productIds[] = $product->getId();
      }
      $productIds = implode(',', $productIds);
      //var_dump($productIds);

      $read = Mage::getSingleton('core/resource')->getConnection('core_read');
      $query = "SELECT main_table.ruffle_id ruffle_id ,
        is_all, 
        SUM(vip_qty) s_vip_qty, 
        SUM(general_qty) s_general_qty, 
        MAX(r.start_date) m_start_date, 
        MAX(r.end_date) m_end_date ,
        MAX(r.announce_date) m_announce_date ,
        r.available_day daycanbuy,
        r.email_join_en,
        r.email_join_th,
        DATE_ADD(announce_date, INTERVAL r.available_day DAY) final_date
        FROM ruffle_product AS main_table 
        JOIN ruffle AS r ON main_table.ruffle_id = r.ruffle_id 
        WHERE product_id in ({$productIds})
        AND is_active = 1
        GROUP BY main_table.ruffle_id;";
        //HAVING ((MAX(is_all) = 1 and (SUM(vip_qty) + SUM(general_qty) > 0)) or (MAX(is_all) = 0 and SUM(vip_qty) > 0));";
      $res = $read->fetchAll($query);

      foreach ($res as $row) {
        //$data[] = $row['s_general_qty'];
        $res = $row;

        $is_all = $row['is_all'];
        $s_vip_qty = $row['s_vip_qty'];
        $s_general_qty = $row['s_general_qty'];
        
        if($is_all == 1){
          if ($s_vip_qty + $s_general_qty > 0) {
            $res['is_allow'] = 1;

          }else{
            $res['is_allow'] = 0;

          }
        }else{

          if($groupId == 4){ //Vip
            if ($s_vip_qty > 0) {
            $res['is_allow'] = 1;
              
            }else{
            $res['is_allow'] = 0;
              
            }
          }elseif($groupId == 1){
            if ($s_general_qty > 0) {
              $res['is_allow'] = 1;
            
            }else{
              $res['is_allow'] = 0;
              
            }
          }
        }
        // Check join time
        $date_now = Mage::getModel('core/date')->date('Y-m-d');
        if ($row['m_end_date'] >= $date_now) {
          $res['join_time'] = "intime";
        }else{
          $res['join_time'] = "timeout";
        }
        // Check final time
        if ($res['daycanbuy'] != NULL || $res['daycanbuy'] != 0) {
          $res['limit_date_can_buy'] = $res['final_date'];

        }else{
          $res['limit_date_can_buy'] = 'unlimit';
        }
        
      }
      $res['sql'] = $query;
      return $res;
  
    }
  
  }

  public function checkAvailableDayCanBuy($product){
    $ruffleData = null;
    // $customer = Mage::getSingleton('customer/session')->getCustomer();
    // $groupId = $customer->getCustomerGroupId();
    $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
    $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
    $date = date('Y-m-d', $currentTimestamp);
    if($product){
      if($product->getTypeId() =='configurable'){
        $winnerRuffle = $this->checkWinnerRuffle($product);
        $options = $winnerRuffle->getData('product_options');
        $optionSelected = $this->getChildProduct($product->getId(), $options);
        $ruffleCollection = Mage::getModel('ruffle/product')->getCollection()
          ->addFieldToFilter('product_id', $optionSelected->getId());
        $ruffleCollection->getSelect()
          ->join(
            array('r' =>Mage::getSingleton('core/resource')->getTableName('ruffle')),
            'main_table.ruffle_id = r.ruffle_id',
            array('r.start_date', 'r.end_date','r.announce_date', 'r.available_day')
          );
        $ruffleCollection->addFieldToFilter('is_active', 1);
        $ruffleCollection=$this->addFilterbyIsAll($ruffleCollection, $groupId);
      }else{
        $ruffleCollection = Mage::getModel('ruffle/product')->getCollection()
          ->addFieldToFilter('product_id', $product->getId());
        $ruffleCollection->getSelect()
          ->join(
            array('r' =>Mage::getSingleton('core/resource')->getTableName('ruffle')),
            'main_table.ruffle_id = r.ruffle_id',
            array('r.start_date', 'r.end_date','r.announce_date', 'r.available_day')
          );
        //$ruffleCollection=$this->addFilterbyIsAll($ruffleCollection, $groupId);
      }
    }
    $ruffleData = null;

    if($ruffleCollection){
      foreach($ruffleCollection as $ruffle){
        $available_day = $ruffle->getData('available_day');
        $announce_date = $ruffle->getData('announce_date');
        if($available_day != null && $available_day > 0){
          $end_announce_date = date('Y-m-d', strtotime($announce_date. ' + '.$available_day.' days'));
          if($announce_date <= $date && $date <= $end_announce_date){
            $ruffleData = $ruffle->getData();
          }
        }else{
          if($announce_date <= $date){
            $ruffleData = $ruffle->getData();
          }
        }
      }
    }

    if(empty($ruffleData) || $ruffle == null){
      return false;
    }

    return true;
  }

  public function getChildProduct($parentProductId,$optionData)
  {
    $product = Mage::getModel('catalog/product')->load($parentProductId);
    $childProduct = Mage::getModel('catalog/product_type_configurable')->getProductByAttributes(unserialize($optionData), $product);
    return $childProduct;
  }

  public function checRuffleForConfigurable($product){
    if($product->getTypeId() == 'configurable'){
      $childProducts = Mage::getModel('catalog/product_type_configurable')
        ->getUsedProducts(null,$product);
      $countChild = 0;
      foreach($childProducts as $child) {
        $isRuffle = $this->checkProductRunningRuffle($child);
        if($isRuffle){
          $countChild = $countChild + 1;
        }
      }
    }
    if($countChild == 0){
      return false;
    }
    return true;
  }

  public function checkProductAssignToRuffle($product)
  {
    $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
    $date = date('Y-m-d', $currentTimestamp);
    if ($product->getTypeId() == 'configurable') {
      $ruffleCollection = Mage::getModel('ruffle/product')->getCollection()
        ->addFieldToFilter('product_id', $product->getId());
      $ruffleCollection->getSelect()
        ->join(
          array('r' => Mage::getSingleton('core/resource')->getTableName('ruffle')),
          'main_table.ruffle_id = r.ruffle_id',
          array('r.start_date', 'r.end_date')
        );
      $ruffleCollection->addFieldToFilter('start_date', array('lteq' => $date))
        ->addFieldToFilter('end_date', array('gteq' => $date));

      if($ruffleCollection->getSize()){
        return true;
      }
      return false;
    }
    return false;
  }
}