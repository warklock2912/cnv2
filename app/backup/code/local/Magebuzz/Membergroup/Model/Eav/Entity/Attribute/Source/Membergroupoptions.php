<?php
/**
 * Created by PhpStorm.
 * User: tungd
 * Date: 9/29/16
 * Time: 6:10 PM
 */

class Magebuzz_Membergroup_Model_Eav_Entity_Attribute_Source_Membergroupoptions extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
  public function getAllOptions()
  {
    if (is_null($this->_options)) {
      $this->_options = array(
        array(
          "label" => Mage::helper("eav")->__("No Need Approve"),
          "value" =>  0
        ),
        array(
          "label" => Mage::helper("eav")->__("Need Approve"),
          "value" =>  1
        ),

        array(
          "label" => Mage::helper("eav")->__("Approved"),
          "value" =>  2
        ),

      );
    }
    return $this->_options;
  }

  public function getOptionArray()
  {
    $_options = array();
    foreach ($this->getAllOptions() as $option) {
      $_options[$option["value"]] = $option["label"];
    }
    return $_options;
  }
  public function getOptionText($value)
  {
    $options = $this->getAllOptions();
    foreach ($options as $option) {
      if ($option["value"] == $value) {
        return $option["label"];
      }
    }
    return false;
  }

  public function getFlatColums()
  {
    $columns = array();
    $columns[$this->getAttribute()->getAttributeCode()] = array(
      "type"      => "tinyint(1)",
      "unsigned"  => false,
      "is_null"   => true,
      "default"   => null,
      "extra"     => null
    );

    return $columns;
  }
  public function getFlatIndexes()
  {
    $indexes = array();

    $index = "IDX_" . strtoupper($this->getAttribute()->getAttributeCode());
    $indexes[$index] = array(
      "type"      => "index",
      "fields"    => array($this->getAttribute()->getAttributeCode())
    );

    return $indexes;
  }
  public function getFlatUpdateSelect($store)
  {
    return Mage::getResourceModel("eav/entity_attribute")
      ->getFlatUpdateSelect($this->getAttribute(), $store);
  }




}