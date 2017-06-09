<?php

class Allopass_Hipay_Model_Source_Product_Facilipay3x
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => '3xcb', 'label' => Mage::helper('hipay')->__('With fees')),
            array('value' => '3xcb-no-fees', 'label' => Mage::helper('hipay')->__('Without fees')),
        );
    }
}