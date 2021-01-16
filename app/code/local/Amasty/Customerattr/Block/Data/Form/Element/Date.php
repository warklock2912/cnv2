<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Customerattr
 */
class Amasty_Customerattr_Block_Data_Form_Element_Date
    extends Varien_Data_Form_Element_Date
{
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    public function getElementHtml()
    {
        $this->addClass('input-text');

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s style="width:110px !important; float:left;" />'
            . ' <img src="%s" alt="" class="v-middle" id="%s_trig" title="%s" style="position: relative;top: 8px;left:3px%s" />'
            . '<div style="clear: both;"></div>',
            $this->getName(), $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $this->getImage(), $this->getHtmlId(), 'Select Date',
            ($this->getDisabled() ? 'display:none;' : '')
        );
        $outputFormat = $this->getFormat();
        if (empty($outputFormat)) {
            throw new Exception(
                'Output format is not specified. Please, specify "format" key in constructor, or set it using setFormat().'
            );
        }
        $displayFormat = Varien_Date::convertZendToStrFtime(
            $outputFormat, true, (bool)$this->getTime()
        );

        $html .= sprintf(
            '
            <script type="text/javascript">
            //<![CDATA[
                Calendar.setup({
                    inputField: "%s",
                    ifFormat: "%s",
                    showsTime: %s,
                    button: "%s_trig",
                    align: "Bl",
                    singleClick : true
                });
            //]]>
            </script>',
            $this->getHtmlId(), $displayFormat,
            $this->getTime() ? 'true' : 'false', $this->getHtmlId()
        );

        $html .= $this->getAfterElementHtml();

        return $html;
    }

}
