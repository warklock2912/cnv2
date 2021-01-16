<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Shipment PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Magestore_Pdfinvoiceplus_Model_Sales_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Abstract {

    /**
    * Draw table header for product items
    *
    * @param  Zend_Pdf_Page $page
    * @return void
    */
    protected function _drawHeader(Zend_Pdf_Page $page) {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Products'),
            'feed' => 100,
        );

        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Qty'),
            'feed' => 35
        );

        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('SKU'),
            'feed' => 565,
            'align' => 'right'
        );

        $lineBlock = array(
            'lines' => $lines,
            'height' => 10
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
    * Return PDF document
    *
    * @param  array $shipments
    * @return Zend_Pdf
    */
    public function getPdf($shipments = array()) {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $page = $this->newPage();
            $order = $shipment->getOrder();
            $this->y = 700;
            $x = 70;
            //draw label

            $this->_setFontBold($page, 17);
            $page->drawRectangle($x - 20, $this->y, $page->getWidth() - 20, 450, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
            $page->drawText('ORDER NUMBER: ', $x, $this->y - 50, 'UTF-8');
            $page->drawText('NAME:', $x, $this->y - 80, 'UTF-8');
            $page->drawText('ADDRESS:', $x, $this->y - 110, 'UTF-8');
            $page->drawText('TEL:', $x, $this->y - 140, 'UTF-8');
            //draw value
            $this->_setFontRegular($page, 17);
            $shipping_address = $order->getShippingAddress();
            $page->drawText($order->getIncrementId(), $x + 150, $this->y - 50, 'UTF-8');
            $page->drawText($shipping_address->getName(), $x + 150, $this->y - 80, 'UTF-8');
            $page->drawText($shipping_address->getStreetFull() . ',' .             
                $shipping_address->getCity() . ',' .
                $shipping_address->getRegion() . ',' .
                $shipping_address->getPostcode() . ','.
                $shipping_address->getCountry(), $x + 150, $this->y - 110, 'UTF-8');
            $page->drawText($shipping_address->getTelephone(), $x + 150, $this->y - 140, 'UTF-8');
            //draw logo
            $image = Mage::getBaseDir('skin') . DS . 'adminhtml' . DS . 'default' . DS . 'default' . DS . 'magebuzz' . DS . 'logo.png';
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $top = 550; //top border of the page
                $widthLimit = 270; //half of the page width
                $heightLimit = 270; //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = $x + 330;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);
            }
        }
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    /**
    * Create new page and assign to PDF object
    *
    * @param  array $settings
    * @return Zend_Pdf_Page
    */
    public function newPage(array $settings = array()) {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }


    // // MS-1006
    // public function getPackingSlipPdf($shipments = array()) {
    //     $this->_beforeGetPdf();
    //     $this->_initRenderer('shipment');

    //     $pdf = new Zend_Pdf();
    //     $this->_setPdf($pdf);
    //     $style = new Zend_Pdf_Style();
    //     $this->_setFontBold($style, 10);
    //     foreach ($shipments as $shipment) {
    //       if ($shipment->getStoreId()) {
    //         Mage::app()->getLocale()->emulate($shipment->getStoreId());
    //         Mage::app()->setCurrentStore($shipment->getStoreId());
    //       }
    //       $page = $this->newPage();
    //       $order = $shipment->getOrder();
    //       $this->y = 800;
    //       $x = 10; //7.6 centimeter  =  287.244095 pixel
    //     //draw label
    //       $this->_setFontRegular($page, 15);
    //       $shipping_address = $order->getShippingAddress();
          
    //       $page->drawText('Order # '.$order->getIncrementId(), $x+20, $this->y - 40, 'UTF-8');
    //       $page->drawText($shipping_address->getName()."   T: ".$shipping_address->getTelephone(), $x+20, $this->y - 60, 'UTF-8');
    //       $page->drawText($shipping_address->getStreetFull(), $x+20, $this->y - 80, 'UTF-8');
    //       $page->drawText($shipping_address->getCity() . ',' .
    //               $shipping_address->getRegion(), $x+20, $this->y - 100, 'UTF-8');
    //       $page->drawText($shipping_address->getPostcode() . ','.
    //               $shipping_address->getCountry(), $x+20, $this->y - 120, 'UTF-8');
    //       $newline = 160;
    //       $add_area = 5;
    //        foreach($order->getAllVisibleItems() as $_item):

    //            $product = $_item->getName()." - Qty: ".round($_item->getQtyInvoiced());
    //            $length  = strlen($product);
    //            if ($length > 30) {
    //               $this->_setFontRegular($page, 12);
    //            }else{
    //               $this->_setFontRegular($page, 15);
    //            }
    //            $page->drawText($product, $x+20, $this->y - $newline, 'UTF-8');
    //            $newline = $newline+20;
    //            $add_area = $add_area+20;
    //        endforeach;

    //        $this->_setFontRegular($page, 15);
    //        $newline = $newline+20;
    //        $grand_total = number_format($order->getGrandTotal(), 2, '.', '');
    //        $base_shipping = number_format($order->getBaseShippingAmount(), 2, '.', '');
    //        $price_total = number_format($grand_total-$base_shipping, 2, '.', '');
    //        $page->drawText("     ".$price_total."     ".$base_shipping."     ".$grand_total, $x+10, $this->y - $newline, 'UTF-8');

    //        $y2 = 600-$add_area;
    //        $page->drawRectangle(20, $this->y, 237, $y2, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
           
    //     }
    //     $this->_afterGetPdf();
    //     if ($shipment->getStoreId()) {
    //         Mage::app()->getLocale()->revert();
    //     }
    //     return $pdf;
    // }

}
