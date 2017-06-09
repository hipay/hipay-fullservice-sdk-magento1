<?php

class Allopass_Hipay_Model_Source_Product_Facilipay4x
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '4xcb', 'label' => Mage::helper('hipay')->__('With fees')),
            array('value' => '4xcb-no-fees', 'label' => Mage::helper('hipay')->__('Without fees')),
        );
    }
}