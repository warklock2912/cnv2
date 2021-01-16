<?php

/**
 * MageWorx
 * Search Suite
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */
class MageWorx_SearchSuiteSphinx_Model_Observer {

    public function controllerActionPredispatchAdminhtmlSystemConfigEdit($observer) {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        if (strpos($request->getRequestUri(), 'mageworx_searchsuite')) {
            Mage::helper('mageworx_searchsuitesphinx')->registerEngine('sphinx', 'Sphinx');
        }
    }

}
