<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Tab_Allmember extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('raffleOnlineAllUserGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(TRUE);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('campaignmanage/raffleonline')->getCollection();
        $collection->addFieldToFilter('raffle_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('raffle_random_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('campaignmanage')->__('Random All Winner(s)'),
                    'name' => 'raffle_random',
                    'element_name' => 'raffle_random',
                    'onclick' => 'setLocation(\' ' . $this->getUrl('*/*/randomAllWinner', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
                    'class' => 'raffle_random',
                ))
        );
        $this->setChild('random_winner_quota',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('campaignmanage')->__('Random Winner Quota'),
                    'name' => 'random_winner_quota',
                    'element_name' => 'random_winner_quota',
                    'onclick' => 'setLocation(\' ' . $this->getUrl('*/*/randomWinnerQuota', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
                    'class' => 'raffle_random',
                ))
        );

    }

    public function getRandomWinnerQuotaButton()
    {
        return $this->getChildHtml('random_winner_quota');
    }

    /**
     * @return string
     */
    public function getRaffleRandomButtonHtml()
    {
        return $this->getChildHtml('raffle_random_button');
    }

    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getRaffleRandomButtonHtml();
        $html .= $this->getRandomWinnerQuotaButton();
        return $html;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('campaignmanage')->__('ID'),
            'sortable' => true,
            'width' => 60,
            'index' => 'id'
        ));
        $this->addColumn('customer_id', array(
            'header' => Mage::helper('campaignmanage')->__('Customer ID'),
            'sortable' => false,
            'index' => 'customer_id'
        ));
        $this->addColumn('customer_name', array(
            'header' => Mage::helper('campaignmanage')->__('Name'),
            'sortable' => false,
            'index' => 'customer_name'
        ));
        $this->addColumn('email', array(
            'header' => Mage::helper('campaignmanage')->__('Email'),
            'sortable' => false,
            'index' => 'email'
        ));
        $this->addColumn('phone', array(
            'header' => Mage::helper('campaignmanage')->__('Phone Number'),
            'sortable' => false,
            'index' => 'phone'
        ));
        $this->addColumn('card_id', array(
            'header' => Mage::helper('campaignmanage')->__('Personal ID'),
            'sortable' => false,
            'index' => 'card_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('campaignmanage')->__('Product Name'),
            'index' => 'product_name',
            'filter' => false,
            'renderer' => 'Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Renderer_Productname',
        ));

        $this->addColumn('is_winner', array(
            'header' => Mage::helper('campaignmanage')->__('Is Winner?'),
            'sortable' => true,
            'index' => 'is_winner'
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('campaignmanage')->__('Created At'),
            'sortable' => false,
            'index' => 'created_at',
            'type' => 'datetime'
        ));
        $this->addExportType('*/*/exportMemberCsv', Mage::helper('campaignmanage')->__('CSV'));
        $this->addExportType('*/*/exportMemberXml', Mage::helper('campaignmanage')->__('XML'));

        return parent::_prepareColumns();
    }
}
