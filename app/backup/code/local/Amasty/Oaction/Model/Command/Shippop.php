<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */
class Amasty_Oaction_Model_Command_Shippop extends Amasty_Oaction_Model_Command_Abstract
{
    public function __construct($type)
    {
        parent::__construct($type);
        $this->_label = 'Shippop';
        $this->_fieldLabel = 'Choose Ship';
    }

    /**
     * Executes the command
     *
     * @param array $ids product ids
     * @param string $val field value
     * @return string success message if any
     */
   
    
    public function execute($ids, $val)
    {
        $success = parent::execute($ids, $val);

        $hlp = Mage::helper('amoaction');

        $message = trim($val);
        if (!$message) {
            $this->_errors[] = $hlp->__('Message can not be empty');
            return $success;
        }

        $numAffectedOrders = 0;
        $checkprices = $this->checkprice($ids,$val);
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $error_msg = $checkprice["message"];
       //var_dump($checkprice);

        if($checkprices["response"]["data"]){
            foreach ($checkprices["response"]["data"] as $datas) {
                $count = count($datas);
                if($count > 0 ){
                    foreach ($datas as $data) {
                        
                         $status = $data["available"];
                         if ($status == false) {
                            $checkprice_status = 'false';
                            $this->_errors[] = 'Some item is not available';
                            $count = 0;
                             break;
                         }else if($status == true){
                             $checkprice_status = 1;
                            
                         }
                         
                    }

                }

            } //end foreach
        
            if($count > 0){
                if($checkprice_status == 1){
                    // start step 2 Booking
                    $bookingresults = $this->booking($checkprices["orders"],$val);
                    $book_status = $bookingresults['status'];
                    if ($book_status == true){
                        $purchase_id = $bookingresults["purchase_id"];
                        // Step confirm
                        $confirm = $this->confirm( $purchase_id );

                        

                        

                        //echo "<br>---confirm----- ";

                        //var_dump($confirm);
                        if ($confirm["status"] == true) {
                            $tracks = $this->tracking_purchase($purchase_id);

                            foreach ($tracks as $track) {
                                
                                $j=0;
                                foreach ($track as $trackitem) {
                                    $tracking_code  = $trackitem['tracking_code'];
                                    $courier_tracking_code  = $trackitem['courier_tracking_code'];
                                    $courier_code  = $trackitem['courier_code'];

                                    $id= $ids[$j];
                                    $query = "INSERT INTO shippop (
                                        shippop_purchase_id,
                                        order_id,
                                        tracking_code,
                                        courier_tracking_code,
                                        courier_code,
                                        status
                                    )
                                    VALUES (
                                        :purchase_id,
                                        :order_id,
                                        :tracking_code,
                                        :courier_tracking_code,
                                        :courier_code,
                                        :status
                                      
                                    );";

                                    $binds = array(
                                        'purchase_id'    => $purchase_id,
                                        'order_id'       => $id,
                                        'tracking_code'       => $tracking_code,
                                        'courier_tracking_code' => $courier_tracking_code,
                                        'courier_code' => $courier_code,
                                        'status' => 'confirm'
                                    );

                                
                                    $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                                    $write->query($query, $binds);

                                    $j++;
                                } //end foreach
                               
                            } //end foreach
                            $results = $conn->fetchAll("
                                    SELECT  order_id ,
                                            tracking_code,
                                            courier_tracking_code,
                                            courier_code
                                    FROM shippop
                                    WHERE shippop_purchase_id = $purchase_id ");
                            foreach ($results as $list) {
                                // var_dump($list); die;
                                //echo "<br>Order id : ";
                                 $order_id = $list["order_id"];
                               // echo "<br>";
                                 $tracking_code = $list["tracking_code"];
                                 $courier_tracking_code = $list["courier_tracking_code"];
                                 $courier_code = strtolower($list["courier_code"]);
                                
                                // new ----------------------
                                $order     = Mage::getModel('sales/order')->load($order_id);
                                 $orderCode = $order->getIncrementId();
                                // Create Qty array
                                $shipmentItems = array();
                                foreach ($order->getAllItems() as $item) {
                                    $shipmentItems [$item->getId()] = $item->getQtyToShip();
                                }

                                // Prepear shipment and save ....
                                if ($order->getId() && !empty($shipmentItems) && $order->canShip()) {
                                    $order->getId();
                                    
                                    $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($shipmentItems);
                                    $shipment->save();
                                    // Add tracking
                                    $track = Mage::getModel('sales/order_shipment_track')
                                        ->setNumber($tracking_code)
                                        ->setCarrierCode($courier_code)
                                        ->setTitle("Ship By ".$courier_code." (Shippop)");
                                    // Set state & status
                                    $shipment->addTrack($track)
                                        ->save();
                                    if($order->hasInvoices() == true){
                                        $order->setStatus('complete');
                                        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                                    }else{
                                        $order->setStatus('processing');
                                        $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
                                    }
                                    $order->save();
                                    ++$numAffectedOrders; 
                                    $result_msg = $orderCode;
                                }

                            } // end foreach
                            
                        
                            
                            $result_msg = $hlp->__('Shippop purchase id #%d have been successfully shipped.', $purchase_id);
                            
                            
                        }else{ // confirm true
                            $this->_errors[] =  "Confirm false";

                        } // end confirm false
                    }else{
                        $this->_errors[] = "Book false";
                    }
                    // book false
                }else{
                    
                    $result_msg = $checkprice_status;
                }
            }else{
                $this->_errors[] = 'Shipped False,Data response wrong';
            }

        }elseif(!$checkprices["response"]["data"]){
            if($checkprices["response"]["message"]){
                $result_msg = $checkprices["response"]["message"];
            }else{
                $this->_errors[] = "false";
            }
        }else{
                $this->_errors[] = "false";
            
        }
        return $result_msg;
    }
    public function checkprice($ids,$val) {
           $apikey = Mage::getStoreConfig('Ship/config_api/apikey', Mage::app()->getStore());
           $url = Mage::getStoreConfig('Ship/config_api/url', Mage::app()->getStore());
           $sender = Mage::getStoreConfig('Ship/address/name', Mage::app()->getStore());
           $full_address = Mage::getStoreConfig('Ship/address/full_address', Mage::app()->getStore());
           $postcode = Mage::getStoreConfig('Ship/address/postcode', Mage::app()->getStore());
           $phoenumber = Mage::getStoreConfig('Ship/address/phoenumber', Mage::app()->getStore());

           $val = strtoupper($val);
           $i = 0;
           $array_order = array();
           foreach ($ids as $id) {
                $_order = Mage::getModel('sales/order')->load($id);

                if($_order->getState() != 'processing'){
                    Mage::getSingleton('core/session')->addError('Error : Please create invoice for order #'.$_order->getIncrementId());
                    continue;
                }

                $weight = $_order->getWeight();
                $weight = $weight*1000;
                $shipping = $_order->getShippingAddress();

            $array_order[$i] = array(
                'from'=>array(
                    'name'=>$sender,
                    'address'=>$full_address,
                    'district'=>'-',
                    'state'=>'-',
                    'province'=>'-',
                    'postcode'=>$postcode,
                    'tel'=>$phoenumber
                ),
                'to'=>array(
                    'name'      =>$shipping->getName(),
                    'address'   =>$shipping->getStreetFull(). $shipping->getSubdistrict() .', '. $shipping->getCity() .', '. $shipping->getRegion() .', '. $shipping->getPostcode(),
                    'district'  =>$shipping->getSubdistrict(),
                    'state'     =>$shipping->getCity(),
                    'province'  =>$shipping->getRegion(),
                    'postcode'  =>$shipping->getPostcode(),
                    'tel'       =>$shipping->getTelephone()
                ),
                'parcel'=>array(
                    'name'=>'-',
                    'weight'=> $weight,
                    'width'=> 1,
                    'length'=> 1,
                    'height'=> 1
                ),
                'courier_code' => $val
                // ,
                // 'showall'=> true
            );

            $i++;
            }
        $post_data = array(
            'api_key'=> $apikey,
            'data' => array()
        );
        $post_data['data'] = $array_order;
        
        $post_data = http_build_query($post_data);
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, 'http://mkpservice.'.$url.'/pricelist/');
        curl_setopt($curl,CURLOPT_POST, sizeof($post_data));
        curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($curl);
        curl_close($curl);

