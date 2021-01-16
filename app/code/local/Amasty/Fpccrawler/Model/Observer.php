<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Fpccrawler
 */


/**
 * @author Amasty
 */
define("MAX_ROWS_BEFORE_CLEAN", "500");
define("FILE_LOCK_GENERATE", "amfpccrawler_lock_generate.lock");
define("FILE_LOCK_PROCESS", "amfpccrawler_lock_process.lock");

class Amasty_Fpccrawler_Model_Observer
{

    public function generateQueue()
    {
        $helper    = Mage::helper('amfpccrawler');
        return $helper->generateQueue();

    }

    public function processQueue()
    {
        $helper    = Mage::helper('amfpccrawler');
        return $helper->processQueue();
    }

    public function checkCURL(Varien_Event_Observer $observer)
    {
        $params = Mage::app()->getRequest()->getParams();
        if (isset($params['section']) && $params['section'] == 'amfpccrawler') {
            // check if CURL lib exists
            if (!function_exists('curl_version')) {
                Mage::getSingleton('adminhtml/session')->addError('FPC Crawler will not work because PHP library CURL is disabled or not installed');
            }

            // echo the notice with Approx. Queue Processing Time
            $time = Mage::getResourceModel('amfpccrawler/log')->getQueueProcessingTime();
            $msg  = Mage::getModel('core/layout')
                        ->createBlock('core/template')
                        ->setProcessing($time)
                        ->setForAdminNotice(true)
                        ->setTemplate('amasty/amfpccrawler/charts/queueProcessing.phtml')
                        ->toHtml();
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);

            if (Mage::getStoreConfig('amfpccrawler/advanced/show_notifications')) {
                // check max_execution_time and warn the user
                $maxLifetime = ini_get('max_execution_time');
                $maxLifetime = $maxLifetime >= 0 ? $maxLifetime : 30;
                $processingTime = Mage::getResourceModel('amfpccrawler/log')->getQueueProcessingTime();
                if ($processingTime['cronProcessingTime'] > $maxLifetime && $maxLifetime != 0) {
                    $msg = Mage::helper('amfpccrawler')->__('Your one cron job processing time(' . $processingTime['cronProcessingTime'] . 's) is more than PHP allows(' . $maxLifetime . 's). Please, adjust your crawler settings to lower one cron job executing time!');
                    Mage::getSingleton('adminhtml/session')->addWarning($msg);
                }
                if ($processingTime['cronProcessingTime'] > 30) {
                    $msg = Mage::helper('amfpccrawler')->__('Your one cron job processing time(' . $processingTime['cronProcessingTime'] . 's) is more than PHP settings allows by default (30s). Please, check your max_execution_time PHP settings or adjust your crawler settings to lower one cron job processing time!');
                    Mage::getSingleton('adminhtml/session')->addNotice($msg);
                }
            }
        }
    }

}