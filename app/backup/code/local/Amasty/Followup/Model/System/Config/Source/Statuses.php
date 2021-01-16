<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

class Amasty_Followup_Model_System_Config_Source_Statuses
{
    protected $_options;
        
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            if (version_compare(Mage::getVersion(), '1.5', '>')) {
                $this->_options = Mage::getResourceModel('sales/order_status_collection')
                    ->toOptionArray();
            } else {
                $states = Mage::getConfig()->getNode(Mage_Sales_Model_Config::XML_PATH_ORDER_STATES);

                foreach($states->children() as $state){

                    foreach ($state->statuses->children() as $status => $node) {

                        $this->_options[] = array(
                            'value' => $status,
                            'label' => $status
                        );
                    }
                }
            }
        }
        return $this->_options;
    }
}
