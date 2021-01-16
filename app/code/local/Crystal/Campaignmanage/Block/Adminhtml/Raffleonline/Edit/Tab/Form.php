<?php

class Crystal_Campaignmanage_Block_Adminhtml_Raffleonline_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function getCategoriesArray()
    {
        $categoriesArray = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')
            ->addFieldToFilter('is_active', array('eq' => '1'))
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
                $b = '';
                for ($i = 1; $i < $category['level']; $i++) {
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

    public function getStoresArray()
    {
        $storesCollection = Mage::getModel('storepickup/store')->getCollection();

        $stores = array();

        foreach ($storesCollection as $store) {
            $stores[] = array(
                'label' => $store->getStoreName(),
                'value' => $store->getId()
            );
        }
        return $stores;
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
                'legend' => Mage::helper('campaignmanage')->__('Raffle Detail'))
        );

        $data = Mage::registry('raffleonline_data')->getData();
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

        $fieldset->addField('content', 'textarea', array(
            'label' => Mage::helper('campaignmanage')->__('Content'),
            'name' => 'content',
        ));
        $fieldset->addField('start_register_time', 'datetime', array(
            'label' => Mage::helper('campaignmanage')->__('Start Register Time'),
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'input_format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'name' => 'start_register_time',
            'time' => true,
            'required' => true,
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));
        $fieldset->addField('end_register_time', 'datetime', array(
            'label' => Mage::helper('campaignmanage')->__('End Register Time'),
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'input_format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
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

        $fieldset->addField('allow_pickup', 'select', array(
            'label' => Mage::helper('campaignmanage')->__('Pickup?'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'allow_pickup',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('campaignmanage')->__('Yes'),),
                array('value' => 0, 'label' => Mage::helper('campaignmanage')->__('No'),),
            )
        ));

        $storesArr = $this->getStoresArray();
        $fieldset->addField('stores_active', 'multiselect', array(
            'label' => Mage::helper('campaignmanage')->__('Choose stores'),
            'name' => 'stores_active',
            'values' => $storesArr
        ));

        $fieldset->addField('allow_shipping', 'select', array(
            'label' => Mage::helper('campaignmanage')->__('Allow Shipping?'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'allow_shipping',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('campaignmanage')->__('Yes'),),
                array('value' => 0, 'label' => Mage::helper('campaignmanage')->__('No'),),
            )
        ));
        $methodOptions = Mage::getModel('amtable/method')->getCollection()->toOptionArray();
        $options = array();
        foreach ($methodOptions as $method) {
            $options[$method['value']] = $method['label'];
        }
        $fieldset->addField('am_table_method_id', 'select', array(
            'label' => Mage::helper('ruffle')->__('Delivery Method'),
            'title' => Mage::helper('ruffle')->__('Delivery Method'),
            'name' => 'am_table_method_id',
            'required' => true,
            'options' => $options,
        ));
        $fieldset->addField('is_card_payment', 'select', array(
            'label' => Mage::helper('campaignmanage')->__('Allow to choose Credit Card?'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'is_card_payment',
            'values' => array(
                array('value' => 1, 'label' => Mage::helper('campaignmanage')->__('Yes'),),
                array('value' => 0, 'label' => Mage::helper('campaignmanage')->__('No'),),
            )
        ));

        $fieldset->addField('category_id', 'select', array(
            'label' => $this->__('Category Id'),
            'title' => $this->__('Category Id'),
            'name' => 'category_id',
            'after_element_html' => '<br/><small> For Raffle Type</small>',
            'values' => $this->getCategoriesArray(),
        ));


        $fieldset->addField('point_spent', 'text', array(
            'label' => Mage::helper('campaignmanage')->__('Rewards Points'),
            'name' => 'point_spent'
        ));
        if (Mage::getSingleton('adminhtml/session')->getRaffleonlineData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getRaffleonlineData());
            Mage::getSingleton('adminhtml/session')->setRaffleonlineData(null);
        } elseif (Mage::registry('raffleonline_data')) {
            $form->setValues($data);
        }
        return parent::_prepareForm();
    }
}
