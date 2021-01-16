<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


class Amasty_SecurityAuth_Model_Observer
{
    /**
     * Listens to the admin_user_authenticate_after Event and checks whether the user has access to areas that are configured
     * to be protected by Two Factor Auth. If so, send the user to either add a Two Factor Auth to their Account, or enter a
     * code from their connected Auth provider
     *
     */
    public function adminUserAuthenticateAfter(Varien_Event_Observer $observer)
    {
        $userId = $observer->getUser()->getId();
        /**
         * @var Amasty_SecurityAuth_Model_Auth $userAuth
         */
        $userAuth = Mage::getModel('amsecurityauth/auth')->load($userId);

        if (!Mage::helper('amsecurityauth')->isActive()
            || !Mage::helper('amsecurityauth')->isActiveForUser($userAuth)
        ) {
            return $this;
        }

        $code = Mage::app()->getRequest()->getPost($userAuth->getCodeName());
        if (!$userAuth->verifyCode($userAuth->getTwoFactorToken(), $code, Mage::getStoreConfig('amsecurityauth/general/discrepancy'))) {
            /** @var $adminSession Mage_Admin_Model_Session */
            $adminSession = Mage::getSingleton('admin/session');
            $adminSession->unsetAll();
            $adminSession->getCookie()->delete($adminSession->getSessionName());

            Mage::throwException(Mage::helper('amsecurityauth')->__('Security Code is Incorrect.'));
            Mage::app()->getResponse()->setRedirect('*/admin');
        }

    }

    public function saveUserAuthSettings(Varien_Event_Observer $observer)
    {
        $request = Mage::app()->getRequest();

        $userId = $request->getPost('user_id')
            ? $request->getPost('user_id')
            : Mage::getModel('admin/user')->getCollection()->getLastItem()->getId();

        //Validate current admin password
        $currentPassword = $request->getParam('current_password', null);
        if (!empty($currentPassword)) {
            $result = Mage::getSingleton('admin/session')->getUser()->validateCurrentPassword($currentPassword);
            // if admin pass is wrong don't save key
            if (is_array($result)) {
                Mage::getSingleton('core/session')->setUserIdTwoAuth($userId);
                return $this;
            }
        }

        $userAuth = Mage::getModel('amsecurityauth/auth')->load($userId);

        $data = array(
            'user_id' => $userId,
            'enable' => $request->getPost('amsecurityauth_active'),
            'two_factor_token' => $request->getPost('amsecurityauth_secret')
        );
        if (!$userAuth->getId()) {
            Mage::getModel('amsecurityauth/auth')->getResource()->insert($data);
        } else {
            $userAuth->setData($data);
            $userAuth->save();
        }

        return $this;
    }

    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        $user = Mage::registry('permissions_user');

        if (!Mage::getStoreConfig('amsecurityauth/general/active')
            || !$user
            || !$user->getId()
        ) {
            return $this;
        }

        /**
         * @var Mage_Adminhtml_Block_Page
         */
        $block = $observer->getBlock();

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/permissions_user_edit_tabs');
        if ($blockClass == get_class($block)) {
            $label = Mage::helper('amsecurityauth')->__('Two-Factor Settings');
            if (version_compare(Mage::getVersion(), '1.11') <= 0
                && Mage::helper('core')->isModuleEnabled('Enterprise_Enterprise')
            ) {
                $block->addTab('amsecurityauth_edit_permissions', array(
                    'label' => $label,
                    'title' => $label,
                    'content' => $block->getLayout()->createBlock('amsecurityauth/adminhtml_permissions_user_edit_tab_auth')->toHtml())
                );
            } elseif (version_compare(Mage::getVersion(), '1.4.2.0', '<=')) {
                $block->addTab('amsecurityauth_edit_permissions', array(
                    'label' => $label,
                    'title' => $label,
                    'content' => $block->getLayout()->createBlock('amsecurityauth/adminhtml_permissions_user_edit_tab_auth')->toHtml())
                );
            } else {
                $block->addTabAfter('amsecurityauth_edit_permissions', array(
                    'label' => $label,
                    'title' => $label,
                    'content' => $block->getLayout()->createBlock('amsecurityauth/adminhtml_permissions_user_edit_tab_auth')->toHtml()),
                    'roles_section'
                );
            }
        }

        return $this;
    }

    public function checkMagentoVersion(Varien_Event_Observer $observer)
    {
        $params = Mage::app()->getRequest()->getParams();
        if (isset($params['section'])
            && 'amsecurityauth' == $params['section']
            && version_compare(Mage::getVersion(), '1.4.2.0', '<=')
        ) {
            $msg = Mage::helper('amsecurityauth')->__('It seems you are using the outdated version of Magento. Please, read <a href="https://amasty.com/docs/doku.php?id=magento_1%3Atwo-step_authentication&utm_source=extension&utm_medium=link&utm_campaign=2factor-oldmage#troubleshooting" target="_blank">this guide</a> to make the module functional.');
            Mage::getSingleton('adminhtml/session')->addWarning($msg);
        }
    }
}
