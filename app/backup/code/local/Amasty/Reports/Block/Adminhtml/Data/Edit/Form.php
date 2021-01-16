<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */

class Amasty_Reports_Block_Adminhtml_Data_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post')
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        $hlp = Mage::helper('amreports');
        $model = Mage::registry('amreports_data');
        if (!$model->getData()) {
            $fldSettings = $this->_addFieldset($form,$hlp, 'settings', 'Report type');
            $this->_addReportType($fldSettings, $hlp);
            $this->_addHiddenField($fldSettings, 'newReport', '1');
            $this->_addSaveButton($fldSettings, $hlp);
        } else {
            if ($model->getData('orderStatuses')) {
                $model->setData('show_order_statuses', 1);
            }
            $fldSettings = $this->_addFieldset($form,$hlp,'settings', 'Settings');
            $reportProcessor = Mage::getSingleton(
                'amreports_reports/' . $model->getData('report_type')
            );

            $this->_addDisabledField($fldSettings, $hlp, 'report_type');

            $this->_addHiddenField($fldSettings, 'report_type');

            $this->_addHiddenField($fldSettings, 'json_answer');
            $firstRun = true;
            $this->_addJs($fldSettings);
            foreach($reportProcessor->getReportFields() as $field) {
                //we must add dateFrom and dateTo order
                if (is_array($model->getData('DateFrom')) && ($field=='DateFrom' || $field=='DateTo') ) {
                    if (!$firstRun) {
                        continue;
                    }
                    $dateFrom = $model->getData('DateFrom');
                    $dateTo = $model->getData('DateTo');
                    foreach ($dateFrom as $key => $date) {
                        if ($key==0) {
                            $this->_addDateFrom($fldSettings, $hlp, $key, $date, $dateTo[$key]);
                        } else {
                            $this->_addCompareDate($fldSettings, $date, $dateTo[$key]);
                        }
                    }
                    $firstRun = false;
                    continue;
                } else {
                    call_user_func(array($this, '_add'.$field),$fldSettings,$hlp);
                }
            }
            //css hack
            $this->_addCss($fldSettings);
            $this->_addReportButton($fldSettings, $hlp);
        }
        //set form values
        $data = Mage::getSingleton('adminhtml/session')->getFormData();
        if ($data) {
            $form->addValues($data);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif ($model) {
            $form->addValues($model->getData());
        }
        if ($model->getData()) {
            $this->_loadChart($form, $hlp, isset($data) ? $data : $model->getData() );
            $this->_addResultsTable($form, $hlp);
        }
        return parent::_prepareForm();
    }

    protected function _loadChart($form,$hlp,$data)
    {
        $fldGraph = $this->_addFieldset( $form,$hlp,'graph','Graph' );
        $fldGraph->addField('post_id', 'hidden', array(
            'after_element_html' => '<div class="amreports-chartSelector" id="chartselector" ></div><div class="amreports-chart" id="chartdiv" style="height:600px;"></div>',
        ));
    }

    protected function _addResultsTable($form,$hlp)
    {
        $fldTable = $this->_addFieldset( $form,$hlp,'results','Results' );
        $fldTable->addField('result', 'hidden', array(
            'after_element_html' => '<div class="sortable" id="resultTable" ></div>',
        ));
    }

    protected function _addOrderStatus(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $orderStatus = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        foreach($orderStatus as &$val) {
            $val['value'] = $val['status'];
            unset($val['url']);
        }
        $fieldset->addField('show_order_statuses', 'select', array(
            'name'      => 'show_order_statuses',
            'label'     => $hlp->__('Order Status'),
            'options'   => array(
                '0' => $hlp->__('Any'),
                '1' => $hlp->__('Specified'),
            ),
            'note'      => $hlp->__('Applies to Any of the Specified Order Statuses'),
        ), 'to');
        $fieldset->addField('OrderStatus', 'multiselect', array(
            'name'      => 'OrderStatus',
            'values'    => $orderStatus,
        ), 'show_order_statuses');
        // define field dependencies
        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
            ->addFieldMap("show_order_statuses", 'show_order_statuses')
            ->addFieldMap("OrderStatus", 'OrderStatus')
            ->addFieldDependence('OrderStatus', 'show_order_statuses', '1')
        );
    }

    protected function _addReportName( Varien_Data_Form_Element_Fieldset $fieldset,$hlp )
    {
        $fieldset->addField(
            'name', 'text', array(
                'label' => $hlp->__('Report Name'),
                'name'  => 'name',
                'required' => true,
            )
        );
    }

    protected function _addBestsellersCount( Varien_Data_Form_Element_Fieldset $fieldset,$hlp )
    {
        $fieldset->addField(
            'BestsellersCount', 'text', array(
                'label' => $hlp->__('Count Of Bestsellers'),
                'name'  => 'BestsellersCount',
                'required' => true,
            )
        );
    }
    protected function _addReportType(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $fieldset->addField(
            'report_type', 'select', array(
                'label'  => $hlp->__('Select Report Type'),
                'name'   => 'report_type',
                'values' => $hlp->getReportsTypes()
            )
        );
    }

    protected function _addOrdersBy(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $fieldset->addField(
            'OrdersBy', 'select', array(
                'label'  => $hlp->__('Select Orders By'),
                'name'   => 'OrdersBy',
                'values' => array(
                    'created_at'=>$hlp->__('Created at'),
                    'updated_at'=>$hlp->__('Updated at'),
                ),
            )
        );
    }

    protected function _addPeriod(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $fieldset->addField(
            'Period', 'select', array(
                'label'  => $hlp->__('Period'),
                'name'   => 'Period',
                'values' => array(
                    'TO_DAYS'=>$hlp->__('Day'),
                    'MONTH'=>$hlp->__('Month'),
                    'YEAR'=>$hlp->__('Year'),
                ),
            )
        );
    }

    protected function _addShowEmpty(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $fieldset->addField(
            'ShowEmpty', 'select', array(
                'label'  => $hlp->__('Show Empty Rows'),
                'name'   => 'ShowEmpty',
                'values' => array(
                    '0'=>$hlp->__('No'),
                    '1'=>$hlp->__('Yes'),
                ),
            )
        );
    }

    protected function _addHiddenField(Varien_Data_Form_Element_Fieldset $fieldset, $name)
    {
        $fieldset->addField(
            $name, 'hidden', array(
                'name'   => $name
            )
        );
    }

    protected function _addDisabledField(Varien_Data_Form_Element_Fieldset $fieldset, $hlp)
    {
        $fieldset->addField(
            'report_name_full', 'text', array(
                'label' => $hlp->__('Report Type'),
                'name'  => 'report_name_full',
                'disabled' => true,
            )
        );
    }

    protected function _addDateFrom(Varien_Data_Form_Element_Fieldset $fieldset, $hlp, $id=0, $value='', $value2='')
    {
        $label  = $hlp->__('Date From To');
        $fieldset->addType('lineDate', 'Amasty_Reports_Block_Adminhtml_Fields_Date');
        $fieldset->addField('DateFrom'.$id, 'lineDate', array(
            'name'               => 'DateFrom[]',
            'nameTo'             => 'DateTo[]',
            'idTo'               => 'DateTo'.$id,
            'label'              => $label,
            'tabindex'           => 1,
            'image'              => $this->getSkinUrl('images/grid-cal.gif'),
            'format'             => 'd/M/yyyy',//Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT) ,
            'value'              => $value,
            'dateTo'             => $value2,
            'required'           => true,
            'value_class'        => 'report-td'
        ));
    }

    protected function _addDateTo(Varien_Data_Form_Element_Fieldset $fieldset, $hlp)
    {
        return $this;
    }

    protected function _addCompareDate(Varien_Data_Form_Element_Fieldset $fieldset,$from,$to)
    {
        $fieldset->addField('jscompare', 'hidden', array(
            'after_element_html' => "<script>
            document.observe('dom:loaded', function() {
            amReports.addCompare('$from','$to');
            })
            </script>"
        ));
    }

    protected function _addCompare(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $fieldset->addField(
            'compare', 'link', array(
                'label'  => $hlp->__('Add Compare Date Range'),
                'name'   => 'compare',
                'href'   => '#',
                'value'     => '',
                'class'   => 'amreports-plusField',
                'onclick'  => 'amReports.addCompare();return false;',
            )
        );
    }

    protected function _addJs(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $url = Mage::helper("adminhtml")->getUrl("*/amreports_reports/ajax");
        $fieldset->addField('jsscripts', 'hidden', array(
            'after_element_html' => "<script type='text/javascript' src='https://www.google.com/jsapi'></script>
            <script> var amReports = new amReports('".$url."','".Mage::app()->getStore()->getBaseCurrencyCode()."') </script>
            "
        ));
    }

    protected function _addReportButton(Varien_Data_Form_Element_Fieldset $fieldset, $hlp)
    {
        $fieldset->addField('reportbutton', 'hidden', array(
            'after_element_html' => "
            <button class='amreports-submitButton' onclick='amReports.getReport();return false;'>".$hlp->__('Get Report')."</button>
            "
        ));
    }

    protected function _addSaveButton(Varien_Data_Form_Element_Fieldset $fieldset, $hlp)
    {
        $fieldset->addField('saveButton', 'hidden', array(
            'after_element_html' => "
            <button class='amreports-saveButton' onclick='editForm.submit();'>".$hlp->__('Create Report')."</button>
            "
        ));
    }

    protected function _addCss(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $fieldset->addField('cssHack', 'hidden', array(
            'after_element_html' => "
            <style>.form-list {
                width: 100%;
            }</style>
            "
        ));
    }

    protected function _addFieldset( $form,$hlp, $name,$legend )
    {
        $skin =  Mage::getDesign()->getSkinUrl('/Amasty/amreports/img/') ;//$this->getSkinUrl('/imagename.jpg');
        if ($legend=='Results') {
            /*
             * <li><a href="#" onclick="jQuery(\'#sorttable\').tableExport({type:\'excel\',escape:\'false\'});"> <img src="'.$skin.'xls.png"> XLS</a></li>
            <li class="divider"></li>
             *
             */
            $setting =  array('legend' => $hlp->__($legend) ,
                              'header_bar'=>'
<div id="export">
    <div class="btn-group">
        <button class="btn btn-warning btn-sm dropdown-toggle" onclick="jQuery(\'#exportmenu\').toggle();return false;" data-toggle="dropdown"><i class="fa fa-bars"></i> Export Table Data</button>
        <ul class="dropdown-menu " id="exportmenu" role="menu">
            <li><a href="#" onclick="jQuery(\'#sorttable\').tableExport({type:\'json\',escape:\'false\'});"> <img src="'.$skin.'json.png"> JSON</a></li>
            <li class="divider"></li>
            <li><a href="#" onclick="jQuery(\'#sorttable\').tableExport({type:\'xml\',escape:\'false\'});"> <img src="'.$skin.'xml.png"> XML</a></li>
            <li class="divider"></li>
            <li><a href="#" onclick="amReports.exportTableToCSV.apply(this, [jQuery(\'#sorttable\'), \'export.csv\'])"> <img src="'.$skin.'csv.png"> CSV</a></li>
            <li class="divider"></li>
            <li><a href="#" onclick="jQuery(\'#sorttable\').tableExport({type:\'png\',escape:\'false\'});"> <img src="'.$skin.'png.png"> PNG</a></li>
            <li><a href="#" onclick="amReports.pdfExport()"> <img src="'.$skin.'pdf.png"> PDF</a></li>
        </ul>
    </div>
</div>'
            );
        } else {
            $setting =  array('legend' => $hlp->__($legend));
        }
        return $form->addFieldset($name, $setting);
    }

    protected function _addStoreSelect(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'StoreSelect', 'multiselect', array(
                    'name'     => 'StoreSelect[]',
                    'label'    => $hlp->__('Store View'),
                    'title'    => $hlp->__('Store View'),
                    'values'   => Mage::getSingleton('adminhtml/system_store')
                        ->getStoreValuesForForm(false, true),
                )
            );
        } else {
            $fieldset->addField(
                'StoreSelect', 'hidden', array(
                    'name'  => 'StoreSelect[]',
                    'value' => Mage::app()->getStore(true)->getId()
                )
            );
        }
    }

    protected function _addProfitFormula(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $model = Mage::registry('amreports_data');
        $reportProcessor = Mage::getSingleton(
            'amreports_reports/' . $model->getData('report_type')
        );
        $fields = $reportProcessor->getFormulaFields();
        if ($model->getData('ProfitFormula')) {
            $values = implode(',', $model->getData('ProfitFormula'));
            $fieldset->addField('formulaScript', 'hidden', array(
                'after_element_html' => "<script>  amReports.generateFormulaFields('$values') </script>"
            ));
        }
        $model->setData('ProfitFormula','');
        $fieldset->addField(
            'ProfitFormula', 'select', array(
                'label'  => $hlp->__('Create Profit Formula'),
                'name'   => 'ProfitFormula[]',
                'values' => $fields,
                'after_element_html' => '<a href="#" id="plusField" class="amreports-plusField" onclick="amReports.addFormula(this);return false;"></a>'
            )
        );
    }

    protected function _addProductTypes(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $productType = Mage::getModel('catalog/product_type')->getAllOptions();
        $fieldset->addField(
            'ProductTypes', 'multiselect', array(
                'label'  => $hlp->__('Choose Product Types'),
                'name'   => 'ProductTypes[]',
                'values' => $productType
            )
        );
    }

    protected function _addSku(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $fieldset->addField(
            'Sku', 'text', array(
                'label'  => $hlp->__('Enter Product SKU'),
                'name'   => 'Sku',
                'required' => true,
            )
        );
    }

    protected function _addCustomerSelect(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $all = Mage::getModel('customer/group')->getCollection();
        $options = array();
        foreach($all as $group) {
            $options[$group->getId()]['value'] = $group->getId();//$group->getData('customer_group_code');
            $options[$group->getId()]['label'] = $group->getData('customer_group_code');
        }
        $fieldset->addField('CustomerSelect', 'multiselect', array(
            'label'     => $hlp->__('Select a Group'),
            'name'      => 'CustomerSelect',
            'values'    => $options
        ));
    }

    protected function _addCouponCode(Varien_Data_Form_Element_Fieldset $fieldset,$hlp)
    {
        $fieldset->addField(
            'CouponCode', 'text', array(
                'label'  => $hlp->__('Enter Coupon Code'),
                'name'   => 'CouponCode'
            )
        );
    }
}