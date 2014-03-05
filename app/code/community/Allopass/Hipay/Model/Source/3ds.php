<?php

/**
 *
 * Allopass Hipay Activate 3DS
 *
 */
class Allopass_Hipay_Model_Source_3ds
{
 	/**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('hipay')->__('Enable for all transactions')),
            array('value' => 2, 'label'=>Mage::helper('hipay')->__('Enable for configured 3ds rules')),
            array('value' => 0, 'label'=>Mage::helper('hipay')->__('Disabled')),
            
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('hipay')->__('Disabled'),
            1 => Mage::helper('hipay')->__('Enable for all transactions'),
            2 => Mage::helper('hipay')->__('Enable for configured 3ds rules'),
        );
    }

}