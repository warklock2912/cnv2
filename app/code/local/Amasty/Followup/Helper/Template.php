<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */
class Amasty_Followup_Helper_Template extends Mage_Core_Helper_Abstract
{
    static function create($templateCode, $templateLabel)
    {
        $locale = 'en_US';

        $template = Mage::getModel('adminhtml/email_template');

        $template->loadDefault($templateCode, $locale);
        $template->setData('orig_template_code', $templateCode);
        $template->setData('template_variables', Zend_Json::encode($template->getVariablesOptionArray(true)));

        $template->setData('template_code', $templateLabel);

        $template->setTemplateType(Mage_Core_Model_Email_Template::TYPE_HTML);

        $template->setId(NULL);

        $template->save();
    }
}
?>