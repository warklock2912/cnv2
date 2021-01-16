<?php
header('Content-Type: application/json');
require_auth();
Mage::app();
define('STATUS_SESSION_EXPIRED', 406);
define('STATUS_TOKEN_EXPIRED', 405);
Mage::app()->setCurrentStore(getStoreId());
/**
 * @return mixed
 */

function checkIsLoggedIn()
{
    try {
        $pidsess = Mage::getSingleton('core/session', array('name' => 'frontend'));
        $custsess = Mage::getSingleton('customer/session');
        $isLoggedIn = false;

        if ($custsess->isLoggedIn() == true) {
            $isLoggedIn = true;
        } else {
            sessionExpiredResult();
            die();
        }

        $customer = getCustomer();
        if (Mage::getModel('core/cookie')->get('frontend') !== $customer->getData('m_token')) {
            http_response_code(constant('STATUS_TOKEN_EXPIRED'));
            echo json_encode(array(
                'status_code' => constant('STATUS_TOKEN_EXPIRED'),
                'message' => 'You\'re logged in other device.'
            ));
            die();
        }
    } catch (Exception $e) {
        sessionExpiredResult();
        die();
    }

    unset($piddata);
    unset($custsess);

    return $isLoggedIn;
}

function sessionExpiredResult()
{
    http_response_code(constant('STATUS_SESSION_EXPIRED'));
    echo json_encode(array(
        'status_code' => constant('STATUS_SESSION_EXPIRED'),
        'message' => 'Session is expired , please loggin again!'
    ));
}

function getCustomer()
{
    try {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
    } catch (Exception $e) {
        sessionExpiredResult();
        die();
    }
    return $customer;
}

function display_children($categories, $level)
{
    $result = array();
    $tree = array();
    if (count($categories) > 0) {
        foreach ($categories as $_category):
            $result['category_id'] = $_category->getId();
            $result['parent_id'] = (string )$_category->getParentId();
            $result['name'] = $_category->getName();
            $result['is_active'] = $_category->getIsActive();
            $result['is_include_navigation_menu'] = $_category->getIncludeInMenu() == 1 ? true : false;
            $_categoryAddition = Mage::getModel('catalog/category')->load($_category->getId());
            $result['images'] = (string)$_categoryAddition->getImageUrl();
            $result['reference_category'] = $_category->getData('reference_category');
            $tree[] = $result;
        endforeach;

    }
    return $tree;
}

function require_auth()
{
    $AUTH_USER = 'crystal-test';
    $AUTH_PASS = '6e9b3066c3e6b80b766952c4ddfedf5c';
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
    $is_not_authenticated = (
        !$has_supplied_credentials ||
        $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
        $_SERVER['PHP_AUTH_PW'] != $AUTH_PASS
    );
    if ($is_not_authenticated) {
        header('HTTP/1.1 401 Authorization Required');
        header('WWW-Authenticate: Basic realm="Access denied"');
        exit;
    }
}

