<?php

/**
 *
 * Allopass Hipay redirect url for pending
 *
 */
class Allopass_Hipay_Model_Source_Pendingredirect
{
    public function toOptionArray()
    {

        return array(
            array('value' => 'hipay/checkout/pending', 'label' => Mage::helper('hipay')->__('Pending page')),
            array('value' => 'checkout/onepage/success', 'label' => Mage::helper('hipay')->__('Success page')),
            array('value' => 'checkout/onepage/failure', 'label' => Mage::helper('hipay')->__('Failure page')),
        );
    }
}
