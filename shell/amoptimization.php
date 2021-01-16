<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


require_once 'abstract.php';

class Amasty_Shell_Optimization extends Mage_Shell_Abstract
{
    public function run()
    {
        if (isset($this->_args['optimize'])) {

            $cliWriter = new Zend_Log_Writer_Stream('php://stdout');
            $cliWriter->setFormatter(new Zend_Log_Formatter_Simple('%message%'.PHP_EOL));
            $logger = new Zend_Log($cliWriter);

            try {

                /** @var Amasty_Optimization_Model_Image_Optimizer $optimizer */
                $optimizer = Mage::getSingleton(
                    'amoptimization/image_optimizer', array('logger' => $logger)
                );

                $optimizer->optimizeImages();
            } catch (Exception $e) {
                $logger->err('Error: ' . $e->getMessage());
            }

        } else {
            Mage::helper('ambase/utils')->_echo($this->usageHelp());
        }
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f amoptimization.php -- [options]

  optimize      Optimize images in directories "media" and "skin"
  help          This help

USAGE;
    }
}

$shell = new Amasty_Shell_Optimization();
$shell->run();
