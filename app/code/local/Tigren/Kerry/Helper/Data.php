<?php

/**
 * Class Tigren_Kerry_Helper_Data
 */
class Tigren_Kerry_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     *
     */
    const SIZE_LABEL = '384:576';

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param array $requestData
     * @return array
     * @throws Mage_Core_Exception
     */
    public function requestCreateShipmentData(Mage_Sales_Model_Order_Shipment $shipment, array $requestData)
    {
        $order = $shipment->getOrder();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        $shipmentInformation = array();

        $shipmentInformation['req']['shipment'] = array(
            'con_no' => $shipment->getConsignmentNo(),
            's_name' => Mage::getStoreConfig('kerry/sender_fields/name'),
            's_address' => Mage::getStoreConfig('kerry/sender_fields/address'),
            's_village' => '',
            's_soi' => '',
            's_road' => '',
            's_subdistrict' => Mage::getStoreConfig('kerry/sender_fields/subdistrict'),
            's_district' => Mage::getStoreConfig('kerry/sender_fields/city'),
            's_province' => Mage::getStoreConfig('kerry/sender_fields/region'),
            's_zipcode' => Mage::getStoreConfig('kerry/sender_fields/postcode'),
            's_mobile1' => Mage::getStoreConfig('kerry/sender_fields/telephone'),
            's_mobile2' => '',
            's_telephone' => Mage::getStoreConfig('kerry/sender_fields/telephone'),
            's_email' => Mage::getStoreConfig('kerry/sender_fields/email'),
            's_contactperson' => Mage::getStoreConfig('kerry/sender_fields/contact'),
            'r_name' => $shippingAddress->getFirstname() .' '. $shippingAddress->getLastname(),
            'r_address' => $shippingAddress->getStreet(1),
            'r_village' => '',
            'r_soi' => '',
            'r_road' => '',
            'r_subdistrict' => $shippingAddress->getData('subdistrict'),
            'r_district' => $shippingAddress->getCity(),
            'r_province' => ($shippingAddress->getRegion()) ? $shippingAddress->getRegion() : '',
            'r_zipcode' => $shippingAddress->getPostcode(),
            'r_mobile1' => $shippingAddress->getTelephone(),
            'r_mobile2' => '',
            'r_telephone' => $shippingAddress->getTelephone(),
            'r_email' => $shippingAddress->getEmail(),
            'r_contactperson' => $shippingAddress->getFirstname() . $shippingAddress->getLastname(),
            'special_note' => '',
            'service_code' => Mage::getStoreConfig('kerry/general/service_code'),
            'tot_pkg' => $requestData['totalPackages'],
            'declare_value' => '0',
            'ref_no' => $shipment->getConsignmentNo(),
            'action_code' => 'A',
            'shipment_type' => '1',
            'merchant_id' => Mage::getStoreConfig('kerry/general/merchant_id')
        );

        if ($paymentMethod == 'cashondelivery') {
            $shipmentInformation['req']['shipment']['cod_amount'] = $order->getBaseGrandTotal();
            $shipmentInformation['req']['shipment']['cod_type'] = 'CASH';
        }else{
            $shipmentInformation['req']['shipment']['cod_amount'] = 0;
            $shipmentInformation['req']['shipment']['cod_type'] = null;
        }

        return $shipmentInformation;
    }

    /**
     * @param $message
     * @return bool
     */
    public function logAPI($message)
    {
        Mage::log($message, Zend_Log::DEBUG, 'kerr-api/kerry_debug_' . Mage::getModel('core/date')->gmtDate('Y-m-d') . '.log', true);
        return true;
    }


    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Zend_Pdf
     * @throws Mage_Core_Exception
     * @throws Zend_Barcode_Exception
     * @throws Zend_Pdf_Exception
     */
    public function getAwbPdf(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $pdf = new Zend_Pdf();
        /** @var Zend_Pdf_Page $page * */
        $boxSum = $shipment->getData('box_sum');
        for($pageNumber = 0; $pageNumber < $boxSum; $pageNumber++){
            $pdf->pages[] = $pdf->newPage(self::SIZE_LABEL);
            $page = $pdf->pages[$pageNumber];
            $style = new Zend_Pdf_Style();
            $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . DIRECTORY_SEPARATOR . 'app/code/local/Tigren/Kerry/Helper/Pdf/DB_Helvethaica_X_v3_2_2.ttf');
            $style->setFont($font, 15);
            $style->setLineColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
            $style->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->setStyle($style);

            // $page->drawRectangle(0, 0, 384, 576, Zend_Pdf_Page::SHAPE_DRAW_FILL);

            $remarkPath = Mage::getBaseDir() . DIRECTORY_SEPARATOR . 'app/code/local/Tigren/Kerry/Helper/Pdf/carnival.png';
            $remark = Zend_Pdf_Image::imageWithPath($remarkPath);
            $page->drawImage($remark, 0, 15, 384, 561);

            $barCode = $this->generateBarCode($shipment);
            $page->drawText($shipment->getData('consignment_no'), 32, 510, 'UTF-8');
            
            $page->drawImage($barCode, 30, 505, 310, 488);
            $style->setFont($font, 20);
            $page->setStyle($style);
            if($shipment->getOrder()->getPayment()->getMethodInstance()->getCode() == 'cashondelivery') {
                $page->drawText('เก็บเงินปลายทาง', 32, 445, 'UTF-8');
                if($pageNumber == 0)
                    $page->drawText($shipment->getData('cod_amount'), 170, 445, 'UTF-8');
            }
            else {
                $page->drawText('ชำระเงินแล้ว', 32, 445, 'UTF-8');
            }
            $page->drawText($pageNumber + 1 . '/' . $boxSum, 290, 445, 'UTF-8');

            $page->drawText($shipment->getOrder()->getIncrementId(), 32, 400, 'UTF-8');
            $style->setFont($font, 15);
            $page->setStyle($style);
            $page->drawText($shipment->getShippingAddress()->getFirstname() . ' ' . $shipment->getShippingAddress()->getLastname(), 32, 325, 'UTF-8');

//            $page->drawText($shipment->getShippingAddress()->getStreet(1), 40, 280, 'UTF-8');
//            $test = $shipment->getShippingAddress()->getStreet(1);
            $y = 280;
            $addresses = explode('\n', wordwrap($shipment->getShippingAddress()->getStreet(1), 70, '\n'));
            $addressesCustom1 = array();
            $addressesCustom2 = array();
            foreach ($addresses as $key => $street){
                if(count($addresses) >= 3){
                    if($key == 0){
                        $addressesCustom1[] =  $addresses[0] . $addresses[1] . $addresses[2];
                    }
                    if($key >=3){
                        $addressesCustom2[] = $addresses[$key];
                    }
                }else{
                    $addressesCustom1[] =  $street;
                }
            }
            if($addressesCustom1){
                $page->drawText(implode('', $addressesCustom1), 32, $y, 'UTF-8');
                $y -= 15;
            }
            if($addressesCustom2){
                $page->drawText(implode('', $addressesCustom2), 32, $y, 'UTF-8');
                $y -= 15;
            }
            $page->drawText(implode(', ', array($shipment->getShippingAddress()->getData('subdistrict'), $shipment->getShippingAddress()->getData('city'), $shipment->getShippingAddress()->getData('region'), $shipment->getShippingAddress()->getPostcode())), 32, $y, 'UTF-8');

            $page->drawText($shipment->getShippingAddress()->getTelephone(), 90, 215, 'UTF-8');

            $page->drawText($shipment->getIncrementId(), 32, 170, 'UTF-8');
            $barCodeIncrementId = $this->generateIncrementIdBarCode($shipment);
            $page->drawImage($barCodeIncrementId, 30, 165, 200, 150);

            $page->drawText(Mage::getStoreConfig('kerry/sender_fields/name'), 32, 100, 'UTF-8');
            $page->drawText(Mage::getStoreConfig('kerry/sender_fields/address'), 32, 85, 'UTF-8');
            $page->drawText(Mage::getStoreConfig('kerry/sender_fields/subdistrict') . ' ' . Mage::getStoreConfig('kerry/sender_fields/city') . ' ' . Mage::getStoreConfig('kerry/sender_fields/region') . ' ' . Mage::getStoreConfig('kerry/sender_fields/postcode'), 32, 70, 'UTF-8');
        }

        return $pdf;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Zend_Pdf_Resource_Image_Png
     * @throws Zend_Barcode_Exception
     * @throws Zend_Pdf_Exception
     */
    protected function generateBarCode(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $config = new Zend_Config(array(
            'barcode' => 'code128',
            'barcodeParams' => array(
                'text' => $shipment->getData('consignment_no'),
                'withQuietZones' => false,
                'drawText' => false
            ),
            'renderer' => 'image',
            'rendererParams' => array('imageType' => 'png')
        ));
        $barcodeResource = \Zend_Barcode::factory($config)->draw();
        ob_start();
        imagepng($barcodeResource);
        $barcodeImage = ob_get_clean();
        $image = new Zend_Pdf_Resource_Image_Png('data:image/png;base64,' . base64_encode($barcodeImage));
        return $image;
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Zend_Pdf_Resource_Image_Png
     * @throws Zend_Barcode_Exception
     * @throws Zend_Pdf_Exception
     */
    protected function generateIncrementIdBarCode(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $config = new Zend_Config(array(
            'barcode' => 'code128',
            'barcodeParams' => array(
                'text' => $shipment->getData('increment_id'),
                'withQuietZones' => false,
                'drawText' => false
            ),
            'renderer' => 'image',
            'rendererParams' => array('imageType' => 'png')
        ));
        $barcodeResource = \Zend_Barcode::factory($config)->draw();
        ob_start();
        imagepng($barcodeResource);
        $barcodeImage = ob_get_clean();
        $image = new Zend_Pdf_Resource_Image_Png('data:image/png;base64,' . base64_encode($barcodeImage));
        return $image;
    }
}
