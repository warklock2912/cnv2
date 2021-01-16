<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteAutocomplete
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteAutocomplete_Model_Observer {

    public function controllerActionLayoutRenderBefore($observer) {
        $helper = Mage::helper('mageworx_searchsuiteautocomplete');
        if ($helper->showPopup()) {
            $block = Mage::app()->getLayout()->getBlock('head');
            $animation = $helper->getAnimation();
            if ($block) {
                $block->addItem('skin_js', 'js/mageworx/searchsuiteautocomplete/searchsuiteautocomplete.js');
            }
            if ($animation == 'nprogress') {
                if ($block) {
                    $block->addItem('skin_js', 'js/mageworx/searchsuiteautocomplete/NProgress/nprogress.js');
                    $block->addCss('css/mageworx/searchsuiteautocomplete/NProgress/nprogress.css');
                }
            }
        }
    }

    public function controllerActionLayoutRenderBeforeAdminhtmlSystemConfigEdit($observer) {
        if (Mage::app()->getRequest()->getParam('section') == 'mageworx_searchsuite') {
            $block = Mage::app()->getLayout()->getBlock('head');
            if ($block) {
                $block->addJs('mageworx/jquery/jquery.min.js');
                $block->addJs('mageworx/jquery/noconflict.js');
                $block->addItem('skin_js', 'js/mageworx/searchsuiteautocomplete/tinycolor-0.9.15.min.js');
                $block->addItem('skin_js', 'js/mageworx/searchsuiteautocomplete/pick-a-color-1.2.2.min.js');
                $block->addCss('css/mageworx/searchsuiteautocomplete/pick-a-color-1.2.0.min.css');
                $block->addCss('css/mageworx/searchsuiteautocomplete/customizer.css');
                $block->addItem('skin_js', 'js/mageworx/searchsuiteautocomplete/customizer.js');
            }
        }
    }

}
