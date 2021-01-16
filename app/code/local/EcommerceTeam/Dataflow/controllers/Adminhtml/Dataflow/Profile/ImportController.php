<?php
/**
 * Dataflow - Magento Extension
 *
 * @package Dataflow
 * @category EcommerceTeam
 * @copyright Copyright 2013 EcommerceTeam Inc. (http://www.ecommerce-team.com)
 * @version: 2.2.0
 */

class EcommerceTeam_Dataflow_Adminhtml_Dataflow_Profile_ImportController
    extends Mage_Adminhtml_Controller_Action
{
    /** @var EcommerceTeam_Dataflow_Helper_Data */
    protected $_helper;

    /**
     * Initialize helper
     */
    protected function _construct()
    {
        $this->_helper = Mage::helper('ecommerceteam_dataflow');
        parent::_construct();
    }
    
    /**
     * Init navigation and breadcrumbs
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/convert')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Dataflow'), Mage::helper('adminhtml')->__('Task'));
        
        return $this;
    }

    /**
     * Profiles grid
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ecommerceteam_dataflow/adminhtml_profile_import'));
        $this->renderLayout();
    }

    /**
     * Create new profile
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Create/Edit profile
     */
    public function editAction()
    {
        /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
        $profile = Mage::getModel('ecommerceteam_dataflow/profile_import');
        if ($id = $this->getRequest()->getParam('id')) {
            $profile->load($id);
        }
        Mage::register('profile', $profile);
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ecommerceteam_dataflow/adminhtml_profile_import_edit'));
        $this->_addLeft($this->getLayout()->createBlock('ecommerceteam_dataflow/adminhtml_profile_import_edit_tabs'));
        $this->renderLayout();
    }

    /**
     * @param array $data
     * @return EcommerceTeam_Dataflow_Model_Profile_Import
     */
    protected function _saveProfile(array $data)
    {
        /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
        $profile = Mage::getModel('ecommerceteam_dataflow/profile_import');
        if ($this->getRequest()->getParam('id')) {
            $profile->load($this->getRequest()->getParam('id'));
        }
        $mapping = array();
        if (isset($data['mapping']) && is_array($data['mapping'])) {
            foreach ($data['mapping'] as $mappingData) {
                $mapping[$mappingData['number']] = $mappingData['code'];
            }
            unset($data['mapping']);
        }
        if (!isset($data['fallbacks']) || !is_array($data['fallbacks'])) {
            $data['fallbacks'] = '';
        }
        if (!isset($data['transform']) || !is_array($data['transform'])) {
            $data['transform'] = '';
        }
        $profile->addData($data);
        $profile->setData('mapping', $mapping);
        $profile->setData('parser_model', $this->_helper->getDefaultParserModel());

        $profile->save();

        return $profile;
    }

    /**
     * Save profile data
     */
    public function saveAction()
    {
        try {
            if ($this->getRequest()->isPost()) {
                $this->_saveProfile($this->getRequest()->getPost());
                $this->_getSession()->addSuccess($this->__('Profile saved successfully.'));
                $this->_redirect('*/*');
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }

    /**
     * Delete profile
     */
    public function deleteAction()
    {
        try {
            /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
            $profile = Mage::getModel('ecommerceteam_dataflow/profile_import');
            $profile->load($this->getRequest()->getParam('id'));
            if (!$profile->getId()) {
                Mage::throwException($this->__('Import no longer exists.'));
            } $profile->delete();
            $this->_getSession()->addSuccess($this->__('Profile deleted successfully.'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectReferer();
        }
        $this->_redirect('*/*');
    }

    /**
     * Save profile and run
     */
    public function saveAndRunAction()
    {
        try {
            if ($this->getRequest()->isPost()) {
                $profile = $this->_saveProfile($this->getRequest()->getPost());
                $this->_getSession()->addSuccess($this->__('Profile saved successfully.'));
                $this->_redirect('*/*/run', array('id' => $profile->getId()));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectReferer();
        }
    }
    
    public function runAction()
    {
        /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
        $profile = Mage::getModel('ecommerceteam_dataflow/profile_import');
        $profile->load($this->getRequest()->getParam('id'));

        Mage::register('profile', $profile);
        if (!empty($_FILES)) {
            try {
                if (!$profile->getId()) {
                   throw new EcommerceTeam_Dataflow_Exception($this->__('Profile no longer exists.'));
                }
                $fileData = array_shift($_FILES);
                $filePath = $fileData['tmp_name'];
                $profile->run($filePath);
                $this->_redirect('*/*');
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirectReferer();
            }
        } else {
            $this->_forward('file');
        }
    }
    
    public function fileAction()
    {
        try {
            /** @var $profile EcommerceTeam_Dataflow_Model_Profile_Import */
            $profile = Mage::getModel('ecommerceteam_dataflow/profile_import');
            $profile->load($this->getRequest()->getParam('id'));
            if (!$profile->getId()) {
                throw new EcommerceTeam_Dataflow_Exception($this->__('Profile no longer exists.'));
            }

            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('ecommerceteam_dataflow/adminhtml_profile_import_file'));
            $this->renderLayout();
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*');
        }
    }

    /**
     * Profiles grid
     */
    public function scheduleAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ecommerceteam_dataflow/adminhtml_profile_schedule'));
        $this->renderLayout();
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/convert/ecommerceteam_dataflow_product_import');;
    }
}
