<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Observer_Minification
{
    public function onControllerResponseSendBefore($observer)
    {
        $response = Mage::app()->getResponse();

        $page = $response->getBody();
        $responseModified = false;

        $processors = array('js', 'css', 'fingerprints', 'footerjs', 'html');

        foreach ($processors as $code) {
            if (!Mage::getStoreConfigFlag("amoptimization/$code/enabled"))
                continue;

            /** @var Amasty_Optimization_Model_Minification_Processor $processor */
            $processor = Mage::getSingleton("amoptimization/minification_processor_$code");

            $page = $processor->process($page);

            $responseModified = true;
        }

        if ($responseModified) {
            $response->setBody($page);
        }
    }
}
