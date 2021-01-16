<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Dealerlocator_Block_Html_Head extends Amasty_Meta_Block_Page_Html_Head {
  protected function _construct() {
    $this->setTemplate('page/html/head.phtml');
  }

  public function addExternalItem($type, $name, $params = null, $if = null, $cond = null) {
    parent::addItem($type, $name, $params = null, $if = null, $cond = null);
  }

  public function removeExternalItem($type, $name) {
    parent::removeItem($type, $name);
  }

  protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe) {
    $params = $itemParams ? ' ' . $itemParams : '';
    $href = $itemName;
    switch ($itemType) {
      case 'rss':
        $lines[$itemIf]['other'][] = sprintf('<link href="%s"%s rel="alternate" type="application/rss+xml" />', $href, $params);
        break;
      case 'link_rel':
        $lines[$itemIf]['other'][] = sprintf('<link%s href="%s" />', $params, $href);
        break;

      case 'external_js':
        $lines[$itemIf]['other'][] = sprintf('<script type="text/javascript" src="%s" %s></script>', $href, $params);
        break;

      case 'external_css':
        $lines[$itemIf]['other'][] = sprintf('<link rel="stylesheet" type="text/css" href="%s" %s/>', $href, $params);
        break;
    }
  }

}