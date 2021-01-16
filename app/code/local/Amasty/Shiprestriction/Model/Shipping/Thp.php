<?php
// ** Thai EMS
class Amasty_Shiprestriction_Model_Shipping_Thp
  extends Mage_Shipping_Model_Carrier_Abstract
  implements Mage_Shipping_Model_Carrier_Interface
{
  protected $_code = 'thp';

  public function getAllowedMethods()
  {
    return array();
  }

  public function collectRates(Mage_Shipping_Model_Rate_Request $request)
  {
    /** @var Mage_Shipping_Model_Rate_Result $result */
    $result = Mage::getModel('shipping/rate_result');
    return $result;
  }

  public function isTrackingAvailable()
  {
    return true;
  }
}