function getProductsForFeature($category_id)
{

    $category = Mage::getModel('catalog/category')->load($category_id);

    /**
     * Getting product collection for a particular category
     */
    $prodCollection = Mage::getResourceModel('catalog/product_collection')
        ->addCategoryFilter($category)
        ->addFinalPrice()
        ->addAttributeToSelect('*')
        ->setOrder('entity_id', 'DESC')
        ->setPageSize(20);

    /**
     * Applying status and visibility filter to the product collection
     * i.e. only fetching visible and enabled products
     */
    Mage::getSingleton('catalog/product_status')
        ->addVisibleFilterToCollection($prodCollection);

    Mage::getSingleton('catalog/product_visibility')
        ->addVisibleInCatalogFilterToCollection($prodCollection);

    $dataPro = array();
    $dataProArr = array();
    $label_collection = Mage::getModel('amlabel/label')->getCollection()
        ->addFieldToFilter('include_type', array('neq' => 1));
    try {
        foreach ($prodCollection as $product) {
            //
            $labels = array();
            if (0 < $label_collection->getSize()) {
                foreach ($label_collection as $label) {
                    $name = 'amlabel_' . $label->getId();
                    if ($product->hasData($name)) {
                        $labels[] = $label->getId();
                    } elseif ($product->getData('sku')) {
                        $skus = explode(',', $label->getIncludeSku());
                        if (in_array($product->getData('sku'), $skus)) {
                            $labels[] = $label->getId();
                        }
                    }
                }
            }
            $dataPro['label'] = $labels;
            //
            $dataPro['id'] = $product->getId();
            $dataPro['product_id'] = $product->getId();

            $dataPro['name'] = $product->getName();
            $dataPro['category'] = $category_id;
            $imagesData = array();
            $dataMediaArr = array();
            if (count($product->getMediaGalleryImages())) {
                foreach ($product->getMediaGalleryImages() as $image) { //will load all gallery images in loop
                    //print_r($image);
                    $imagesData['title'] = $image->getTitle();
                    $imagesData['position'] = $image->getPosition();
                    $imagesData['url'] = $image->getUrl();
                    $dataMediaArr[] = $imagesData;
                }
            }
            if ($product->isSalable() == 1)
                $dataPro["in_stock"] = true;
            else
                $dataPro["in_stock"] = false;

            $dataPro['buy_with_point'] = false;
            if ($product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($product)) {
                $dataPro['buy_with_point'] = true;
                $dataPro['points_spend'] = (int)$product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($product);
            }
            $dataPro["image"] = $product->getImageUrl();
            $dataPro["images"] = $dataMediaArr;
            $dataPro['brand'] = $product->getResource()->getAttribute('carnival_brand')->getFrontend()->getValue($product);
            $dataPro["price"] = $product->getPrice();
            $dataPro["special_price"] = $product->getFinalPrice() . "";

            $dataProArr[] = $dataPro;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => $e->getMessage()));
    }
    return $dataProArr;
}

function getQuote()
{
    $customer = getCustomer();
    $quote = Mage::getModel('sales/quote')->setSharedStoreIds(getStoreId())->loadByCustomer($customer);
    if (!$quote->getId()) {
        $quoteObj = Mage::getModel('sales/quote');
        $quoteObj->assignCustomer($customer);
        $quoteObj->setStoreId(getStoreId());
        $quoteObj->collectTotals();
        $quoteObj->setIsActive(true);
        $quoteObj->save();
    }
    $session = Mage::getSingleton('checkout/session');
    return $quote = $session->getQuote();
}

function getRewardPointData($quote, $totalPointsSpend)
{
    if ($quote->getItemsQty()) {
        $_blockRewardpointsAccount = Mage::app()->getLayout()->getBlockSingleton('rewardpoints/account_dashboard');
        $pointsAvailable = $_blockRewardpointsAccount->getBalanceTextPoints();
        $result['balance_points'] = ['label' => __('You have ' . $pointsAvailable . ' Available'), 'value' => $pointsAvailable];
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        $cart = Mage::getSingleton('checkout/cart');
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
        }
        $helperSpending = Mage::helper('rewardpoints/calculation_spending');

        $result['spending_points'] = [
            'label' => __('Points Spending'),
            'value' => $totalPointsSpend > 0 ? '-' . $totalPointsSpend : '' . $totalPointsSpend
        ];

        if ($address->getRewardpointsDiscount()) {
            $result['use_points'] = ['label' => __('Use Point (' . $address->getRewardpointsDiscount() . ') Points'), 'value' => '-' . $address->getRewardpointsDiscount()];
        }
        return $result;
    }
}

