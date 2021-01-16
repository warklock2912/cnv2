<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSolr
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSolr_Adminhtml_Mageworx_Searchsuitesolr_CheckController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $instance = Mage::helper('mageworx_searchsuitesolr')->getInstance();
        $status = $instance->status();
        //$msg = htmlentities($instance->GetLastError());
        $result = array('status' => (int) $status, 'msg' => $msg);
        $json = json_encode($result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        die($json);
    }

}
