<?php
require_once '../../app/Mage.php';
require_once '../functions.php';

$phone = Mage::getStoreConfig('aboutusmobile_options/contactus/contact_phone');

$email = array(
	'type' => 'email',
	'label' => Mage::getStoreConfig('aboutusmobile_options/contactus/contact_email'),
	'is_active' => true,
	'detail' => null,
	'url' => null
);
$phone = array(
	'type' => 'phone',
	'label' => Mage::getStoreConfig('aboutusmobile_options/contactus/contact_phone'),
	'is_active' => true,
	'detail' => Mage::getStoreConfig('aboutusmobile_options/contactus/contact_details'),
	'url' => null
);

// 3 type: fb,instagram,youtube
$follow_fb_1 = array(
	'type' => 'fb',
	'label' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Facebook_1/label'),
	'is_active' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Facebook_1/isactive') == 1 ? true : false,
	'detail' => null,
	'url' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Facebook_1/url')
);
$follow_fb_2 = array(
	'type' => 'fb',
	'label' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Facebook_2/label'),
	'is_active' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Facebook_2/isactive') == 1 ? true : false,
	'detail' => null,
	'url' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Facebook_2/url')
);
$follow_insta_1 = array(
	'type' => 'instagram',
	'label' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Instagram_1/label'),
	'is_active' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Instagram_1/isactive') == 1 ? true : false,
	'detail' => null,
	'url' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Instagram_1/url')
);
$follow_insta_2 = array(
	'type' => 'instagram',
	'label' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Instagram_2/label'),
	'is_active' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Instagram_2/isactive') == 1 ? true : false,
	'detail' => null,
	'url' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Instagram_2/url')
);
$follow_youtube = array(
	'type' => 'youtube',
	'label' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Youtube/label'),
	'is_active' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Youtube/isactive') == 1 ? true : false,
	'detail' => null,
	'url' => Mage::getStoreConfig('aboutusmobile_options/Folowus_Youtube/url')
);
$dataArr = array(
		'contact_detail' => array(
			$phone,
			$email
		),
		'follow_us' => array(
			$follow_fb_1, $follow_fb_2, $follow_insta_1, $follow_insta_2, $follow_youtube
		)
);
dataResponse(200, 'valid', $dataArr);