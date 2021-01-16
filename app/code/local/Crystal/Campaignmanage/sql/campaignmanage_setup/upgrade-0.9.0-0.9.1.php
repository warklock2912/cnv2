<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
	->addConstraint(
		'FK_ITEMS_RELATION_ITEM',
		$installer->getTable('campaignmanage/queue'),
		'campaign_id',
		$installer->getTable('campaign'),
		'campaign_id',
		'cascade',
		'cascade'
	);
$installer->endSetup();