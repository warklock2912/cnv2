<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Model_Observer
{
    public function replaceRequireJs($observer)
    {
        $block = $observer->getBlock();
        $controller = Mage::app()->getRequest()->getControllerName();
        if ($block instanceof Mage_Adminhtml_Block_Page_Head && $controller=='amreports_data') {
            $items = $block->getItems();
            if (isset($items['js/prototype/prototype.js'])) {
                unset($items['js/prototype/prototype.js']);
            }
            $jsToAdd = array(
                'prototype.js',
                'export/tableExport.js',
                'export/jquery.base64.js',
                'export/html2canvas.js',
                //'export/jspdf/libs/sprintf.js',
                'export/jspdf.min.js',
                //'export/jspdf/libs/base64.js',
                'jquery-2.1.4.min.js'
            );

            foreach ($jsToAdd as $js) {
                $items = $this->addJs($items,$js);
            }
            $block->setItems($items);
        }
    }

    protected function addJs($items, $name)
    {
        $amPrototype = array();
        $amPrototype['js/amasty/amreports/'.$name]['type'] = 'js';
        $amPrototype['js/amasty/amreports/'.$name]['name'] = 'amasty/amreports/'.$name;
        $amPrototype['js/amasty/amreports/'.$name]['params'] = '';
        $amPrototype['js/amasty/amreports/'.$name]['if'] = '';
        $amPrototype['js/amasty/amreports/'.$name]['cond'] = '';
        $items = $amPrototype + $items;
        return $items;
    }
}