        return array(
            'orders' => $array_order,
            'response' => json_decode($result, true),
        );
    }
    public function booking($array_order,$val) {
        $apikey = Mage::getStoreConfig('Ship/config_api/apikey', Mage::app()->getStore());
        $email = Mage::getStoreConfig('Ship/config_api/email', Mage::app()->getStore());
        $url = Mage::getStoreConfig('Ship/config_api/url', Mage::app()->getStore());
        $sender = Mage::getStoreConfig('Ship/address/name', Mage::app()->getStore());
        $full_address = Mage::getStoreConfig('Ship/address/full_address', Mage::app()->getStore());
        $postcode = Mage::getStoreConfig('Ship/address/postcode', Mage::app()->getStore());
        $phoenumber = Mage::getStoreConfig('Ship/address/phoenumber', Mage::app()->getStore());

        $val = strtoupper($val);
        $post_data = array(
            'api_key'=>$apikey,
            'data' => array(),
            'email' => $email
        );
        $post_data['data'] = $array_order;

        $post_data = http_build_query($post_data);
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, 'http://mkpservice.'.$url.'/booking/');
        curl_setopt($curl,CURLOPT_POST, sizeof($post_data));
        curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

    function confirm( $purchase_id ) {
        $apikey = Mage::getStoreConfig('Ship/config_api/apikey', Mage::app()->getStore());
        $url = Mage::getStoreConfig('Ship/config_api/url', Mage::app()->getStore());
        $post_data = array(
            'api_key'=>$apikey,
            'purchase_id' => $purchase_id
        );
        $post_data = http_build_query($post_data);
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, 'http://mkpservice.'.$url.'/confirm/');
        curl_setopt($curl,CURLOPT_POST, sizeof($post_data));
        curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }
    function tracking_purchase( $purchase_id ) {
        $apikey = Mage::getStoreConfig('Ship/config_api/apikey', Mage::app()->getStore());
        $email = Mage::getStoreConfig('Ship/config_api/email', Mage::app()->getStore());
        $url = Mage::getStoreConfig('Ship/config_api/url', Mage::app()->getStore());
        $post_data = array(
            'api_key'=> $apikey,
            'purchase_id' => $purchase_id,
            'email' => $email
        );
        $post_data = http_build_query($post_data);
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, 'http://mkpservice.'.$url.'/tracking_purchase/');
        curl_setopt($curl,CURLOPT_POST, sizeof($post_data));
        curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

    protected function _getValueField($title)
    {
        $hlp = Mage::helper('amshiprestriction');
        $field = array('amoaction_value' => array(
            'name'   => 'amoaction_value',
            'type'   => 'select',
            //'values'  => $hlp->getAllCarriers(),
            'values' => array(
                array('label' => 'CJE', 'value' => 'cje'),
            ),
            'class'  => 'required-entry',
            'label'  => $title,
        ));
        return $field;
    }
}