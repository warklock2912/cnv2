<?php
require_once '../../app/Mage.php';
require_once '../functions.php';
try {
    Mage::getSingleton("core/session", array("name" => "frontend"));
} catch(Exception $e){
    sessionExpiredResult();
    die();
}

$queryText = $_REQUEST['q'];
$currentPage = $_REQUEST['p'] ?: 1;
$setPageSize = $_REQUEST['limit']?:20;
$categoryId = isset($_REQUEST['cat']) ? $_REQUEST['cat'] : 2;
$order = $_REQUEST['order']?:"relevance";
$dir = $_REQUEST['dir']?  $_REQUEST['dir'] :"desc";
$currentNumberProduct = ($currentPage - 1) * $setPageSize;
$helper = Mage::helper('mageworx_searchsuiteautocomplete');
if ($queryText && !empty($queryText)) {
    $queryModel = Mage::helper('catalogsearch')->getQuery();
    $queryModel->setStoreId(getStoreId());
    $queryModel->prepare();
    $queryModel->setPopularity($queryModel->getPopularity() + 1);
    $queryModel->save();
    $fields = $helper->getPopupFields();
    $dataArr = array();

    if (in_array('product', $fields)) {
        $layer = getLayer();
        addFacetCondition($layer);
        $dataArr['filter_list'] = searchFilterList($layer);

        $attr = array();
        $fields = $helper->getProductResultFields();
        if (in_array('description', $fields)) {
            $attr[] = 'description';
        }
        if (in_array('short_description', $fields)) {
            $attr[] = 'short_description';
        }
        if (in_array('product_image', $fields)) {
            $attr[] = 'image';
        }
        $collection = $layer->getProductCollection();
        $collection->addAttributeToSelect($attr);
        if (isset($_REQUEST['carnival_brand'])){
            $carnival_brand = explode(',',$_REQUEST['carnival_brand']);
            $collection->addAttributeToFilter('carnival_brand',array( 'in' => $carnival_brand));
        }
        $collection->setOrder($order, $dir);
        $collection->getSelect()->limit($setPageSize, $currentNumberProduct);
        $products = $collection->load();
        $label_collection = Mage::getModel('amlabel/label')->getCollection()
            ->addFieldToFilter('include_type', array('neq'=>1));
        if ($products->count() > 0) {
            foreach ($products as $product) {
                //
                $labels = array();
                if (0 < $label_collection->getSize()) {
                    foreach ($label_collection as $label) {
                        $name = 'amlabel_' . $label->getId();
                        if ($product->hasData($name)) {
                            $labels[] = $label->getId();
                        }
                        elseif ($product->getData('sku')) {
                            $skus = explode(',', $label->getIncludeSku());
                            if (in_array($product->getData('sku'), $skus)){
                                $labels[] = $label->getId();
                            }
                        }
                    }
                }
                $data['label'] = $labels;
                //
                $buy_with_point = false;
                $points_spend = 0;
                if ($product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($product)){
                  $buy_with_point = true;
                  $points_spend = (int)$product->getResource()->getAttribute('rewardpoints_spend')->getFrontend()->getValue($product);
                }
                $data['product_id'] = $product->getId();
                $data['sku'] = $product->getSku();
                $data['name'] = $product->getName();
                $data['image'] = $product->getImageUrl();
                $data['price'] = (string)$product->getPrice();
                $data['special_price'] = $product->getFinalPrice();
                $data['buy_with_point'] = $buy_with_point;
                $data['points_spend'] = $points_spend;
                $data['brand'] = $product->getResource()->getAttribute('carnival_brand')->getFrontend()->getValue($product);
                if($product->isSaleable())
                    $data["in_stock"] = true;
                else
                    $data['in_stock'] = false;
                $dataArr['products_list'][] = $data;

            }
        }
    }
    dataResponse(200, 'Valid', $dataArr);

} else {
    dataResponse(400, 'Invalid Post');
}
