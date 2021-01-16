<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 *
 * @extension   Auto-Cancel Orders
 * @type        Order management
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Magento Commerce
 * @package     Appmerce_AutoCancel
 * @copyright   Copyright (c) 2011-2013 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_AutoCancel_Block_Methods extends Mage_Core_Block_Html_Select
{
    /**
     * Methods cache
     *
     * @var array
     */
    private $_methods;

    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     */
    protected $_addAllOption = true;

    /**
     * Retrieve active payment methods
     */
    protected function _getMethods($methodCode = null)
    {
        if (is_null($this->_methods)) {
            $this->_methods = array();
            $methods = Mage::getSingleton('payment/config')->getActiveMethods();
            foreach ($methods as $code => $model) {
                $this->_methods[$code] = Mage::getStoreConfig('payment/' . $code . '/title');
            }

            // Sort for human legibility
            natcasesort($this->_methods);
        }

        if (!is_null($methodCode)) {
            return isset($this->_methods[$methodCode]) ? $this->_methods[$methodCode] : null;
        }

        return $this->_methods;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            if ($this->_addAllOption) {
                $this->addOption('default', Mage::helper('autocancel')->__('--Please Select--'));
            }
            foreach ($this->_getMethods() as $code => $label) {
                $this->addOption($code, $label);
            }
        }
        return parent::_toHtml();
    }

}
