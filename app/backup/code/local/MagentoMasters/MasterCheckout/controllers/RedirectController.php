<?php
class MagentoMasters_MasterCheckout_RedirectController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        $this->_redirect('mastercheckout/', array('_secure'=>true));
    }
}