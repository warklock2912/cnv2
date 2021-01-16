<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSphinx_Adminhtml_Mageworx_Searchsuitesphinx_CheckController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $instance = Mage::helper('mageworx_searchsuitesphinx')->getInstance();
        $status = $instance->Status();
        $msg = htmlentities($instance->GetLastError());
        $result = array('status' => (int) $status, 'msg' => $msg);
        $json = json_encode($result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        die($json);
    }

}
