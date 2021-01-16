<?php

/*
 * Copyright (c) 2013 www.magebuzz.com 
 */

class Magebuzz_Countingdown_Block_Countingdown extends Mage_Core_Block_Template {

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    protected function _toHtml() {
        $block = $this->getLayout()->createBlock('countingdown/countingdown')->setTemplate('countingdown/countingdown.phtml');
        $banners[] = $block->renderView();
        $htmlBanner = implode('', $banners);
        $html = $htmlBanner;
        return $html;
    }

}
