<?php

/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package	Plumrocket_Cart_Reservation-v1.5.x
@copyright	Copyright (c) 2013 Plumrocket Inc. (http://www.plumrocket.com)
@license	http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 
*/

class Plumrocket_Cartreservation_Block_System_Time extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->getElementHtml($element);
    }

    public function getElementHtml($element)
    {
        $element->addClass('select');

        $value_days = 0;
        $value_hrs = 0;
        $value_min = 0;
        $value_sec = 0;

        if($value = $element->getValue()) {
            $values = explode(',', $value);
            if(is_array($values) && count($values) == 4) {
                $value_days = $values[0];
                $value_hrs = $values[1];
                $value_min = $values[2];
                $value_sec = $values[3];
            }
        }

        $html = '<input type="hidden" id="' . $element->getHtmlId() . '" />';

        $html .= '<select name="'. $element->getName() . '" '.$element->serialize($element->getHtmlAttributes()).' style="width:40px">'."\n";
        for($i=0;$i<90;$i++) {
            $day = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$day.'" '. ( ($value_days == $i) ? 'selected="selected"' : '' ) .'>' . $day . '</option>';
        }

        $html.= '</select>&nbsp;&nbsp;'."\n";


        $html .= '<select name="'. $element->getName() . '" '.$element->serialize($element->getHtmlAttributes()).' style="width:40px">'."\n";
        for($i=0;$i<24;$i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_hrs == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }

        $html.= '</select>'."\n";

        $html.= '&nbsp;:&nbsp;<select name="'. $element->getName() . '" '.$element->serialize($element->getHtmlAttributes()).' style="width:40px">'."\n";
        for($i=0;$i<60;$i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_min == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }

        $html.= '</select>'."\n";

        $html.= '&nbsp;:&nbsp;<select name="'. $element->getName() . '" '.$element->serialize($element->getHtmlAttributes()).' style="width:40px">'."\n";
        for($i=0;$i<60;$i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_sec == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }

        $html.= '</select>'."\n";
        $html.= $element->getAfterElementHtml();
        return $html;
    }
}