function getCartDetails($quote, $customerId)
{
    try {
        // Reset shipping method
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        if ($shippingMethod) {
            $quote->getShippingAddress()->setShippingMethod(null);  //setting method to null
        }
        if (!count($quote->getAllVisibleItems())) {
            $quote->setData('coupon_code', '')
                ->collectTotals()
                ->save();
        }
        $quote->collectTotals();
        $quote->save();
        //End Reset shipping method

        $productsResult = array();
        $wishList = Mage::getModel('wishlist/wishlist')->loadByCustomer($customerId);
        $wishListItemCollection = $wishList->getItemCollection();
        $totalPointsSpend = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $product_id = $product->getId();
//        $product = Mage::getSingleton('catalog/product')->load($product_id);
            $isWishList = false;
            foreach ($wishListItemCollection as $wishListItem) {
                if ($wishListItem->getProductId() == $product_id)
                    $isWishList = true;
            }
            $buyWithPoint = false;
            $pointsSpend = 0;
            $_product = Mage::getModel('catalog/product')->load($product_id);
            if ($_product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($_product)) {
                $buyWithPoint = true;
                $pointsSpend = (int)$_product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($_product);
            }
            $totalPointsSpend += $pointsSpend * $item->getQty();
            $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $productsResult['items'][] = array(// Basic product data
                'item_id' => $item->getId(),
                'product_id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'set' => $product->getAttributeSetId(),
                'type' => $product->getTypeId(),
                'price' => (string)$product->getPrice(),
                'special_price' => $product->getSpecialPrice() ? (string)$product->getSpecialPrice() : $product->getSpecialPrice(),
                'image' => Mage::helper('catalog/image')->init($product, 'small_image') . '',
                'qty' => $item->getQty(),
                /*'cart_expired_time' => Mage::helper('cartreservation')->getItemTime($item),*/
                'options' => array(
                    'value_index' => $productOptions['info_buyRequest']['super_attribute']['255'],
                    'label' => $productOptions['attributes_info'][0]
                ),
                'buy_with_point' => $buyWithPoint,
                'points' => $pointsSpend,
                'is_wishlist' => $isWishList
            );
            $productsResult['quote_id'] = $quote->getId();
            $productsResult['sub_total'] = (string)$quote->getSubtotal();
            $productsResult['grand_total'] = (string)$quote->getGrandTotal();
            $productsResult['reward_point'] = getRewardPointData($quote, $totalPointsSpend);
            $productsResult['minimum_point_spent'] = Mage::getStoreConfig('rewardpoints/spending/redeemable_points');

            if (Mage::helper('cartreservation')->moduleEnabled() === true) {
                $productsResult['cart_expired_time'] = Mage::helper('cartreservation/customer')->leftReservationTime();
            }
            $discount = $quote->getTotals();
            if (array_key_exists('discount', $discount)) {
                $productsResult['discount']['amount'] = (string)$discount['discount']->getValue();
                $productsResult['discount']['code'] = $quote->getCouponCode();
            }

            $point = Mage::helper('rewardpoints/calculation_earning')->getRateEarningPoints($quote->getGrandTotal());
            $productsResult['point'] = $point;
        }
        if (count($productsResult) > 0)
            return $productsResult;
        else return (object)$productsResult;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => $e));
        die;
    }
}

function dataResponse($statusCode, $message, $data = '', $label = 'data', $total = null)
{
    http_response_code($statusCode);

    $dataResponse = array(
        'status_code' => $statusCode,
        'message' => $message,
        $label => $data
    );
    if ($total != null) {
        $dataResponse['total'] = $total;
    }
    echo json_encode($dataResponse);
}

function getStoreId()
{

    if (!function_exists('getallheaders')) {
        function getallheaders()
        {
            $headers = array();
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }
    }

    $headers = getallheaders();

    $store_code = $headers['Store'];
    switch ($store_code) {
        case 'en':
            $store_id = Mage::getModel('core/store')->load('en', 'code')->getId();
            break;
        case 'th':
            $store_id = Mage::getModel('core/store')->load('th', 'code')->getId();
            break;
        case 'app_en':
            $store_id = Mage::getModel('core/store')->load('app_en', 'code')->getId();
            break;
        case 'app_th':
            $store_id = Mage::getModel('core/store')->load('app_th', 'code')->getId();
            break;
        default:
            $store_id = Mage::app()->getStore()->getId();
            break;
    }
    return $store_id;
}

