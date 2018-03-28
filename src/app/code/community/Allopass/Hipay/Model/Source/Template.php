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
