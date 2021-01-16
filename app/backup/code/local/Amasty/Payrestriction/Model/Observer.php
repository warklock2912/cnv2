<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Payrestriction
 */
class Amasty_Payrestriction_Model_Observer
{
    /**
     * Append rule product attributes to select by quote item collection
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_SalesRule_Model_Observer
     */
    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $attributes = Mage::getResourceModel('ampayrestriction/rule')->getAttributes();
        
        $result = array();
        foreach ($attributes as $code) {
            $result[$code] = true;
        }
        $attributesTransfer->addData($result);
        
        return $this;
    }
  public function handleNewConditions($observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)){
            $cond = array();
        }
        
        $types = array(
            'customer' => 'Customer attributes',
        );
        foreach ($types as $typeCode => $typeLabel){
            $condition           = Mage::getModel('ampayrestriction/rule_condition_' . $typeCode);
            $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();
            
            $attributes = array();
            foreach ($conditionAttributes as $code=>$label) {
                $attributes[] = array(
                    'value' => 'ampayrestriction/rule_condition_'.$typeCode.'|' . $code, 
                    'label' => $label,
                );
            }         
            $cond[] = array(
                'value' => $attributes, 
                'label' => Mage::helper('ampayrestriction')->__($typeLabel), 
            );            
        }

        $transport->setConditions($cond);
        
        return $this; 
    }                        
    
}