function getLayer()
{
    $searchParameter = Mage::helper('mageworx_searchsuite')->getSearchParameter();
    $categoryParameter = Mage::helper('mageworx_searchsuite')->getSearchCategory();
    $layer = null;
    if (!$searchParameter && !$categoryParameter) {
        $layer = Mage::getSingleton('catalogsearch/layer');

        if (Mage::getConfig()->getModuleConfig('Enterprise_Search')->is('active', true)) {
            $helper = Mage::helper('enterprise_search');
            if ($helper->isThirdPartSearchEngine() && $helper->isActiveEngine()) {
                $layer = Mage::getSingleton('enterprise_search/search_layer');
            }
        }
    } else {
        $layer = Mage::getModel('mageworx_searchsuite/layer');
    }

    return $layer;
}

function addFacetCondition($layer)
{
    if (!Mage::getConfig()->getModuleConfig('Enterprise_Search')->is('active', true)) {
        return $this;
    }
    $category = $layer->getCurrentCategory();
    $childrenCategories = $category->getChildrenCategories();

    $useFlat = (bool)Mage::getStoreConfig('catalog/frontend/flat_catalog_category');
    $categories = ($useFlat) ? array_keys($childrenCategories) : array_keys($childrenCategories->toArray());

    $layer->getProductCollection()->setFacetCondition('category_ids', $categories);

    return $this;
}

function getLocator($campaignId)
{
    $locatorId = Mage::getModel('campaignmanage/campaign')->load($campaignId)->getDealerlocatorId();
    $locator = Mage::getModel('dealerlocator/dealerlocator')->load($locatorId);
    return $locator;
}


function checkJoinedQueue($campaignId, $customerId)
{
    $model = Mage::getModel('campaignmanage/queue')->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->addFieldToFilter('customer_id', $customerId)
        ->getFirstItem();

    if ($model->getId()) {
        return true;
    } else
        return false;
}

function checkJoinedRaffle($campaignId, $customerId)
{
    $model = Mage::getModel('campaignmanage/raffle')->getCollection()
        ->addFieldToFilter('campaign_id', $campaignId)
        ->addFieldToFilter('customer_id', $customerId)
        ->getFirstItem();
    if ($model->getId()) {
        return true;
    } else
        return false;
}

function checkJoinedRaffleOnline($campaignId, $customerId)
{
    $model = Mage::getModel('campaignmanage/raffleonline')->getCollection()
        ->addFieldToFilter('raffle_id', $campaignId)
        ->addFieldToFilter('customer_id', $customerId)
        ->getFirstItem();
    if ($model->getId()) {
        return true;
    } else
        return false;
}

function getCustomerData($customer)
{
    $rewardPoint = Mage::getResourceModel('rewardpoints/customer_collection')->addFieldToFilter('customer_id', $customer->getId())->getFirstItem();
    $data['user_id'] = $customer->getId();
    $data['first_name'] = $customer->getFirstname();
    $data['last_name'] = $customer->getLastname();
    $data['email'] = $customer->getEmail();
    $data['gender'] = $customer->getGender();
    $data['telephone'] = $customer->getTelephone();
    $data['point'] = $rewardPoint->getPointBalance();
    $data['birth_day'] = $customer->getDob() ? date("Y-m-d", strtotime($customer->getDob())) : null;
    $data['vip_member'] = $customer->getVipMemberId();
    $data['is_vip_member'] = $customer->getGroupId() == 4 ? true : false;
    $data['profile_photo'] = $customer->getProfilePhoto() ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $customer->getProfilePhoto() : null;
    $customerCard = Mage::getModel('activity/activity')->getCollection()->addFieldToFilter('customer_id', $customer->getId())->getFirstItem();
    $data['cart_id'] = $customerCard->getCardId() ? $customerCard->getCardId() : null;
    return $data;
}

function convertValueToString($val)
{
    if ($val != null)
        return (string)$val;
    else
        return $val;
}

