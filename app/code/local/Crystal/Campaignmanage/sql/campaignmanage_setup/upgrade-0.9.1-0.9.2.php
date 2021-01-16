<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()
	->addConstraint(
		'FK_RAFFLE_RELATION_CAMPAIGN',
		$installer->getTable('campaignmanage/raffle'),
		'campaign_id',
		$installer->getTable('campaign'),
		'campaign_id',
		'cascade',
		'cascade'
	);
$installer->endSetup();