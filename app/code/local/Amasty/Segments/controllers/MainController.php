<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


class Amasty_Segments_MainController extends Mage_Core_Controller_Front_Action
{
    function testAction(){
        $process = Mage::getSingleton('index/indexer')->getProcessByCode("amsegemnts_indexer");
        $process->reindexEverything();
        exit(1);
    }
}
?>