function spendPointsActivity($customerId, $points_cost, $campaign_name)
{
    $customer = $customerId ?
        Mage::getModel('customer/customer')->load($customerId) : Mage::getSingleton('customer/session')->getCustomer();

    //$campaign = Mage::getModel('campaignmanage/campaignonline')->load($campaignId);
    //$points_cost = $campaign->getData('point_spent');

    $rewardAccount = Mage::getModel('rewardpoints/customer')
        ->load($customerId, 'customer_id');

    $point_balance = $rewardAccount->getData('point_balance');

    if ($point_balance < $points_cost) {
        return 'Your points balance is not enough to spend for this activity';
    } else {
        $rewardAccount->setCustomerId($customer->getId());
        try {
            Mage::helper('rewardpoints/action')->addTransaction('admin', $customer, new Varien_Object(array(
                    'point_amount' => -$points_cost,
                    'title' => 'Spend for activity ' . $campaign_name
                ))
            );
            return 1;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

function refundPointActivity($customer_id, $points_cost, $campaign_name)
{
    $customer = $customer_id ? Mage::getModel('customer/customer')->load($customer_id) : Mage::getSingleton('customer/session')->getCustomer();
    try {
        Mage::helper('rewardpoints/action')->addTransaction('admin', $customer, new Varien_Object(array(
                'point_amount' => $points_cost,
                'title' => 'Refund for activity ' . $campaign_name
            ))
        );
        return 1;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function updateNoQueue($campaignId, $customerId)
{
    $campaign = Mage::getModel('campaignmanage/campaign')->load($campaignId);

    if ($campaign->getCampaignType() != Crystal_Campaignmanage_Model_Campaigntype::TYPE_STORE_SHUFFLE) {
        $queueCurrentList = Mage::getModel('campaignmanage/queue')->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId)
            ->setOrder('created_at', 'ASC');
    } else {
        $queueCurrentList = Mage::getModel('campaignmanage/queue')->getCollection()
            ->addFieldToFilter('campaign_id', $campaignId)
            ->setOrder('no_of_queue', 'ASC');
        refundPointActivity($customerId, $campaign->getData('points_cost'), $campaign->getCampaignName());
    }
    $i = 1;
    foreach ($queueCurrentList as $queue) {
        $queue->setData('no_of_queue', $i);
        $queue->save();
        $i++;
    }
}

function check_product_sale_qty($stock, $qty)
{
    if ($stock->getData('use_config_min_sale_qty')) {
        $min_qty = 1;
    } else {
        $min_qty = (int)$stock->getData('min_sale_qty');
    }

    if ($stock->getData('use_config_max_sale_qty')) {
        $max_qty = (int)Mage::getStoreConfig('cataloginventory/item_options/max_sale_qty', Mage::app()->getStore());
    } else {
        $max_qty = (int)$stock->getData('max_sale_qty');
    }

    if ($qty < $min_qty) {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => 'The minimum quantity allowed for purchase is ' . $min_qty));
        die;
    }

    if ($qty > $max_qty) {
        http_response_code(400);
        echo json_encode(array('status_code' => 400, 'message' => 'The maximum quantity allowed for purchase is ' . $max_qty));
        die;
    }
}

function loginAction($customer, $profilePhoto, $profilePhotoPath, $profilePhotoName, $deviceId)
{
    if ($customer->getProfilePhoto()) {
        $image = $customer->getProfilePhoto();
        unlink(Mage::getBaseDir() . DS . $image);
    }
    if ($profilePhoto != null) {
        file_put_contents($profilePhotoPath . $profilePhotoName, base64_decode($profilePhoto));
        $customer->setProfilePhoto("media/profile/" . $profilePhotoName);
    }

    $customer->save();
    // get customer info to Response
    $userInfo = getCustomerData($customer);

    $quote = Mage::getModel('sales/quote')->setSharedStoreIds(Mage::app()->getStore()->getId())->loadByCustomer($customer);
    if (!$quote->getId()) {
        $quoteObj = Mage::getModel('sales/quote');
        $quoteObj->assignCustomer($customer);
        $quoteObj->setStoreId(getStoreId());
        $quoteObj->collectTotals();
        $quoteObj->setIsActive(true);
        $quoteObj->save();
    }

    //add notification device
    if ($deviceId != null) {
        $notificationDevice = Mage::getModel('pushnotification/device')
            ->getCollection()
            ->addFieldToFilter('device_id', $deviceId)->getFirstItem();
        $notificationDevice->setData('user_id', $customer->getId())->save();
    }

    return $userInfo;
}

function searchFilterList($layer)
{
    /** Get Filter Listing **/
    $category = Mage::getModel('catalog/category')->load(2);
    $layer->setCurrentCategory($category);
    $attributes = $layer->getFilterableAttributes();
    foreach ($attributes as $attribute) {
        if (isset($_REQUEST[$attribute->getAttributeCode()])) {
            unset($_REQUEST[$attribute->getAttributeCode()]);
            unset($_GET[$attribute->getAttributeCode()]);
        }
        $filter_attr = array();
        $filter_attr['title'] = $attribute->getFrontendLabel();
        $filter_attr['code'] = $attribute->getAttributeCode();

        if ($attribute->getAttributeCode() == 'price') {
            $filterBlockName = 'catalog/layer_filter_price';
        } elseif ($attribute->getBackendType() == 'decimal') {
            $filterBlockName = 'catalog/layer_filter_decimal';
        } else {
            $filterBlockName = 'catalog/layer_filter_attribute';
        }

        $result = Mage::app()->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
        $i = 0;

        foreach ($result->getItems() as $option) {
            $attr_option = array();
            if ($attribute->getAttributeCode() == 'price') {
                $attr_option['label'] = str_replace(array('<span class=\'p_bath\' style=\'margin-left :2px\' >', '</span>', '<span class="price">'), '', $option->getLabel());
            } else {
                $attr_option['label'] = $option->getLabel();
            }

            $attr_option['value'] = $option->getValue();
            $attr_option['count'] = $option->getCount();
            $i++;
            $filter_attr['options'][] = $attr_option;
        }

        if ($i != 0) {
            $filter_attributes[] = $filter_attr;
        }
    }

    $resultFilter = array();

    // add order listing to filter data
    // add order listing to filter data
    $orderOptionsAvailable = $category->getAvailableSortByOptions();
    $orderOptions = array();
    foreach ($orderOptionsAvailable as $key => $value) {
        if ($key !== 'position') {
            $orderArr['label'] = $value;
            $orderArr['value'] = $key;
        } else {
            $orderArr = array(
                'label' => 'Relevance',
                'value' => 'relevance'
            );
        }
        $orderOptions[] = $orderArr;

    }
    $resultFilter['OderBy'] = ['code' => 'order', 'options' => $orderOptions];
    $resultFilter['filters'] = $filter_attributes;

    // get list category
    $key = $layer->getStateKey() . '_SUBCATEGORIES';
    $dataFilter = $layer->getAggregator()->getCacheData($key);

    if ($dataFilter === null) {
        /** @var $categoty Mage_Catalog_Model_Categeory */
        $categories = $category->getChildrenCategories();

        $layer->getProductCollection()
            ->addCountToCategories($categories);

        $dataFilter = array();
        foreach ($categories as $category) {
            if ($category->getIsActive() && $category->getProductCount()) {
                $dataFilter[] = array(
                    'label' => Mage::helper('core')->escapeHtml($category->getName()),
                    'value' => $category->getId(),
                    'count' => $category->getProductCount(),
                );
            }
        }

        $tags = $layer->getStateTags();
        $layer->getAggregator()->saveCacheData($dataFilter, $key, $tags);
    }
    $resultFilter['category'] = $dataFilter;

    return $resultFilter;
}

function addToken($customer)
{
    $SID = Mage::getSingleton('core/session')->getEncryptedSessionId(); //current session id

    $resource = Mage::getSingleton('core/resource');
    $write = $resource->getConnection('core_write');
    $table = $resource->getTableName('customer_entity');
    $write->update(
        $table,
        ['m_token' => $SID],
        ['entity_id = ?' => $customer->getId()]
    );
}

function checkCardExist($cardToken)
{
    $card = Mage::getModel('p2c2p/token')
        ->getCollection()
        ->addFieldToFilter('stored_card_unique_id', $cardToken);
    if (count($card))
        return true;
    return false;
}


function convertCsvToArray($string = '', $delimiter = ',', $addHeader = true)
{
    $enclosure = '"';
    $escape = "\\";

    $rows = array_filter(preg_split('/\r*\n+|\r+/', $string));

    $data = array();
    if ($addHeader) {
        $header = array_shift($rows);
        $header = str_getcsv($header, $delimiter, $enclosure, $escape);

        foreach ($rows as $row) {
            $row = str_getcsv($row, $delimiter, $enclosure, $escape);
            $data[] = array_combine($header, $row);
        }
    } else {
        foreach ($rows as $row) {
            $data[] = str_getcsv($row, $delimiter, $enclosure, $escape);
        }
    }

    return $data;
}


function limitMinMaxQty($quote,$qty, $product = null, $itemId = null)
{
    $result = array(
        'success' => true,
    );

    if (Mage::helper('minmaxqtyorderpercate')->getConfig('enable')) {

        $cartItems = $quote->getAllVisibleItems();

        $cates_qty = array();
        $cates_name = array();
        foreach ($cartItems as $item) {

            $cates = $item->getProduct()->getCategoryIds();
            $cates_name[$item->getName()] = $cates;

            $itemQty = $item->getQty();
            if ($item->getItemId() == $itemId){
                $itemQty = $qty;
            }

            foreach ($cates as $cate) {
                if (is_array($cates_qty) and !empty($cates_qty[$cate])) {
                    $cates_qty[$cate] += $itemQty;
                } else {
                    $cates_qty[$cate] = $itemQty;
                }
            }
        }


        if ($product != null){
            $cates = $product->getCategoryIds();
            $cates_name[$product->getName()] = $cates;

            foreach ($cates as $cate) {
                if (is_array($cates_qty) and !empty($cates_qty[$cate])) {
                    $cates_qty[$cate] += $qty;
                } else {
                    $cates_qty[$cate] = $qty;
                }
            }
        }

        $customer = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $_qty = Mage::helper('minmaxqtyorderpercate')->OrderQty($cates_qty, $customer);
        if (!empty($_qty)) {
            if (!empty($_qty['min_qty'])) {
                foreach ($_qty['min_qty'] as $catid => $qtylimit) {
                    $product_names = array();
                    foreach ($cates_name as $prt_name => $catids) {
                        if (in_array($catid, $catids)) {
                            $product_names[] = $prt_name;
                        }
                    }
                    $product_name = implode(',', $product_names);
                    $cate_name = Mage::getModel('catalog/category')->load($catid)->getName();
                    $message = str_replace("{{category_name}}", $cate_name, Mage::helper('minmaxqtyorderpercate')->getConfig('mess_err_min'));
                    $message = str_replace("{{qty_limit}}", $qtylimit, $message);
                    $message = str_replace("{{product_name}}", $product_name, $message);
                    // $message = "The min quantity allowed for purchase at category ".$namecate." is ".$k;
                }

            }
            if (!empty($_qty['max_qty'])) {
                foreach ($_qty['max_qty'] as $catid => $qtylimit) {
                    $product_names = array();
                    foreach ($cates_name as $prt_name => $catids) {
                        if (in_array($catid, $catids)) {
                            $product_names[] = $prt_name;
                        }
                    }
                    $product_name = implode(',', $product_names);
                    $cate_name = Mage::getModel('catalog/category')->load($catid)->getName();
                    $message = str_replace("{{category_name}}", $cate_name, Mage::helper('minmaxqtyorderpercate')->getConfig('mess_err_max'));
                    $message = str_replace("{{qty_limit}}", $qtylimit, $message);
                    $message = str_replace("{{product_name}}", $product_name, $message);
                }

            }

            $result['success'] = false;
            $result['message'] = $message;
        }
    }
    return $result;
}

//Kpayment Credit
function getPaymentResult($params, $private = null) {
    $kpaymentHelper = Mage::helper('kpayment');
    $kpaymentHelper->logAPI('[Mobile request Kpayment KpaymentCode]', 'credit');

    $options['headers'] = array(
        'Content-Type: ' . 'application/json; charset=UTF-8',
    );
    $url = Mage::getStoreConfig('kpayment_options/credit/return_url');;
    $post_data = http_build_query($params);
    $url = $url . '?' . $post_data;
    $kpaymentHelper->logAPI('[Mobile request Kpayment Log url]', 'credit');
    $kpaymentHelper->logAPI($url, 'credit');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $kpaymentHelper->logAPI($response, 'credit');
    $kpaymentHelper->logAPI('[Mobile $response Kpayment KpaymentCode]', 'credit');
    $kpaymentHelper->logAPI($response, 'credit');

    return $response;
}

function saveReferencePayment($payment, $order, $charge, $token)
{
    if (!isset($charge['id'])) {
        die;
    }
    $payment->setAdditionalInformation('charge_id', $charge['id']);
    $payment->setAdditionalInformation('transaction_state', $charge['transaction_state']);
    $payment->setAdditionalInformation('status', $charge['status']);
    $payment->setAdditionalInformation('source', json_encode($charge['source'], true));
    $payment->setAdditionalInformation('authen_url', $charge['redirect_url']);
    $payment->save();

    /** @var Tigren_Kpayment_Model_Credit_Reference $reference **/
    $reference = Mage::getModel('kpayment/credit_reference');
    $reference->setData('token_id', $token);
    $reference->setData('method', 'Kpaymentredirect');
    $reference->setData('order_increment', $order->getIncrementId());
    $reference->setData('charge_id', $charge['id']);
    $reference->setData('object', $charge['object']);
    $reference->setData('amount', $charge['amount']);
    $reference->setData('currency', $charge['currency']);
    $reference->setData('transaction_state', $charge['transaction_state']);
    $reference->setData('source_id', $charge['source']['id']);
    $reference->setData('source_object', $charge['source']['object']);
    $reference->setData('source_brand', $charge['source']['brand']);
    $reference->setData('source_card_masking', $charge['source']['card_masking']);
    $reference->setData('created', $charge['created']);
    $reference->setData('status', $charge['status']);
    $reference->setData('livemode', $charge['livemode'] ? 1 : 0);
    $reference->setData('failure_code', $charge['failure_code']);
    $reference->setData('failure_message', $charge['failure_message']);
    $reference->setData('authen_url', $charge['redirect_url']);
    $reference->setData('settlement_info', $charge['settlement_info']);
    $reference->setData('refund_info', $charge['refund_info']);
    $reference->setData('response_at', date('Y-m-d H:i:s'));
    $reference->save();
}

function updateInquiryStatus($chargeId, $orderIncrement, $inquiryStatus = 'default')
{
    try{
        /** @var Tigren_Kpayment_Helper_Data $kHelper **/
        $kHelper = Mage::helper('kpayment');

        $write = $kHelper->writeAdapter();

        $query = "UPDATE kpayment_credit_reference SET inquiry_status = :inquiryStatus, inquiry_date = NOW() WHERE charge_id = :chargeId AND order_increment = :orderIncrement;";
        $write->query($query, array(
            'chargeId' => $chargeId,
            'orderIncrement' => $orderIncrement,
            'inquiryStatus' => $inquiryStatus
        ));
    }
    catch(Exception $e){
        return array(
            'code' => '500',
            'message' => $e->getMessage()
        );
    }
    return array(
        'code' => '200',
        'message' => 'success'
    );
}
