<?php

/**
 * Class Tigren_Kerry_Model_Observer
 */
class Tigren_Kerry_Model_Observer
{
    /**
     * @param $observer
     * @throws Exception
     */

    public function isAllowedBooking()
    {
        return Mage::getSingleton('admin/session')->isAllowed('kerry/booking');
    }

    /**
     * @return mixed
     */
    public function isAllowedPrint()
    {
        return Mage::getSingleton('admin/session')->isAllowed('kerry/print');
    }

    /**
     * @param $observer
     * @throws Exception
     */
    public function addCustomBtn($observer)
    {
        /** @var Mage_Adminhtml_Block_Sales_Order_Shipment_View $block **/
        $block = $observer->getEvent()->getBlock();
        $controllerName = $block->getRequest()->getControllerName();
        $action = $block->getRequest()->getActionName();
        if(get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_Shipment_View' && $controllerName == 'sales_order_shipment' && $action == 'view'){
            if(method_exists(get_class($block),'addButton')){
                $shipment = $block->getShipment();
                if($shipment->getData('booking_status')){
                    if($this->isAllowedPrint()) {
                        $block->addButton(
                            'print_awb',
                            array(
                                'label' => Mage::helper('kerry')->__('Print AWB'),
                                'class' => 'cancel save primary',
                                'onclick' => "setLocation('" . $block->getUrl('kerry/adminhtml_bill/print', array('shipment_id' => $shipment->getId())) . "')"
                            ),
                            1,
                            1000
                        );
                    }
                }else{
                    if($this->isAllowedBooking()) {
                        $block->addButton(
                            'booking_shipment',
                            array(
                                'label' => Mage::helper('kerry')->__('Booking Kerry'),
                                'class' => 'cancel save primary',
                                'onclick' => "jQuery('#booking-modal').show()"
                            ),
                            1,
                            1000
                        );
                    }
                }
            }
        }

        /** @var Mage_Adminhtml_Block_Sales_Shipment_Grid $block **/
        $block = $observer->getEvent()->getBlock();
        if($controllerName == 'sales_shipment' && $action == 'index'){
            if(method_exists(get_class($block),'_prepareColumns')){
//                $block->addColumnAfter(
//                    'consignment_no',
//                    array(
//                        'header' => Mage::helper('sales')->__('Consignment No'),
//                        'index' => 'consignment_no',
//                    ),
//                    'total_qty'
//                );
//                $block->addColumnAfter(
//                    'booking_created_at',
//                    array(
//                        'header' => Mage::helper('sales')->__('Booking At'),
//                        'index' => 'booking_created_at',
//                        'type'      => 'datetime',
//                    ),
//                    'consignment_no'
//                );
//                $block->addColumnAfter(
//                    'cod_amount',
//                    array(
//                        'header' => Mage::helper('sales')->__('COD'),
//                        'index' => 'cod_amount',
//                        'type'  => 'currency',
//                        'currency' => 'base_currency_code',
//                    ),
//                    'booking_created_at'
//                );
//                $block->addColumnAfter(
//                    'action',
//                    array(
//                        'header'    => Mage::helper('sales')->__('Air Way Bill'),
//                        'width'     => '50px',
//                        'type'      => 'action',
//                        'getter'     => 'getId',
//                        'actions'   => array(
//                            array(
//                                'caption' => Mage::helper('sales')->__('Print'),
//                                'url'     => array('base' => 'kerry/adminhtml_bill/print'),
//                                'field'   => 'shipment_id'
//                            )
//                        ),
//                        'filter'    => false,
//                        'sortable'  => false,
//                        'is_system' => true
//                    ),
//                    'cod_amount'
//                );
//                $block->sortColumnsByOrder();
            }
        }
    }
}