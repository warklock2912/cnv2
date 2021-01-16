<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
	->addConstraint(
		'FK_PRODUCT_RELATION_CAMPAIGN',
		$installer->getTable('campaignmanage/products'),
		'campaign_id',
		$installer->getTable('campaign'),
		'campaign_id',
		'cascade',
		'cascade'
	);
$installer->endSetup();