<?php
require_once(Mage::getModuleDir('controllers','Mage_Review').DS.'ProductController.php');
class Amasty_SeoReviews_ProductController extends Mage_Review_ProductController
{
  public function postAction()
  {
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $_response = array();
    if (!$this->_validateFormKey()) {
      // returns to the product item page
//      $this->_redirectReferer();
      $_response['success'] = 'error';
      $message = $this->__('Unable to post the review.');
      $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>'.$message.'</span></li></ul></li></ul>';
      $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
      return;
    }

    if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
      $rating = array();
      if (isset($data['ratings']) && is_array($data['ratings'])) {
        $rating = $data['ratings'];
      }
    } else {
      $data   = $this->getRequest()->getPost();
      $rating = $this->getRequest()->getParam('ratings', array());
    }

    if (($product = $this->_initProduct()) && !empty($data)) {
      $session = Mage::getSingleton('core/session');
      /* @var $session Mage_Core_Model_Session */
      $review = Mage::getModel('review/review')->setData($this->_cropReviewData($data));
      /* @var $review Mage_Review_Model_Review */

      $validate = $review->validate();
      if ($validate === true) {
        try {
          $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($product->getId())
            ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setStores(array(Mage::app()->getStore()->getId()))
            ->save();

          foreach ($rating as $ratingId => $optionId) {
            Mage::getModel('rating/rating')
              ->setRatingId($ratingId)
              ->setReviewId($review->getId())
              ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
              ->addOptionVote($optionId, $product->getId());
          }

          $review->aggregate();
          $_response['success'] = 'success';
          $message = $this->__('Your review has been accepted for moderation.');
          $_response['message'] = '<ul class="messages"><li class="success-msg"><ul><li><span>'.$message.'</span></li></ul></li></ul>';

        }
        catch (Exception $e) {
          $session->setFormData($data);
//          $session->addError($this->__('Unable to post the review.'));
          $_response['success'] = 'error';
          $message = $this->__('Unable to post the review.');
          $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>'.$message.'</span></li></ul></li></ul>';
        }
      }
      else {
        $session->setFormData($data);
        if (is_array($validate)) {
          $message = '';
          foreach ($validate as $errorMessage) {
//            $session->addError($errorMessage);
            $message .= $errorMessage.'<br>';
          }
          $_response['success'] = 'error';
          $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>'.$message.'</span></li></ul></li></ul>';
        }
        else {
//          $session->addError($this->__('Unable to post the review.'));
          $_response['success'] = 'error';
          $message = $this->__('Unable to post the review.');
          $_response['message'] = '<ul class="messages"><li class="error-msg"><ul><li><span>'.$message.'</span></li></ul></li></ul>';
        }
      }
    }
    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($_response));
    return;
//    if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
//      $this->_redirectUrl($redirectUrl);
//      return;
//    }
//    $this->_redirectReferer();
  }

}
