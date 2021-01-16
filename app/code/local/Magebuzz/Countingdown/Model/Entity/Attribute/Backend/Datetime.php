<?php

class Magebuzz_Countingdown_Model_Entity_Attribute_Backend_Datetime extends Mage_Eav_Model_Entity_Attribute_Backend_Datetime
{
  const DATETIME_DATEPICKER_FORMAT = 'd/m/Y H:i';

  /**
   * Prepare date for save in DB
   *
   * @param   string | int $date
   * @return  string
   */
  public function formatDate($date)
  {
    if (empty($date)) {
      return null;
    }
    $date = Mage::app()->getLocale()->date($date,
      Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
      null, false
    );

    return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
  }
}