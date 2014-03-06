<?php

/**
 *
 * Allopass Hipay templates types
 *
 */
class Allopass_Hipay_Model_Source_Template
{
 	/*public function toOptionArray()
    {
        
        return array(
        		array('value' => 'basic', 'label' => Mage::helper('hipay')->__('basic')),
        		array('value' => 'basic2', 'label' => Mage::helper('hipay')->__('basic2')),
        		array('value' => 'basic3', 'label' => Mage::helper('hipay')->__('basic3')),
        		array('value' => 'basic4', 'label' => Mage::helper('hipay')->__('basic4')),
        );
    }*/
	
	public function toOptionArray()
    {
      
        $options = array();

        foreach (Mage::getSingleton('hipay/config')->getTemplateHosted() as $value => $label) {       
                $options[] = array(
                   'value' => $value,
                   'label' => $label
                );      
        }

        return $options;
    }
}