<?php

/**
 *
 * Allopass Hipay Payment Action Dropdown source
 *
 */
class Allopass_Hipay_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE, 'label' => Mage::helper('hipay')->__('Authorization')),
            array('value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE, 'label' => Mage::helper('hipay')->__('Sale')),
        );
    }
}
