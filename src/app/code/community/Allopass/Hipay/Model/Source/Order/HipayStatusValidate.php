<?php

class Allopass_Hipay_Model_Source_Order_HipayStatusValidate
{
    public function toOptionArray()
    {
        $options = array();
        
        $options[] = array(
            'value' => 117,
            'label' => Mage::helper('hipay')->__('Capture Requested')
        );
        
        $options[] = array(
            'value' => 118,
            'label' => Mage::helper('hipay')->__('Capture')
        );
       
        return $options;
    }
}
