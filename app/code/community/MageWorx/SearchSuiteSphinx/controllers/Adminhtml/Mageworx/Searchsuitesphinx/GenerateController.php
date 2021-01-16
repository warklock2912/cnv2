<?php
/**
 * MageWorx
 * Search Suite Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SearchSuiteSphinx
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SearchSuiteSphinx_Adminhtml_Mageworx_Searchsuitesphinx_GenerateController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        $configFilePath = Mage::getModuleDir('etc', 'MageWorx_SearchSuiteSphinx') . DS . 'conf' . DS . 'sphinx.conf';

        if (file_exists($configFilePath)) {
            $configModel = Mage::getModel('mageworx_searchsuitesphinx/generateConfig');
            $configData = $configModel->getConfigData();

            $configContent = strtr(file_get_contents($configFilePath), $configData);

            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="sphinx.conf"');
            $this->getResponse()->setBody($configContent);
            exit;
        }
    }

}
