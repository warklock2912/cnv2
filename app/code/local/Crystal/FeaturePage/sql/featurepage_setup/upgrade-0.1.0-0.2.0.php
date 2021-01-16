<?php
$this->startSetup();
$installer = $this;
$installer->removeAttribute('catalog_category', 'parent_brand');
$this->endSetup();