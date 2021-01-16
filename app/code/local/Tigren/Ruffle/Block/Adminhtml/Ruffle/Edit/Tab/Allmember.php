<?php

class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Tab_Allmember extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ruffle_allmember_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        // $this->setChild('manual_winner_button',
        //     $this->getLayout()->createBlock('adminhtml/widget_button')
        //         ->setData(array(
        //             'label'     => Mage::helper('adminhtml')->__('Manual Select Winner'),
        //             'onclick'   => 'manualSelectWinner(\'allmember\')',
        //         ))
        // );

        // $this->setChild('random_winner_button',
        //     $this->getLayout()->createBlock('adminhtml/widget_button')
        //         ->setData(array(
        //             'label'     => Mage::helper('adminhtml')->__('Random Select Winner'),
        //             'onclick'   => 'setLocation(\' '  . $this->getUrl('*/*/randomAllmember', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
        //         ))
        // );
        $this->setChild('random_allwinner_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Random ALL Winner'),
                    'onclick' => 'setLocation(\' '.$this->getUrl('*/*/randomAllmember', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))).'\')',
                ))
        );
        $this->setChild('random_winner_quota',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('adminhtml')->__('Random Winner Quota'),
                    'onclick' => 'setLocation(\' '.$this->getUrl('*/*/randomQuota', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))).'\')',
                ))
        );
    }

    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getManualWinnerButtonHtml();
        $html .= $this->getRandomWinnerButtonHtml();
        $html .= $this->getAllRandomWinnerButtonHtml();
        $html .= $this->getRandomQuotaButtonHtml();
        return $html;
    }

    public function getManualWinnerButtonHtml()
    {
        return $this->getChildHtml('manual_winner_button');
    }

    public function getRandomWinnerButtonHtml()
    {
        return $this->getChildHtml('random_winner_button');
    }

    public function getAllRandomWinnerButtonHtml()
    {
        return $this->getChildHtml('random_allwinner_button');
    }


    public function getRandomQuotaButtonHtml()
    {
        return $this->getChildHtml('random_winner_quota');
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

    protected function _getRuffle()
    {
        $id = $this->getRequest()->getParam('id');

        return Mage::getModel('ruffle/ruffle')->load($id);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ruffle/joiner')->getCollection();
        $collection->addFieldToFilter('ruffle_id', $this->getRequest()->getParam('id'));
        $groupId = Tigren_Ruffle_Model_Ruffle::RUFFLE_VIP_GROUP_ID;
        $tableName = Mage::getModel('core/resource')->getTableName('customer/entity');

        $regionNameTable = Mage::getModel('core/resource')->getTableName('directory_country_region_name');
        $cityNameTable = Mage::getModel('core/resource')->getTableName('directory_region_city_name');
        $subdistrictNameTable = Mage::getModel('core/resource')->getTableName('directory_city_subdistrict_name');

        $regionTable = Mage::getModel('core/resource')->getTableName('directory_country_region');
        $cityTable = Mage::getModel('core/resource')->getTableName('directory_region_city');
        $subdistrictTable = Mage::getModel('core/resource')->getTableName('directory_city_subdistrict');
        $locale = Mage::getSingleton('adminhtml/session')->getLocale();
        if ($locale != 'th_TH') {
            $locale = 'en_US';
        }

        if ($locale == 'th_TH') {
            $collection->getSelect()->join(
                array('c' => $tableName), 'main_table.customer_id=c.entity_id', array('c.*'))
                ->joinLeft(array('r_table' => $regionTable), 'main_table.region_id = r_table.region_id', array('region' => 'r_table.default_name'))
                ->joinLeft(array('c_table' => $cityTable), 'main_table.city_id = c_table.city_id', array('city' => 'c_table.default_name'))
                ->joinLeft(array('s_table' => $subdistrictTable), 'main_table.subdistrict_id = s_table.subdistrict_id', array('subdistrict' => 's_table.default_name'))
                ->where('c.group_id in (1,4)');
        } else {
            $collection->getSelect()->join(
                array('c' => $tableName), 'main_table.customer_id=c.entity_id', array('c.*'))
                ->joinLeft(array('r_table' => $regionTable), 'main_table.region_id = r_table.region_id', array('region' => 'r_table.code'))
                ->joinLeft(array('c_table' => $cityTable), 'main_table.city_id = c_table.city_id', array('city' => 'c_table.code'))
                ->joinLeft(array('s_table' => $subdistrictTable), 'main_table.subdistrict_id = s_table.subdistrict_id', array('subdistrict' => 's_table.code'))
                ->where('c.group_id in (1,4)');
        }
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
            'index' => 'entity_id',
            'is_system' => true,
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'sortable' => true,
            'width' => 60,
            'index' => 'entity_id',
        ));

        $this->addColumn('doc_invoice', array(
            'header' => Mage::helper('catalog')->__('Doc/ Invoice No.'),
            'index' => 'doc_invoice',
        ));

        $this->addColumn('ruffle_number', array(
            'header' => Mage::helper('catalog')->__('Raffle Number'),
            'index' => 'ruffle_number',
        ));


        $this->addColumn('firstname', array(
            'header'    => Mage::helper('ruffle')->__('First Name'),
            'index'     => 'firstname'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('ruffle')->__('Last Name'),
            'index'     => 'lastname'
        ));
        $this->addColumn('personal_id', array(
            'header' => Mage::helper('ruffle')->__('Personal id'),
            'index' => 'personal_id',
        ));

        $this->addColumn('telephone', array(
            'header' => Mage::helper('catalog')->__('Telephone'),
            'index' => 'telephone',
        ));

        $this->addColumn('email_address', array(
            'header' => Mage::helper('catalog')->__('Email Address'),
            'index' => 'email_address',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('catalog')->__('Product Name'),
            'index' => 'product_name',
            'renderer' => 'Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Renderer_Productname',
        ));

        $this->addColumn('msg', array(
            'header' => Mage::helper('catalog')->__('Note'),
            'index' => 'msg',
        ));

        $this->addColumn('customer_ruffle_address', array(
            'header' => Mage::helper('catalog')->__('Address'),
            'index' => 'customer_ruffle_address',
        ));

        $stores = Mage::getModel('storepickup/store')->getCollection();
        $storeOptions = array();

        foreach ($stores as $store) {
            $storeOptions [$store->getId()] = $store->getStoreName();
        }

        $this->addColumn('storepickup_id', array(
            'header' => Mage::helper('catalog')->__('Store Pickup'),
            'index' => 'storepickup_id',
            'type' => 'options',
            'options' => $storeOptions,
        ));


        $this->addColumn('country_id', array(
            'header' => Mage::helper('catalog')->__('Country'),
            'index' => 'country_id',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('region', array(
            'header' => Mage::helper('catalog')->__('Region'),
            'index' => 'region',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('city', array(
            'header' => Mage::helper('catalog')->__('City'),
            'index' => 'city',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('subdistrict', array(
            'header' => Mage::helper('catalog')->__('Subdistrict'),
            'index' => 'subdistrict',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('postcode', array(
            'header' => Mage::helper('catalog')->__('Poscode'),
            'index' => 'postcode',
            'filter' => false,
            'sortable' => false,
        ));

        $this->addColumn('send_email', array(
            'header'    => Mage::helper('catalog')->__('Send Email At'),
            'index'     => 'send_email',
						'type'			=> 'datetime'
        ));

        $this->addExportType('*/*/exportAllMemCsv', Mage::helper('ruffle')->__('CSV'));
        $this->addExportType('*/*/exportAllMemXml', Mage::helper('ruffle')->__('XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/allmemberGrid', array('_current' => true));
    }

    protected function _getSelectedMembers()
    {
        $members = $this->getRuffleMembers();
        if (!is_array($members)) {
            $members = array_keys($this->getSelectedRuffleMember());
        }

        return $members;
    }

    public function getSelectedRuffleMember()
    {
        $members = array();

        return $members;
    }
}
