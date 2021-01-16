<?php
require_once Mage::getModuleDir('controllers', 'Mage_Customer').DS.'AccountController.php';
class MagentoMasters_MasterCheckout_AccountController extends Mage_Customer_AccountController
{
  public function dashboardAction() {
      $this->loadLayout();
     $this->renderLayout();
  }

}