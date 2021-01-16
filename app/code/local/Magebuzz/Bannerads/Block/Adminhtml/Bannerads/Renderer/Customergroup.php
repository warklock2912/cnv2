<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/

class Magebuzz_Bannerads_Block_Adminhtml_Bannerads_Renderer_Customergroup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
  public function render(Varien_Object $row) {
    $getData = $row->getData();
    $customerArray = Mage::getResourceModel('customer/group_collection')->toOptionArray();

    $customerGroupId = unserialize($getData['customer_group_ids']);
    $customer = '';
    foreach ($customerGroupId as $key => $value) {
      if ($key == count($customerGroupId) - 1) {

        if (isset($customerArray[$value]['label'])) $customer .= $customerArray[$value]['label'];

      } else {
        if (isset($customerArray[$value]['label'])) $customer .= $customerArray[$value]['label'] . ',';
      }
    }
    return $customer;
  }

}