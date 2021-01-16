<?php

class Plumrocket_Cartreservation_Model_Override_CatalogInventory_Api_Stock_Item 
    extends Mage_CatalogInventory_Model_Api2_Stock_Item
{
    public function dispatch()
    {
        if (Mage::helper('cartreservation')->moduleEnabled()) {
            Mage::helper('cartreservation')->startOriginalMode();
        }

        parent::dispatch();
        if (Mage::helper('cartreservation')->moduleEnabled()) {
            Mage::helper('cartreservation')->stopOriginalMode();
        }
    }
}
