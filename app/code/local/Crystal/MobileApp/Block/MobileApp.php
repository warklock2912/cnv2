<?php
class Crystal_MobileApp_Block_MobileApp extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getMobileApp()
    {
        if (!$this->hasData('mobileapp')) {
            $this->setData('mobileapp', Mage::registry('mobileapp'));
        }
        return $this->getData('mobileapp');
    }
}
