<?php
require_once '../app/Mage.php';
require_once 'functions.php';
checkIsLoggedIn();

$entityBody = file_get_contents('php://input');
$data = json_decode($entityBody, true);
$productId = $data['product_id'];
$rating[2] = $data['ratings'];
$nickname = $data['nickname'];
$title = $data['title'];
$detail = $data['detail'];
$customerId = $data['customer_id'] ? $data['customer_id'] : null;
if ($data['product_id']) {
	try {
		$review = Mage::getModel('review/review')->setData(array(
			"rating" => $rating,
			"nickname" => $nickname,
			"title" => $title,
			"detail" => $detail
		));
		$review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
			->setEntityPkValue($productId)
			->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
			->setCustomerId($customerId)
			->setStoreId(getStoreId())
			->setStores(array(getStoreId()))
			->save();

		foreach ($rating as $ratingId => $optionId) {
			Mage::getModel('rating/rating')
				->setRatingId($ratingId)
				->setReviewId($review->getId())
				->setCustomerId($customerId)
				->addOptionVote($optionId, $productId);
		}

		$review->aggregate();
		$reviewData = array();
		$reviewData['review_nickname'] = $review->getNickname();
		$reviewData['review_title'] = $review->getTitle();
		$reviewData['review_detail'] = $review->getDetail();
		$reviewData['review_created_at'] = $review->getCreatedAt();
		$vote = Mage::getModel('rating/rating_option_vote')
			->getResourceCollection()
			->setReviewFilter($review->getReviewId())
			->setStoreFilter(getStoreId())
			->getFirstItem();

		$reviewData['review_vote'] = $vote->getPercent();
		http_response_code(200);
		echo json_encode(array('status_code' => 200, 'message' => 'successfully', 'reviewData' => $reviewData));
	} catch (Exception $e) {
		http_response_code(400);
		echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
	}
} else {
	http_response_code(400);
	echo json_encode(array('status_code' => 400, 'message' => 'Invalid'));
}
