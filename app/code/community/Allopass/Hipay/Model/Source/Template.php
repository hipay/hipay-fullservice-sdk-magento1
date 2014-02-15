<?php

/**
 *
 * Allopass Hipay templates types
 *
 */
class Allopass_Hipay_Model_Source_Template
{
 	public function toOptionArray()
    {
        
        return array(
        		array('value' => 'basic', 'label' => Mage::helper('hipay')->__('basic')),
        		array('value' => 'basic2', 'label' => Mage::helper('hipay')->__('basic2')),
        );
    }
}