<?php

class Crystal_Campaignmanage_Block_Adminhtml_Cropanddrop_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function getSizeArray($productId)
    {
        $_product = Mage::getModel('catalog/product')->load($productId);
        if ($_product->isConfigurable()) {
            $allProducts = $_product->getTypeInstance(true)->getUsedProducts(null, $_product);
            foreach ($allProducts as $subproduct) {
                if ($subproduct->getIsInStock() == 1)
                    $sizes[] = array(
                        'label' => $subproduct->getAttributeText('size_products'),
                        'value' => $subproduct->getData('size_products')
                    );
            }
        } else {
            $sizes[] = array(
                'label' => $_product->getAttributeText('size_products'),
                'value' => $_product->getData('size_products')
            );
        }
        return $sizes;
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
                'legend' => Mage::helper('campaignmanage')->__('Crop and Drop Detail'))
        );

        $data = Mage::registry('cropanddrop_data')->getData();


        if ($data['product_id']){
            $fieldset->addField('product_id', 'text', array(
                'label' => Mage::helper('campaignmanage')->__('Product Id'),
                'name' => 'product_id',
                'required' => true,
                'disabled' => 'disabled'
            ));
            $fieldset->addField('size', 'select', array(
                'label' => $this->__('Size'),
                'title' => $this->__('Size'),
                'name' => 'size',
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->getSizeArray($data['product_id']),
                'after_element_html' => '<br/> Notification Will Be Send After Save'
            ));
        } else{
            $fieldset->addField('product_id', 'text', array(
                'label' => Mage::helper('campaignmanage')->__('Product Id'),
                'name' => 'product_id',
                'required' => true
            ));
        }


        if (Mage::getSingleton('adminhtml/session')->getCropanddropData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCropanddropData());
            Mage::getSingleton('adminhtml/session')->setCropanddropData(null);
        } elseif (Mage::registry('cropanddrop_data')) {

            $form->setValues($data);
        }
        return parent::_prepareForm();
    }
}