<?php

/**
 *
 * Allopass Hipay Credit cards types
 *
 */
class Allopass_Hipay_Model_Source_CcType
{
 	public function toOptionArray()
    {
      
        $options = array();

        foreach (Mage::getSingleton('hipay/config')->getCcTypes() as $code => $name) {       
                $options[] = array(
                   'value' => $code,
                   'label' => $name
                );      
        }

        return $options;
    }
}