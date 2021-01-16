<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Model_Observer
{
    /**
     * Add columns (if `Show on Orders Grid` set to `Yes`) to the Orders Grid.
     *
     * @param Varien_Event_Observer $observer
     */
    public function addFilterToMap($collection,$column){
        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        $collection->addFilterToMap($field,$field." ");
        $cond = $column->getFilter()->getCondition();
        if ($field && isset($cond)) {
            $collection->addFieldToFilter($field , $cond);
        }
    }

    public function modifyOrderGrid($observer)
    {
        $layout = Mage::getSingleton('core/layout');
        if (!$layout) {
            return;
        }

        $permissibleActions = array('index', 'grid');
        if (false === strpos(
                Mage::app()->getRequest()->getControllerName(), 'sales_order'
            )
            || !in_array(
                Mage::app()->getRequest()->getActionName(), $permissibleActions
            )
        ) {
            return;
        }

        $attributesCollection = Mage::getModel('customer/attribute')
            ->getCollection();
        $filters = array(
            "is_user_defined =  1",
            "attribute_code != 'customer_activated' "
        );
        $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
            $attributesCollection, 'eav_attribute', $filters
        );
        $filters = array(
            "used_in_order_grid = 1"
        );
        $sorting = 'sorting_order';
        $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
            $attributesCollection,
            'customer_eav_attribute',
            $filters,
            $sorting
        );

        $grid = $layout->getBlock(
            'sales_order.grid'
        ); // Mage_Adminhtml_Block_Sales_Order_Grid
        if (($attributesCollection->getSize() > 0) && ($grid)) {
            $after = 'grand_total';
            foreach ($attributesCollection as $attribute) {
                $column = array();
                $filterIndex = "COALESCE(`_table_"
                    . $attribute->getAttributeCode()
                    . "`.`value`,`_table_guest_" . $attribute->getAttributeCode(
                    ) . "`.`" . $attribute->getAttributeCode() . "`)";
                switch ($attribute->getFrontendInput()) {
                    case 'date':
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $filterIndex,
                            'type'         => 'date',
                            'align'        => 'center',
                            'gmtoffset'    => true,
                            'filter_condition_callback' => array($this,"addFilterToMap")
                        );
                        break;
                    case 'select':
                    case 'selectimg':
                        $options = array();
                        foreach (
                            $attribute->getSource()->getAllOptions(
                                false, true
                            ) as $option
                        ) {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $filterIndex,
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'filter_condition_callback' => array($this,"addFilterToMap")
                        );
                        break;
                    case 'multiselect':
                    case 'multiselectimg':
                        $options = array();
                        foreach (
                            $attribute->getSource()->getAllOptions(
                                false, true
                            ) as $option
                        ) {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $filterIndex,
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                            'filter_condition_callback' => array($this,"addFilterToMap")
                        );
                        break;
                    case 'boolean':
                        $options = array(0 => 'No', 1 => 'Yes');
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $filterIndex,
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                            'filter_condition_callback' => array($this,"addFilterToMap")
                        );
                        break;
                    default:
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $filterIndex,
                            'align'        => 'center',
                            'sortable'     => true,
                            'filter_condition_callback' => array($this,"addFilterToMap")
                        );
                        break;
                }
                if ('file' == $attribute->getTypeInternal()) {
                    $column['renderer']
                        = 'amcustomerattr/adminhtml_renderer_file';
                }
                $grid->addColumnAfter(
                    $attribute->getAttributeCode(), $column, $after
                ); // Mage_Adminhtml_Block_Widget_Grid
                $after = $attribute->getAttributeCode();
            }
        }
    }

    /**
     * Join columns to the Orders Collection.
     *
     * @param Varien_Event_Observer $observer
     */
    public function modifyOrderCollection($observer)
    {
        $permissibleActions = array('index', 'grid', 'exportCsv',
                                    'exportExcel');
        if (false === strpos(
                Mage::app()->getRequest()->getControllerName(), 'sales_order'
            )
            || !in_array(
                Mage::app()->getRequest()->getActionName(), $permissibleActions
            )
        ) {
            return;
        }

        $collection = $observer->getOrderGridCollection()->setIsCustomerMode(true);
        $tableNameCustomerEntity = Mage::getSingleton('core/resource')
            ->getTableName('customer_entity');
        $tableNameGuestEntity = Mage::getSingleton('core/resource')
            ->getTableName('amcustomerattr/guest');
        $attributesCollection = Mage::getModel('customer/attribute')
            ->getCollection();

        $filters = array(
            "is_user_defined = 1",
            "attribute_code != 'customer_activated'"
        );
        $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
            $attributesCollection, 'eav_attribute', $filters
        );

        $filters = array(
            "used_in_order_grid = 1"
        );
        $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
            $attributesCollection,
            'customer_eav_attribute',
            $filters
        );

        if ($attributesCollection->getSize() > 0) {
            foreach ($attributesCollection as $attribute) {
                if ($this->_isJoined(
                    $collection->getSelect()->getPart('from'),
                    '_table_' . $attribute->getAttributeCode()
                )
                ) {
                    continue;
                }
                $collection->getSelect()
                    ->joinLeft(
                        array('_table_' . $attribute->getAttributeCode(
                        ) => $tableNameCustomerEntity . "_"
                            . $attribute->getBackendType()),
                        "_table_" . $attribute->getAttributeCode()
                        . ".entity_id = main_table.customer_id " .
                        " AND _table_" . $attribute->getAttributeCode()
                        . ".attribute_id = " . $attribute->getAttributeId(),
                        array($attribute->getAttributeCode() =>
                                  "COALESCE(`_table_"
                                  . $attribute->getAttributeCode()
                                  . "`.`value`," .
                                  "`_table_guest_"
                                  . $attribute->getAttributeCode() . "`.`"
                                  . $attribute->getAttributeCode() . "`)")
                    )->joinLeft(
                        array("_table_guest_" . $attribute->getAttributeCode(
                        ) => $tableNameGuestEntity),
                        "_table_guest_" . $attribute->getAttributeCode()
                        . ".order_id = main_table.entity_id", array()
                    );
            }
        }
    }

    protected function _isJoined($from, $check)
    {
        $found = false;
        foreach ($from as $alias => $data) {
            if ($check === $alias) {
                $found = true;
            }
        }
        return $found;
    }

    /**
     * Handler for event `controller_action_layout_render_before_adminhtml_customer_index`.
     *
     * @param Varien_Event_Observer $observer
     */
    public function forIndexCustomerGrid($observer)
    {
        $layout = Mage::getSingleton('core/layout');
        if (!$layout) {
            return;
        }

        $permissibleActions = array('index', 'grid');
        if (false === strpos(
                Mage::app()->getRequest()->getControllerName(), 'customer'
            )
            || !in_array(
                Mage::app()->getRequest()->getActionName(), $permissibleActions
            )
        ) {
            return;
        }

        $grid = $layout->getBlock('customer.grid');
        $grid = $this->_modifyCustomerGrid($grid);
    }

    /**
     * Add columns (if `Show on Manage Customers Grid` set to `Yes`) to the Manage Customers Grid.
     *
     * @param Varien_Event_Observer $observer
     */
    protected function _modifyCustomerGrid($grid)
    {
        $attributesCollection = Mage::getModel('customer/attribute')
            ->getCollection();

        $filters = array(
            "is_user_defined = 1",
            "attribute_code != 'customer_activated' "
        );
        $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
            $attributesCollection,
            'eav_attribute',
            $filters
        );

        $filters = array(
            "is_filterable_in_search = 1"
        );
        $sorting = 'sorting_order';
        $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
            $attributesCollection,
            'customer_eav_attribute',
            $filters,
            $sorting
        );

        if (($attributesCollection->getSize() > 0) && ($grid)) {
            if (!Mage::app()->isSingleStoreMode()) {
                $after = 'website_id';
            } else {
                $after = 'customer_since';
            }
            foreach ($attributesCollection as $attribute) {
                $column = array();
                switch ($attribute->getFrontendInput()) {
                    case 'date':
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'type'         => 'date',
                            'align'        => 'center',
                            'gmtoffset'    => true
                        );
                        break;
                    case 'select':
                    case 'selectimg':
                        $options = array();
                        foreach (
                            $attribute->getSource()->getAllOptions(
                                false, true
                            ) as $option
                        ) {
                            if (isset($option['value']) && $option['value']) {
                                if (is_array($option['value'])) {
                                    foreach ($option['value'] as $opt) {
                                        $options[$opt['value']] = $opt['label'];
                                    }
                                } else {
                                    $options[$option['value']]
                                        = $option['label'];
                                }
                            }
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                        );
                        break;
                    case 'multiselect':
                    case 'multiselectimg':
                        $options = array();
                        foreach (
                            $attribute->getSource()->getAllOptions(
                                false, true
                            ) as $option
                        ) {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                        );
                        break;
                    case 'boolean':
                        $options = array(0 => 'No', 1 => 'Yes');
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                        );
                        break;
                    default:
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'sortable'     => true,
                        );
                        break;
                }
                if ('file' == $attribute->getTypeInternal()) {
                    $column['renderer']
                        = 'amcustomerattr/adminhtml_renderer_file';
                }
                $grid->addColumnAfter(
                    $attribute->getAttributeCode(), $column, $after
                ); // Mage_Adminhtml_Block_Widget_Grid
                $after = $attribute->getAttributeCode();
            }
        }

        // add column for `Admin Activation` feature
        $after = (!Mage::app()->isSingleStoreMode()) ? 'website_id'
            : 'customer_since';
        $options = array(0 => 'Pending', 1 => 'No', 2 => 'Yes');
        $column = array(
            'header'       => 'Activated',
            'index'        => 'am_is_activated',
            'filter_index' => 'am_is_activated',
            'align'        => 'center',
            'type'         => 'options',
            'options'      => $options,
            'renderer'     => 'amcustomerattr/adminhtml_renderer_activationStatus',
        );
        $grid->addColumnAfter('am_is_activated', $column, $after);

        return $grid;
    }

    /**
     * Handler for event `core_layout_block_create_after`.
     *
     * @param Varien_Event_Observer $observer
     */
    public function forSearchCustomerGrid($observer)
    {
        if ('index' === Mage::app()->getRequest()->getActionName()) {
            return;
        }

        $grid = $observer->getBlock();
        if ($grid instanceof Mage_Adminhtml_Block_Customer_Grid) {
            $grid = $this->_modifyCustomerGrid($grid);
        }
    }

    /**
     * Join columns to the Customers Collection.
     *
     * @param Varien_Event_Observer $observer
     */
    public function modifyCustomerCollection($observer)
    {
        $collection = $observer->getCollection();
        if ($collection instanceof
            Mage_Customer_Model_Entity_Customer_Collection
            || $collection instanceof
            Mage_Customer_Model_Resource_Customer_Collection
        ) {
            $attributesCollection = Mage::getModel('customer/attribute')
                ->getCollection();

            $filters = array(
                "is_user_defined = 1",
                "attribute_code != 'customer_activated' "
            );
            $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
                $attributesCollection,
                'eav_attribute',
                $filters
            );

            $filters = array(
                "is_filterable_in_search = 1"
            );
            $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
                $attributesCollection,
                'customer_eav_attribute',
                $filters
            );

            if ($attributesCollection->getSize() > 0) {
                foreach ($attributesCollection as $attribute) {
                    $collection->addAttributeToSelect(
                        $attribute->getAttributeCode()
                    );
                }
            }

            // add `activated` attribute to data selection
            $attributesCollectionFull = Mage::getModel('customer/attribute')
                ->getCollection();
            foreach ($attributesCollectionFull as $attribute) {
                $attrCode = $attribute->getAttributeCode();
                if ($attrCode == 'am_is_activated') {
                    $collection->addAttributeToSelect($attrCode);
                    break;
                }
            }
        }
    }

    public function handleBlockOutput($observer)
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();

        $transport = $observer->getTransport();

        $html = $transport->getHtml();

        $salesOrderViewTabInfoClass = Mage::getConfig()->getBlockClassName(
            'adminhtml/sales_order_view_tab_info'
        );
        if ($salesOrderViewTabInfoClass == get_class(
                $block
            )
        ) { // Order View Page
            $customerId = $block->getOrder()->getCustomerId();
            $tempPos = strpos($html, '<!--Account Information-->');
            if (false !== $tempPos) {
                $pos = strpos($html, '</table>', $tempPos);
                $storeId = $block->getOrder()->getStoreId();
                if ($accountData = Mage::helper('amcustomerattr')
                    ->getCustomerAccountData($customerId, $storeId)
                ) {
                    $insert = '';
                    foreach ($accountData as $data) {
                        $insert
                            .= '
                                <tr>
                                    <td class="label"><label>' . $data['label']
                            . '</label></td>
                                    <td class="value"><strong>' . $data['value']
                            . '</strong></td>
                                </tr>';
                    }
                    $html = substr_replace($html, $insert, $pos - 1, 0);
                }
            }
        }

        // Login
        if (Mage::getStoreConfig('amcustomerattr/login/login_field')) { // Login
            $attributesHash = Mage::helper('amcustomerattr')->getAttributesHash(
            );
            if (isset($attributesHash[Mage::getStoreConfig(
                    'amcustomerattr/login/login_field'
                )])) { // check if isset attribute
                $loginClasses = array();
                $loginClasses[] = Mage::getConfig()->getBlockClassName(
                    'checkout/onepage_login'
                );
                $loginClasses[] = Mage::getConfig()->getBlockClassName(
                    'customer/form_login'
                );
                if (in_array(get_class($block), $loginClasses)) { // check block
                    if (Mage::getStoreConfig(
                        'amcustomerattr/login/disable_email'
                    )
                    ) {
                        $replaceWith = $attributesHash[Mage::getStoreConfig(
                            'amcustomerattr/login/login_field'
                        )];
                    } else {
                        $replaceWith = Mage::helper('amcustomerattr')->__(
                                'Email Address'
                            ) . '/' . $attributesHash[Mage::getStoreConfig(
                                'amcustomerattr/login/login_field'
                            )];
                    }
                    $html = str_replace(
                        Mage::helper('amcustomerattr')->__('Email Address'),
                        $replaceWith, $html
                    );
                    $html = str_replace('validate-email', '', $html);
                    $html = str_replace('type="email"', 'type="text"', $html);
                }
            }
        }

        if (Mage::getStoreConfig(
            'amcustomerattr/forgot/forgot_field'
        )
        ) { // Forgot Password Page
            $attributesHash = Mage::helper('amcustomerattr')->getAttributesHash(
            );
            if (isset($attributesHash[Mage::getStoreConfig(
                    'amcustomerattr/forgot/forgot_field'
                )])) { // check if isset attribute
                $forgotpasswordClass = Mage::getConfig()->getBlockClassName(
                    'customer/account_forgotpassword'
                );
                if ($forgotpasswordClass == get_class($block)) { // check block
                    // replace url for form action
                    $html = str_replace(
                        'customer/account/forgotpasswordpost',
                        'amcustomerattr/attachment/forgotpasswordpost', $html
                    );

                    // remove JS validation
                    $html = str_replace('validate-email', '', $html);

                    // replace field title
                    if ($insert = Mage::getStoreConfig(
                        'amcustomerattr/forgot/field_title',
                        Mage::app()->getStore()->getId()
                    )
                    ) {
                        $pos = strpos($html, '</em>');
                        $tempPos = strpos($html, '</label>');
                        $length = $tempPos - $pos - 5;
                        $html = substr_replace(
                            $html, $insert, $pos + 5, $length
                        );
                    }

                    // replace text on the page
                    if ($insert = Mage::getStoreConfig(
                        'amcustomerattr/forgot/text',
                        Mage::app()->getStore()->getId()
                    )
                    ) {
                        $pos = strpos($html, '<p>');
                        $tempPos = strpos($html, '</p>');
                        $length = $tempPos - $pos - 3;
                        $html = substr_replace(
                            $html, $insert, $pos + 3, $length
                        );
                    }
                }
            }
        }

        $formEditClass = Mage::getConfig()->getBlockClassName(
            'adminhtml/customer_edit_tab_account'
        );
        if ($formEditClass == get_class(
                $block
            )
        ) { // Customer Edit Page (Adminhtml)
            $customerId = Mage::app()->getRequest()->getParam('id');
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $isActivated = $customer->getAmIsActivated();
            $pos = strpos($html, 'id="_accountbase_fieldset"');
            $pos = strpos($html, '</tr>', $pos);
            $insert
                = '<tr>
                                <td class="label"><label for="am_is_activated">Account activated</label></td>
                                <td class="value">
                                    <select id="am_is_activated" name="account[am_is_activated]" class=" select">
                                        <option value="0" ' . ($isActivated
                == 0 ? 'selected="selected" ' : '') . '>Pending</option>
                                        <option value="1" ' . ($isActivated
                == 1 ? 'selected="selected" ' : '') . '>No</option>
                                        <option value="2" ' . ($isActivated
                == 2 ? 'selected="selected" ' : '') . '>Yes</option>
                                    </select>
                                </td>
                            </tr>';
            $html = substr_replace($html, $insert, $pos + 5, 0);
        }

        $flag = false;
        $formRegisterClass = Mage::getConfig()->getBlockClassName(
            'customer/form_register'
        );
        $formEditClass = Mage::getConfig()->getBlockClassName(
            'customer/form_edit'
        );

        if ($formRegisterClass == get_class($block)) {
            $flag = true;
            $column = 'on_registration';
        }
        if ($formEditClass == get_class($block)) {
            $flag = true;
            $column = '';
        }

        $onepageBillingClass = Mage::getConfig()->getBlockClassName(
            'checkout/onepage_billing'
        );
        if ($onepageBillingClass == get_class($block)) {
            $flag = true;
            $column = 'used_in_product_listing';
        }

        if (Mage::getStoreConfig(
            'amcustomerattr/general/front_auto_output'
        )
        ) { // check if can to try auto output
            if ($formRegisterClass == get_class($block)) { // Registration Page
                $flag = true;
                if (Mage::helper('amcustomerattr')->getFileAttributes(
                        'on_registration'
                    )->getSize() > 0
                ) {
                    $html = str_replace(
                        'id="form-validate"',
                        ' id="form-validate" enctype="multipart/form-data" ',
                        $html
                    );
                }
                if (false === strpos($html, 'amcustomerattr')) {
                    $pos = strpos($html, '<div class="buttons-set');
                    $insert = Mage::helper('amcustomerattr')->fields();
                    $html = substr_replace($html, $insert, $pos - 1, 0);
                }
            }

            if ($formEditClass == get_class($block)) { // Account Edit Page
                $flag = true;
                if (Mage::helper('amcustomerattr')->getFileAttributes()
                        ->getSize() > 0
                ) { // need for upload
                    $html = str_replace(
                        'id="form-validate"',
                        ' id="form-validate" enctype="multipart/form-data" ',
                        $html
                    );
                }
                if (false === strpos($html, 'amcustomerattr')) {
                    $pos = strpos($html, '<div class="buttons-set">');
                    $insert = Mage::helper('amcustomerattr')->fields();
                    $html = substr_replace($html, $insert, $pos - 1, 0);
                }
            }

            if ($onepageBillingClass == get_class($block)
                && 'express' !== Mage::app()->getRequest()->getControllerName()
            ) { // PayPal Express (attributes do not need)
                $flag = true;
                if (false === strpos($html, 'amcustomerattr')) {
                    if ($block->isCustomerLoggedIn()) {
                        $pos = strpos($html, '<div class="buttons-set"') - 1;
                    } else {
                        $pos = strpos(
                            $html,
                            '<li class="fields" id="register-customer-password">'
                        );// + 51;
                    }
                    $insert = Mage::helper('amcustomerattr')->fields();
                    $html = substr_replace($html, $insert, $pos, 0);
                }
            }

        }

        if ($flag
            && false !== strpos(
                $html, '<!-- Customer Attributes Relations -->'
            )
        ) {
            $insert = Mage::app()->getLayout()->createBlock(
                'amcustomerattr/customer_fields_relations'
            )->setParts($column)->toHtml();
            $pos = strripos($html, '<!-- Customer Attributes Relations -->')
                + 38;
            $html = substr_replace($html, $insert, $pos, 0);
        }
        $transport->setHtml($html);
    }

    public function onCoreLayoutBlockCreateAfter($observer)
    {
        $block = $observer->getBlock();
        // Order Grid
        $permissibleActions = array('exportCsv', 'exportExcel');
        if (($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid
                || $block instanceof
                EM_DeleteOrder_Block_Adminhtml_Sales_Order_Grid)
            && (in_array(
                Mage::app()->getRequest()->getActionName(), $permissibleActions
            ))
        ) {
            // Customer Attributes
            $attributesCollection = Mage::getModel('customer/attribute')
                ->getCollection();
            $filters = array(
                "is_user_defined = 1",
                "attribute_code != 'customer_activated' "
            );
            $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
                $attributesCollection,
                'eav_attribute',
                $filters
            );
            $filters = array(
                "used_in_order_grid = 1"
            );
            $sorting = 'sorting_order';
            $attributesCollection = Mage::helper('amcustomerattr')->addFilters(
                $attributesCollection,
                'customer_eav_attribute',
                $filters,
                $sorting
            );

            if (($attributesCollection->getSize() > 0) && ($block)) {
                $after = 'grand_total';
                foreach ($attributesCollection as $attribute) {
                    $column = array();
                    $filterIndex = "COALESCE(`_table_"
                        . $attribute->getAttributeCode()
                        . "`.`value`,`_table_guest_"
                        . $attribute->getAttributeCode() . "`.`"
                        . $attribute->getAttributeCode() . "`)";
                    switch ($attribute->getFrontendInput()) {
                        case 'date':
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(
                                ),
                                'index'        => $attribute->getAttributeCode(
                                ),
                                'filter_index' => $filterIndex,
                                'type'         => 'date',
                                'align'        => 'center',
                                'gmtoffset'    => true
                            );
                            break;
                        case 'select':
                        case 'selectimg':
                            $options = array();
                            foreach (
                                $attribute->getSource()->getAllOptions(
                                    false, true
                                ) as $option
                            ) {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(
                                ),
                                'index'        => $attribute->getAttributeCode(
                                ),
                                'filter_index' => $filterIndex,
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                            );
                            break;
                        case 'multiselect':
                        case 'multiselectimg':
                            $options = array();
                            foreach (
                                $attribute->getSource()->getAllOptions(
                                    false, true
                                ) as $option
                            ) {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(
                                ),
                                'index'        => $attribute->getAttributeCode(
                                ),
                                'filter_index' => $filterIndex,
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                                'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                                'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                            );
                            break;
                        case 'boolean':
                            $options = array(0 => 'No', 1 => 'Yes');
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(
                                ),
                                'index'        => $attribute->getAttributeCode(
                                ),
                                'filter_index' => $filterIndex,
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                                'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                            );
                            break;
                        default:
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(
                                ),
                                'index'        => $attribute->getAttributeCode(
                                ),
                                'filter_index' => $filterIndex,
                                'align'        => 'center',
                                'sortable'     => true,
                            );
                            break;
                    }
                    if ('file' == $attribute->getTypeInternal()) {
                        $column['renderer']
                            = 'amcustomerattr/adminhtml_renderer_file';
                    }
                    $block->addColumnAfter(
                        $attribute->getAttributeCode(), $column, $after
                    ); // Mage_Adminhtml_Block_Widget_Grid
                    $after = $attribute->getAttributeCode();
                }
            }
        }
    }

    public function handleBlockOutputBefore($observer)
    {
        $block = $observer->getBlock();
        $massactionClass = Mage::getConfig()->getBlockClassName(
            'adminhtml/widget_grid_massaction'
        );
        $customerGridClass = Mage::getConfig()->getBlockClassName(
            'adminhtml/customer_grid'
        );
        $parentClass = get_class($block->getParentBlock());
        if ($massactionClass == get_class($block)
            && $parentClass == $customerGridClass
        ) {
            $block->addItem(
                'deactivate',
                array(
                    'label'   => Mage::helper('amcustomerattr')->__(
                        'Deactivate'
                    ),
                    'url'     => Mage::helper("adminhtml")->getUrl(
                        'adminhtml/amcustomerattractivation/massDeactivate'
                    ),
                    'confirm' => Mage::helper('amcustomerattr')->__(
                        'Are you sure?'
                    ))
            );
            $block->addItem(
                'activate',
                array(
                    'label'   => Mage::helper('amcustomerattr')->__('Activate'),
                    'url'     => Mage::helper("adminhtml")->getUrl(
                        'adminhtml/amcustomerattractivation/massActivate'
                    ),
                    'confirm' => Mage::helper('amcustomerattr')->__(
                        'Are you sure?'
                    ))
            );
        }

    }

    public function onSalesQuoteSaveAfter($observer)
    {
        $data = $observer->getData();
        $customer = $data['order']->getData('customer')->getData('entity_id');
        if (!is_null($customer)) {
            return;
        }
        $attributes = Mage::getSingleton('checkout/session')->getAmcustomerattr(
        );
        if ($attributes === null
            || Mage::app()->getRequest()->getControllerName()
            == 'sales_order_edit'
            || Mage::app()->getRequest()->getControllerName()
            == 'sales_order_create'
        ) {
            $attributes = Mage::app()->getRequest()->getPost('amcustomerattr');
        }
        $attributes['order_id'] = $data['order']->getEntityId();
        $fileAttributes = array();
        if (!empty($attributes)) {
            if (isset($_FILES['amcustomerattr']['error'])
                && !empty($_FILES['amcustomerattr']['error'])
            ) {
                foreach (
                    $_FILES['amcustomerattr']['error'] as $attributeCode =>
                    $error
                ) {
                    if (UPLOAD_ERR_OK == $error) {
                        $temp = explode(
                            '.',
                            $_FILES['amcustomerattr']['name'][$attributeCode]
                        );
                        $ext = strtolower(array_pop($temp));
                        $fileName = Mage::helper('amcustomerattr')
                            ->getCorrectFileName($temp[0]);
                        $f1 = Mage::helper('amcustomerattr')->getFolderName(
                            $fileName[0]
                        );
                        $f2 = Mage::helper('amcustomerattr')->getFolderName(
                            $fileName[1]
                        );
                        $fileDestination = Mage::getBaseDir('media') . DS
                            . 'customer' . DS . $f1 . DS . $f2 . DS;
                        if (file_exists(
                            $fileDestination . $fileName . '.' . $ext
                        )) { // check if exist file with the same name
                            $attributeValue = DS . $f1 . DS . $f2 . DS . uniqid(
                                    date('ihs')
                                ) . $fileName . '.' . $ext;
                        } else {
                            $attributeValue = DS . $f1 . DS . $f2 . DS
                                . $fileName . '.' . $ext;
                        }
                        $attributes[$attributeCode] = $attributeValue;
                        $fileAttributes[$attributeCode] = $attributeValue;
                    }
                }
            }
            $model = Mage::getModel('amcustomerattr/guest');
            $model->setData($attributes);
            try {
                $model->save();
            } catch (Exception $e) {
                echo $e->getMessage();
                return;
            }
            if (!empty($fileAttributes)) {
                foreach ($fileAttributes as $attributeCode => $fileName) {
                    try {
                        $uploader = new Varien_File_Uploader(
                            'amcustomerattr[' . $attributeCode . ']'
                        );
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $destinationFolder = Mage::helper('amcustomerattr')
                            ->getAttributeFileUrl($fileName);
                        $fileName = Mage::helper('amcustomerattr')
                            ->cleanFileName($fileName);
                        $uploader->save(
                            $destinationFolder . $fileName[1] . DS
                            . $fileName[2] . DS, $fileName[3]
                        );
                    } catch (Exception $error) {
                        $e = new Mage_Customer_Exception(
                            Mage::helper('amcustomerattr')->__(
                                'An error occurred while saving the file: '
                                . $error->getMessage()
                            )
                        );
                        if (method_exists($e, 'setMessage')) {
                            $e->setMessage(
                                Mage::helper('amcustomerattr')->__(
                                    'An error occurred while saving the file: '
                                    . $error
                                )
                            );
                        }
                        throw $e;
                    }
                }
            }

        }
    }

    public function onChangeAttribute($observer)
    {
        Mage::helper('amcustomerattr/guest')->update();
    }
}
