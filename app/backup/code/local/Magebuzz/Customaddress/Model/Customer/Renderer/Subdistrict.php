<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Customaddress_Model_Customer_Renderer_Subdistrict implements Varien_Data_Form_Element_Renderer_Interface
{
  static protected $_subdistrictCollections;

  public function render(Varien_Data_Form_Element_Abstract $element)
  {
    $html = '<tr>'."\n";

    $cityId = false;
    if ($city = $element->getForm()->getElement('city_id')) {
      $cityId = $city->getValue();
    }
    $subdistrictCollection = false;
    if ($cityId) {
      if (!isset(self::$_subdistrictCollections[$cityId])) {
        self::$_subdistrictCollections[$cityId] = Mage::getModel('customaddress/subdistrict')
					->getCollection()
          ->addFieldToFilter('city_id', $cityId)
          ->load()
          ->toOptionArray();
      }
      $subdistrictCollection = self::$_subdistrictCollections[$cityId];
    }

    $subdistrictId = intval($element->getForm()->getElement('subdistrict_id')->getValue());

    if(empty($subdistrictId) || $subdistrictId < 1){
      $subdistrictId = "";
    }

    $htmlAttributes = $element->getHtmlAttributes();
    foreach ($htmlAttributes as $key => $attribute) {
      if ('type' === $attribute) {
        unset($htmlAttributes[$key]);
        break;
      }
    }

    $subdistrictHtmlName = $element->getName();
    $subdistrictIdHtmlName = str_replace('subdistrict', 'subdistrict_id', $subdistrictHtmlName);
    $subdistrictHtmlId = $element->getHtmlId();
    $subdistrictIdHtmlId = str_replace('subdistrict', 'subdistrict_id', $subdistrictHtmlId);

    if ($subdistrictCollection && count($subdistrictCollection) > 0) {
      $elementClass = $element->getClass();
      $html.= '<td class="label">'.$element->getLabelHtml().'</td>';
      $html.= '<td class="value">';

      $html .= '<select id="' . $subdistrictIdHtmlId . '" name="' . $subdistrictIdHtmlName . '" '
        . $element->serialize($htmlAttributes) .'>' . "\n";

      $selectedtext = '';
      foreach ($subdistrictCollection as $subdistrict) {
        //$selected = ($subdistrictId==$subdistrict['value']) ? ' selected="selected"' : '';

        $selected = '';
        $value = $subdistrict['value'];
        $value = empty($value) ? '' : ' value="'.$subdistrict['value'].'" ';
        if($subdistrictId == $subdistrict['value']) {
          $selected = ' selected="selected" ';
          $selectedtext = $subdistrict['label'];
        }
        $html.= '<option'.$value.$selected.'>'.$subdistrict['label'].'</option>';
      }
      $html.= '</select>' . "\n";

      $html .= '<input type="hidden" name="' . $subdistrictHtmlName . '" id="' . $subdistrictHtmlId . '" value="'.$selectedtext.'" />';

      $html.= '</td>';
      $element->setClass($elementClass);
    } else {
      $element->setClass('input-text');
      $html.= '<td class="label"><label for="'.$element->getHtmlId().'">'
        . $element->getLabel()
        . ' <span class="required" style="display:none">*</span></label></td>';

      $element->setRequired(false);
      $html.= '<td class="value">';
      $html .= '<input id="' . $subdistrictHtmlId . '" name="' . $subdistrictHtmlName
        . '" value="' . $element->getEscapedValue() . '" ' . $element->serialize($htmlAttributes) . "/>" . "\n";
      $html .= '<input type="hidden" name="' . $subdistrictIdHtmlName . '" id="' . $subdistrictIdHtmlId . '" value=""/>';
      $html .= '</td>'."\n";
    }
    $html.= '</tr>'."\n";
    return $html;
  }
}
