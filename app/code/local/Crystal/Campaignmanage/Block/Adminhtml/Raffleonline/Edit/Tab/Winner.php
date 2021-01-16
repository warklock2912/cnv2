<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Tab_Winner extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('raffle_online_winners_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);

    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_members') {
            $memberIds = $this->_getSelectedMembers();
            if (empty($memberIds)) {
                $memberIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $memberIds));
            } else {
                if ($memberIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $memberIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('email_individual_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Send Email to Selected Winner'),
                    'onclick' => 'emailToSelectedWinner()',
                ))
        );

        $this->setChild('email_to_all_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Send Email to Winners'),
                    'onclick' => 'setLocation(\' '.$this->getUrl('*/*/emailToAllWinner', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))).'\')',
                ))
        );
        $this->setChild('email_to_all_looser_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Send Email to Loser'),
                    'onclick' => 'setLocation(\' '.$this->getUrl('*/*/emailToAllLooser', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))).'\')',
                ))
        );
    }

    public function getAddSelectedButton()
    {
        return $this->getChildHtml('add_item_button');
    }

    public function getMainButtonsHtml()
    {
        $html = '';
        $html .= parent::getMainButtonsHtml();
        $html .= $this->getEmailAllButtonHtml();
        $html .= $this->getEmailAllLooserButtonHtml();
        $html .= $this->getEmailIndividualButtonHtml();

        return $html;
    }

    public function getEmailAllButtonHtml()
    {
        return $this->getChildHtml('email_to_all_button');
    }

    public function getEmailAllLooserButtonHtml()
    {
        return $this->getChildHtml('email_to_all_looser_button');
    }

    public function getEmailIndividualButtonHtml()
    {
        return $this->getChildHtml('email_individual_button');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('campaignmanage/raffleonline')->getCollection();
        $collection->addFieldToFilter('raffle_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('is_winner', true)
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('selected_members', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'selected_members',
            'values' => $this->_getSelectedMembers(),
            'align' => 'center',
            'index' => 'id',
        ));

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

        $this->addColumn('product_id', array(
            'header' => Mage::helper('campaignmanage')->__('Product ID'),
            'sortable' => false,
            'index' => 'product_id'
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('campaignmanage')->__('Product Name'),
            'index' => 'product_name',
            'filter' => false,
            'renderer' => 'Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Renderer_Productname',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('campaignmanage')->__('Created At'),
            'sortable' => false,
            'index' => 'created_at',
            'type' => 'datetime'
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('campaignmanage')->__('Action'),
            'index' => 'action',
            'width' => '200px',
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
            'renderer' => 'Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Renderer_Action',
        ));

        $this->addExportType('*/*/exportWinnerCsv', Mage::helper('campaignmanage')->__('CSV'));
        $this->addExportType('*/*/exportWinnerXml', Mage::helper('campaignmanage')->__('XML'));

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/winner', array('_current' => true));
    }

    protected function _getSelectedMembers()
    {
        return array();
    }

    public function getSelectedRaffleMember()
    {
        $members = array();
        return $members;
    }
}
