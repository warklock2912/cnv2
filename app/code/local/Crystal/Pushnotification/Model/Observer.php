<?php

class Crystal_Pushnotification_Model_Observer
{
	public function sendNotification($event)
	{
		$posts = Mage::getModel('mpblog/post')->getCollection();
		$now = new Zend_Date();
		$oldTime = date('Y-m-d H:i:s', strtotime('-60 minutes'));
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $table = $resource->getTableName('mpblog/post');
		$posts
			->addFieldToFilter('published_at', array('lt' => $now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)))
			->addFieldToFilter('published_at', array('gt' => $oldTime))
			->addFieldToFilter('notify_on_enable', 1)
			->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED)
			->addFieldToFilter('is_sent', array('neq' => 1));
		foreach ($posts as $post):;
			$post = Mage::getModel('mpblog/post')->load($post->getId());
            $write->update(
                $table,
                ['is_sent' => 1],
                ['post_id = ?' => $post->getId()]
            );
			$customerIdsArr = array();
			$categories = $post->getCategories();
			foreach ($categories as $categoryId):;
				$newsNotificationList = Mage::getModel('newsnotification/newsnotification')->getCollection()->addFieldToFilter('category_id', $categoryId);
				foreach ($newsNotificationList as $item) {
					if (!in_array($item['customer_id'], $customerIdsArr)) {
						$customerIdsArr[] = $item['customer_id'];
					}
				}
			endforeach;
            $data = array(
                "type" => '1',
                'content_id' => '' . $post->getId(),
                'id' => '' . $post->getId(),
            );
            Mage::helper('pushnotification')->sendAction($customerIdsArr, $post->getTitle(), $post->getShortContent(),null,$data );
		endforeach;
	}

}
