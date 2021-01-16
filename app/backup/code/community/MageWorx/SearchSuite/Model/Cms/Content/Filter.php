<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuite
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuite_Model_Cms_Content_Filter extends Mage_Core_Model_Email_Template_Filter {

    protected $_designSettings;

    protected function _applyDesignSettings() {
        if ($this->getDesignSettings()) {
            $design = Mage::getDesign();
            $this->getDesignSettings()
                    ->setOldArea($design->getArea())
                    ->setOldStore($design->getStore());

            if ($this->getDesignSettings()->getArea()) {
                Mage::getDesign()->setArea($this->getDesignSettings()->getArea());
            }

            if ($this->getDesignSettings()->getStore()) {
                Mage::app()->getLocale()->emulate($this->getDesignSettings()->getStore());
                $design->setStore($this->getDesignSettings()->getStore());
                $design->setPackageName('');
                $design->setTheme('');
            }
        }
        return $this;
    }

    public function setDesignSettings(array $settings) {
        $this->getDesignSettings()->setData($settings);
        return $this;
    }

    protected function _resetDesignSettings() {
        if ($this->getDesignSettings()) {
            if ($this->getDesignSettings()->getOldArea()) {
                Mage::getDesign()->setArea($this->getDesignSettings()->getOldArea());
            }

            if ($this->getDesignSettings()->getOldStore()) {
                Mage::getDesign()->setStore($this->getDesignSettings()->getOldStore());
                Mage::getDesign()->setPackageName('');
                Mage::getDesign()->setTheme('');
            }
        }
        Mage::app()->getLocale()->revert();
        return $this;
    }

    public function getDesignSettings() {
        if (is_null($this->_designSettings)) {
            $this->_designSettings = new Varien_Object();
        }
        return $this->_designSettings;
    }

    public function process($content) {
        $this->_applyDesignSettings();
        try {
            $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content); ////////////
            $result = $this->filter($content);
        } catch (Exception $e) {
            $this->_resetDesignSettings();
            throw $e;
        }
        $this->_resetDesignSettings();
        return $result;
    }

}
