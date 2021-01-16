<?php

/**
 * Class Tigren_Kerry_Adminhtml_RequestController
 */
class Tigren_Kerry_Adminhtml_RequestController extends Mage_Adminhtml_Controller_action
{

    /**
     *
     */
    public function getShipmentAction()
    {
        $response = array();
        $requestData = $this->getRequest()->getParams();
        try{
            $consignmentPrefix = Mage::getStoreConfig('kerry/general/consignment_no_prefix');
            $currentTime = substr(time(), 1);
            /** @var Mage_Sales_Model_Order_Shipment $shipment **/
            $shipment = Mage::getModel('sales/order_shipment')->load($requestData['shipment_id']);
            /** @var Tigren_Kerry_Helper_Data $kerryHelper **/
            $kerryHelper = Mage::helper('kerry');

            if($shipment->getId()){
                $shipment->setConsignmentNo($consignmentPrefix . $currentTime);
                $response = array(
                    'status' => 1,
                    'message' => 'success',
                    'shipment' => $kerryHelper->requestCreateShipmentData($shipment, $requestData)
                );
            }
        }catch (\Exception $e){
            $response = array(
                'status' => 0,
                'message' => $e->getMessage()
            );
        }

        $this->getResponse()->setBody(json_encode($response));
    }


    /**
     *
     */
    public function accessKerryAction()
    {
        /** @var Tigren_Kerry_Helper_Data $kerryHelper **/
        $kerryHelper = Mage::helper('kerry');
        $shipmentInformation = trim(file_get_contents("php://input"));
        $response = '';
        $result = array();
        try{
            $header = array(
                'Content-Type: application/json; charset=UTF-8',
                'app_id: ' . Mage::getStoreConfig('kerry/general/app_id'),
                'app_key: ' . Mage::getStoreConfig('kerry/general/app_key')
            );
            $data = json_decode($shipmentInformation, true);
            $url = Mage::getStoreConfig('kerry/general/api_base_url') . '/SmartEDI/shipment_info';
            $trans = array(
                'log_name' => 'kerry',
                'start_time' => microtime(true),
                'ref_no' => $data['req']['shipment']['con_no'],
                'service_name' => 'shipment_info'
            );
            $stime = microtime(true);
            $kerryHelper->logAPI('|[TRANS]|-------- Call Service|shipment_info --------');
            $kerryHelper->logAPI('|[INPUT]|' . $shipmentInformation);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $shipmentInformation);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $response = curl_exec($ch);
            $httpInfo = curl_getinfo($ch);
            $httpCode = @$httpInfo['http_code'] ?: 500;
            curl_close($ch);

            if($response === false) {
                $result['res']['shipment']['status_code'] = $httpCode;
                $result['res']['shipment']['status_desc'] = 'INT: '.curl_error($ch);
                $kerryHelper->logAPI('|[ERROR]|' . $httpCode . '|INT: ' . curl_error($ch));
            }

            $arrResponse = json_decode($response, true);
            if(!empty($arrResponse['res']['shipment']['status_code'])){
                if($arrResponse['res']['shipment']['status_code'] === '000'){
                    $shipmentId = $this->getRequest()->getParam('shipment_id');
                    /** @var Mage_Sales_Model_Order_Shipment $shipment **/
                    $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
                    $shipment->setData('consignment_no', $arrResponse['res']['shipment']['con_no']);
                    $shipment->setData('carrier_type', Mage::getStoreConfig('kerry/general/carrier_code'));
                    $shipment->setData('booking_status', '1');
                    $shipment->setData('booking_created_at', Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
                    $shipment->setData('box_sum', $this->getRequest()->getParam('tot_pkg'));
                    if($shipment->getOrder()->getPayment()->getMethodInstance()->getCode() == 'cashondelivery') {
                        $shipment->setData('cod_amount', $shipment->getOrder()->getBaseGrandTotal());
                    }else{
                        $shipment->setData('cod_amount', 0);
                    }
                    $shipment->save();

                    /** @var Mage_Sales_Model_Order_Shipment_Track $track **/
                    $track = Mage::getModel('sales/order_shipment_track');
                    $track->setShipment($shipment)
                        ->setData('title', Mage::getStoreConfig('kerry/general/carrier_code'))
                        ->setData('number', $arrResponse['res']['shipment']['con_no'])
                        ->setData('carrier_code', Mage::getStoreConfig('kerry/general/carrier_code'))
                        ->setData('order_id', $shipment->getData('order_id'));
                    $track->save();

                    Mage::getSingleton('adminhtml/session')->addSuccess(__($arrResponse['res']['shipment']['status_desc']));
                }else{
                    Mage::getSingleton('adminhtml/session')->addError(__($arrResponse['res']['shipment']['status_desc']));
                }
            }

            $kerryHelper->logAPI('|[OUTPUT]|' . json_encode(array($arrResponse)));
            $interval = round((microtime(true) - $stime) * 1000, 2);
            $kerryHelper->logAPI('[TRANS]' . implode(' ', array("-------- End Service|shipment_info in {$interval} ms. --------")));

        } catch (\Exception $e) {
            $httpcode = 500;
            $response = $e->getMessage();
            $result['res']['shipment']['status_code'] = $httpcode;
            $result['res']['shipment']['status_desc'] = 'INT: '.$e->getMessage();
            $kerryHelper->logAPI('|[ERROR]|' . $httpcode . '|INT: ' . $e->getMessage());
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->getResponse()->setBody($response);
    }

    /**
     * @return bool
     */
    protected function _isAllowed(){
        return true;
    }
}