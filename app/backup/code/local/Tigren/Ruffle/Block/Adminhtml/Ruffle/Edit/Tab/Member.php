<?php
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Tab_Member extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct() {
        parent::__construct();
        $this->setId('ruffle_member_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setChild('manual_winner_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Manual Select Winner'),
                    'onclick'   => 'manualSelectWinner(\'general\')',
                ))
        );

        $this->setChild('random_winner_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Random Select Winner'),
                    'onclick'   => 'setLocation(\' '  . $this->getUrl('*/*/randomAllmember', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
                ))
        );
    }

    public function getMainButtonsHtml() {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getManualWinnerButtonHtml(); 
        $html .= $this->getRandomWinnerButtonHtml(); 
        return $html;
    }

    public function getManualWinnerButtonHtml() {
        return $this->getChildHtml('manual_winner_button');
    }

    public function getRandomWinnerButtonHtml() {
        return $this->getChildHtml('random_winner_button');
    }

    protected function _addColumnFilterToCollection($column) {
        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    protected function _getRuffle() {
    	$id = $this->getRequest()->getParam('id');
    	return Mage::getModel('ruffle/ruffle')->load($id);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('ruffle/joiner')->getCollection();
        $collection->addFieldToFilter('ruffle_id', $this->getRequest()->getParam('id'));
        $groupId = Tigren_Ruffle_Model_Ruffle::RUFFLE_GENERAL_GROUP_ID; 
        $tableName = Mage::getModel('core/resource')->getTableName('customer/entity');

        $collection->getSelect()->join(
            array('c' => $tableName), 
            'main_table.customer_id=c.entity_id', 
            array('c.group_id')
        )
        ->where('c.group_id=?', $groupId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('general_joiner', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'selected_members',
            // 'values'            => $this->_getSelectedMembers(),
            'align'             => 'center',
            'index'             => 'joiner_id'
        ));

        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('ruffle')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'customer_id'
        ));

        $this->addColumn('ruffle_number', array(
            'header'    => Mage::helper('ruffle')->__('Raffle Number'),
            'index'     => 'ruffle_number'
        ));

        $this->addColumn('customer_name', array(
            'header'    => Mage::helper('ruffle')->__('Name'),
            'index'     => 'customer_name'
        ));

        $this->addColumn('personal_id', array(
            'header'    => Mage::helper('ruffle')->__('Personal id'),
            'index'     => 'personal_id'
        ));

        $this->addColumn('telephone', array(
            'header'    => Mage::helper('ruffle')->__('Telephone'),
            'index'     => 'telephone'
        ));

        $this->addColumn('email_address', array(
            'header'    => Mage::helper('ruffle')->__('Email Address'),
            'index'     => 'email_address'
        ));

        $this->addColumn('telephone', array(
            'header'    => Mage::helper('ruffle')->__('Telephone'),
            'index'     => 'telephone'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('ruffle')->__('Product Name'),
            'index'     => 'product_name',
          'renderer' => 'Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Renderer_Productname'
        ));

        $this->addColumn('msg', array(
            'header'    => Mage::helper('catalog')->__('Note'),
            'index'     => 'msg'
        ));
        return parent::_prepareColumns();
    }

	public function getGridUrl() {
        return $this->getUrl('*/*/memberGrid', array('_current' => true));
    }

    protected function _getSelectedMembers() {
        // $members = $this->getRuffleMembers();
        // if (!is_array($members)) {
        //     $members = array_keys($this->getSelectedRuffleMember());
        // }
        // $members = array(setCollection)
        return array();
        return $members;
    }

    public function getSelectedRuffleMember() {
        $members = array();

        // $ruffleItems = $this->_getRuffle()->getRuffleItems();
        // if (is_array($ruffleItems)) {
        //     foreach ($this->_getRuffle()->getRuffleItems() as $customerId => $position) {
        //         $members[$customerId] = array('position' => $position);
        //     }
        // }
    	
        return $members;
    }
}