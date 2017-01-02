<?php

class Allopass_Hipay_Model_Source_Order_Status_Accepted extends Allopass_Hipay_Model_Source_Order_Status
{

    // set null to enable all possible
    protected $_stateStatuses = array(
       // Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_PROCESSING,
        Mage_Sales_Model_Order::STATE_COMPLETE
    );
    
    public function toOptionArray()
    {
        if ($this->_stateStatuses) {
            $statuses = Mage::getSingleton('sales/order_config')->getStateStatuses($this->_stateStatuses);
        } else {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }
        $options = array();
        /*$options[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );*/
        foreach ($statuses as $code => $label) {
            if ($code != Mage_Sales_Model_Order::STATE_PROCESSING && $code !=  Mage_Sales_Model_Order::STATE_COMPLETE) {
                continue;
            }
            
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
}
