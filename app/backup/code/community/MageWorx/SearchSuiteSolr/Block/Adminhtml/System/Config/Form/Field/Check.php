<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSolr_Block_Adminhtml_System_Config_Form_Field_Check extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $html = '<button onclick="checkSolr(this);return false;" style="background-image: none;">' . Mage::helper('mageworx_searchsuitesolr')->__('Check Availability') . '</button>';
        $html.='<script type="text/javascript">function checkSolr(btn){
            jQuery.ajax({type:"get",dataType: "json",url:"' . Mage::helper('adminhtml')->getUrl('adminhtml/mageworx_searchsuitesolr_check/index') . '",success:
                function(r){if(r.status){jQuery(btn).css("background-color","#0f0");}else{jQuery(btn).css("background-color","#f00")};}})};</script>';

        return $html;
    }

}
