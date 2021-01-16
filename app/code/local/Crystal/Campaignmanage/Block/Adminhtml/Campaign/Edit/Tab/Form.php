<?php

class Crystal_Campaignmanage_Block_Adminhtml_Campaign_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	public function getCategoriesArray()
	{
		$categoriesArray = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq'=>'1'))
            ->load()
            ->toArray();

        $categories = array();
        $categories[] = array(
            'label' => 'Select Category',
            'value' => null
        );
        foreach ($categoriesArray as $categoryId => $category) {
            if (!isset($category['name'])) {
                continue;
            }
            if (isset($category['name']) && isset($category['level'])) {
                $b ='';
                for($i=1;$i<$category['level'];$i++){
                    $b = $b . "--";
                }
                $categories[] = array(
                    'label' => $b . ' ' . $category['name'] . ' (' . $categoryId . ')',
                    'level' => $category['level'],
                    'value' => $categoryId
                );
            }
        }

        return $categories;
	}

	protected function getTimeLocale($time)
	{
		Mage::getSingleton('core/date')->gmtDate();
		return Mage::helper('core')->formatDate($time, 'short', true);
	}

	protected function _prepareForm()
	{

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('campaignmanage_form', array(
				'legend' => Mage::helper('campaignmanage')->__('Campaign Detail'))
		);
		if ($this->getRequest()->getParam('locator_id')) {
			Mage::registry('campaign_data')->setDealerlocatorId($this->getRequest()->getParam('locator_id'));
		}
		$data = Mage::registry('campaign_data')->getData();
		if (isset($data['image']) && $data['image'] != '') {
			$data['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'campaignmanage/images/' . $data['image'];
		}
		if (isset($data['start_register_time']) && $data['start_register_time'] != '') {
			$data['start_register_time'] = $this->getTimeLocale($data['start_register_time']);
		}
		if (isset($data['end_register_time']) && $data['end_register_time'] != '') {
			$data['end_register_time'] = $this->getTimeLocale($data['end_register_time']);
		}

		$fieldset->addField('campaign_name', 'text', array(
			'label' => Mage::helper('campaignmanage')->__('Campaign Name'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'campaign_name',
		));

		$campaignType = Crystal_Campaignmanage_Model_Campaigntype::getOptionArray();
		$fieldset->addField('campaign_type', 'select', array(
			'label' => Mage::helper('campaignmanage')->__('Campaign Type'),
			'name' => 'campaign_type',
			'required' => true,
			'values' => $campaignType
		));
		$fieldset->addField('queue_prefix', 'text', array(
			'label' => Mage::helper('campaignmanage')->__('Queue Prefix'),
			'name' => 'queue_prefix',
		));
		$fieldset->addField('content', 'textarea', array(
			'label' => Mage::helper('campaignmanage')->__('Content'),
			'name' => 'content',
		));
		$fieldset->addField('start_register_time', 'datetime', array(
			'label' => Mage::helper('campaignmanage')->__('Start Register Time'),
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'input_format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'locale'=>'en_US',
            'name' => 'start_register_time',
            'time' => true,
            'required' => true,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
		));
		$fieldset->addField('end_register_time', 'datetime', array(
			'label' => Mage::helper('campaignmanage')->__('End Register Time'),
			'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'input_format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'locale'=>'en_US',
			'name' => 'end_register_time',
			'time' => true,
			'required' => true,
			'image' => $this->getSkinUrl('images/grid-cal.gif'),
		));
		$fieldset->addField('no_of_part', 'text', array(
			'label' => Mage::helper('campaignmanage')->__('No. of participants'),
			'name' => 'no_of_part',
			'required' => true
		));
		if ($this->getRequest()->getParam('locator_id')) {

			$fieldset->addField('dealerlocator_id', 'hidden', array(
				'label' => Mage::helper('campaignmanage')->__('Dealer Locator ID'),
				'value' => 1,
				'readonly' => true,
				'name' => 'dealerlocator_id',
			));
		}
		$fieldset->addField('image', 'image', array(
			'label' => Mage::helper('campaignmanage')->__('Image'),
			'required' => FALSE,
			'name' => 'image',
		));


		$fieldset->addField('app_display', 'select', array(
			'label' => Mage::helper('campaignmanage')->__('Display on App?'),
			'class' => 'required-entry',
			'required' => true,
			'name' => 'app_display',
			'values' => array(
				array('value' => 1, 'label' => Mage::helper('campaignmanage')->__('Enabled'),),
				array('value' => 0, 'label' => Mage::helper('campaignmanage')->__('Disabled'),),
			)
		));

		$fieldset->addField('category_id', 'select', array(
			'label' => $this->__('Category Id'),
			'title' => $this->__('Category Id'),
			'name' => 'category_id',
			'after_element_html' => '<br/><small> For Raffle Type</small>',
			'values' => $this->getCategoriesArray(),
		));

        $fieldset->addField('points_cost', 'text', array(
            'label' => Mage::helper('campaignmanage')->__('Rewards Points'),
            'name' => 'points_cost'
        ));

		if (Mage::getSingleton('adminhtml/session')->getCampaignData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getCampaignData());
			Mage::getSingleton('adminhtml/session')->setCampaignData(null);
		} elseif (Mage::registry('campaign_data')) {

			$form->setValues($data);
		}
		return parent::_prepareForm();

	}
}
