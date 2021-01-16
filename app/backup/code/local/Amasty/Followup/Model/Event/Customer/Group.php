<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

    class Amasty_Followup_Model_Event_Customer_Group extends Amasty_Followup_Model_Event_Basic
    {
        public function _validateCustomerGroupChanged($afterCustomer, $customerGroupsIds, $origValidate = FALSE){
            $arrCustomerGroups = explode(',', $customerGroupsIds);
            $origData = $afterCustomer->getOrigData();
            
            $origValidated = TRUE;
            
            if ($origValidate) {
                $origValidated = is_array($origData) && 
                    $origData["group_id"] != $afterCustomer->getGroupId();
            }
            
            return 
                    $origValidated && 
                    (in_array($afterCustomer->getGroupId(), $arrCustomerGroups) || empty($customerGroupsIds));
        }
        
        function validate($afterCustomer){
            return $this->_validateBasic($afterCustomer->getStoreId(), $afterCustomer->getEmail(), $afterCustomer->getGroupId()) &&
                    $this->_validateCustomerGroupChanged($afterCustomer, $this->_rule->getCustGroups(), TRUE);
        }
    }
?>