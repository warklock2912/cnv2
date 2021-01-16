<?php

/**
 * Class Tigren_Kerry_Block_Adminhtml_Sales_Shipment_View_Booking
 */
class Tigren_Kerry_Block_Adminhtml_Sales_Shipment_View_Booking extends Mage_Adminhtml_Block_Template
{

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return Mage::getStoreConfig('kerry/general/api_base_url') . '/SmartEDI/shipment_info';
    }

}
