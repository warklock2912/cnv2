<?php
class Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Tab_Winner extends Mage_Adminhtml_Block_Widget_Grid {
	public function __construct() {
        parent::__construct();
        $this->setId('ruffle_winner_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        $this->setChild('email_individual_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Send Email to Selected Winner'),
                    'onclick'   => 'emailToSelectedWinner()',
                ))
        );

        $this->setChild('email_to_all_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Send Email to Winners'),
                    'onclick'   => 'setLocation(\' '  . $this->getUrl('*/*/emailToAllWinner', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
                ))
        );
        $this->setChild('email_to_all_looser_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Send Email to Loser'),
                    'onclick'   => 'setLocation(\' '  . $this->getUrl('*/*/emailToAllLooser', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
                ))
        );
        $this->setChild('clear_winner_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Clear Winner'),
                    'onclick'   => 'setLocation(\' '  . $this->getUrl('*/*/clearWinner', array('_secure' => true, 'id' => $this->getRequest()->getParam('id'))) . '\')',
                ))
        );
    }

    public function getMainButtonsHtml() {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getEmailAllButtonHtml(); 
        $html .= $this->getEmailAllLooserButtonHtml(); 
        $html .= $this->getClearWinnerButtonHtml(); 
        $html .= $this->getEmailIndividualButtonHtml(); 
        return $html;
    }

    public function getEmailAllButtonHtml() {
        return $this->getChildHtml('email_to_all_button');
    }
    public function getEmailAllLooserButtonHtml() {
        return $this->getChildHtml('email_to_all_looser_button');
    }
    public function getClearWinnerButtonHtml() {
        return $this->getChildHtml('clear_winner_button');
    }

    public function getEmailIndividualButtonHtml() {
        return $this->getChildHtml('email_individual_button');
    }

    protected function _addColumnFilterToCollection($column) {
        // Set custom filter for in product flag
        if ($column->getId() == 'selected_members') {
            $memberIds = $this->_getSelectedMembers();
            if (empty($memberIds)) {
                $memberIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $memberIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $memberIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _getRuffle() {
    	$id = $this->getRequest()->getParam('id');
    	return Mage::getModel('ruffle/ruffle')->load($id);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('ruffle/joiner')->getCollection();
        $collection->addFieldToFilter('ruffle_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('is_winner', 1);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('selected_members', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'selected_members',
            'values'            => $this->_getSelectedMembers(),
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

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('ruffle')->__('Product Name'),
            'index'     => 'product_name',
            'renderer' => 'Tigren_Ruffle_Block_Adminhtml_Ruffle_Edit_Renderer_Productname'
        ));
        $this->addColumn('msg', array(
            'header'    => Mage::helper('catalog')->__('Note'),
            'index'     => 'msg'
        ));
      $this->addExportType('*/*/exportCsv',
        Mage::helper('ruffle')->__('CSV'));
        return parent::_prepareColumns();
    }

	public function getGridUrl() {
        return $this->getUrl('*/*/winnerGrid', array('_current' => true));
    }

    protected function _getSelectedMembers() {
        // $members = $this->getRuffleMembers();
        // if (!is_array($members)) {
        //     $members = array_keys($this->getSelectedRuffleMember());
        // }
        // return $members;
        return array();
    }

    public function getSelectedRuffleMember() {
        $members = array();
    	
        return $members;
    }
}